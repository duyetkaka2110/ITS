<?php

namespace App\Http\Controllers;

use App\Models\t_category;
use Illuminate\Http\Request;
use DB;
use App\GetMessage;
use App\Models\t_mitsumori;
use App\Models\t_mitsumori_meisai;
use Session;

use function PHPUnit\Framework\returnSelf;

class CategoryController extends Controller
{
    protected $tmp = [];
    protected $AdQuoNo = 7;
    protected $Category_ID = 0;

    public function __construct()
    {
        // カテゴリ選択した取得
        if (Session::get("AdQuoNo")) {
            $this->AdQuoNo = Session::get("AdQuoNo");
        }
    }

    /**
     * 階層画面
     */
    public function index()
    {
        $title = "階層";
        $categories = $this->getList();
        return view("category.index", compact("title", "categories"));
    }
    
    /**
     * 階層一覧取得
     * param int $id カテゴリID
     */
    public function getList(int $id = 0)
    {
        $categories = t_category::where("AdQuoNo", $this->AdQuoNo)
            ->where("Parent_ID", 0)
            ->when($id, function ($query, $id) {
                return $query->where("Category_ID", $id);
            })
            ->with("allChilds")
            ->orderBy("Sort_No")
            ->orderBy("Parent_ID")
            ->get()->toArray();
        if ($id) {
            // カテゴリ選択後
            $this->_getNewParentArray($categories);
            return $this->tmp;
        }
        // カテゴリ一覧表示
        $categories = json_encode($this->_getCategoryTree($categories));
        return $categories;
    }

    /**
     * 複製でカテゴリの新規追加取得
     * param $categories カテゴリ複製の一覧
     */
    private function _getNewParentArray($categories)
    {
        foreach ($categories as $c) {
            $child = $c["all_childs"];
            unset($c["all_childs"]);
            $cateIDOld = $c["Category_ID"];
            unset($c["Category_ID"]);
            $c["Category_ID"] = t_category::insertGetId($c);
            $this->tmp[$cateIDOld] = $c;
            if ($child) {
                $this->_getNewParentArray($child);
            }
        }
    }

    /**
     * 階層一覧のjsTreeフォーマット取得
     * param $data 階層一覧
     * return 配列
     */
    private function _getCategoryTree($data)
    {
        $tmp = [];
        foreach ($data as $k => $d) {
            $tmp[] = [
                "id" => $d["Category_ID"],
                "parentID" => $d["Parent_ID"] ? intval($d["Parent_ID"]) : 0,
                "text" => $d["Category_Nm"],
                // "state" => ["opened" => true],
                "Sort_No" => $d["Sort_No"],
                "children" => $this->_getCategoryTree($d["all_childs"])
            ];
            $this->_getCategoryTree($d["all_childs"]);
        }
        return $tmp;
    }

    /**
     * 階層データ保存
     * param Request $rq
     * return 配列
     */
    public function store(Request $rq)
    {
        try {
            DB::beginTransaction();
            $msg = null;
            $data = $rq->only("Category_ID", "Category_Nm", "Parent_ID", "Sort_No");
            if ($rq->action == "delete_node") {
                //　削除ボタン
                if ($rq->Category_ID && t_category::find($data["Category_ID"])) {
                    t_category::find($data["Category_ID"])->delete();
                    $this->resetSortNextMinus($rq->Parent_ID, $rq->Sort_No);
                }
            } else if ($rq->action == "duplicate_node") {
                // 複製
                $this->resetSortNext($rq);
                $categories = $this->getList($rq->Category_ID);
                $cateUpdate = [];
                foreach ($categories as $CateIDOld => $c) {

                    $old = t_category::select("AdQuoNo", "Category_ID")
                        ->where("Category_ID", "!=", 0)
                        ->where("Category_ID", $CateIDOld)->first();
                    if ($old) {
                        // t_mitsumori複製
                        t_mitsumori::select("*")
                            ->selectRaw($c["Category_ID"] . " AS Category_ID") // 新規カテゴリID設定
                            ->where("AdQuoNo", $this->AdQuoNo) // 現場選択
                            ->where("Category_ID", $old->Category_ID)
                            ->get()->each(function ($e, $k) {
                                $oldId = $e->id;
                                $e = $e->toArray();
                                unset($e["id"]);
                                $newId = t_mitsumori::insertGetId($e);
                                // t_mitsumori_meisai複製
                                t_mitsumori_meisai::insert(t_mitsumori_meisai::select("*")
                                    ->selectRaw($newId . " AS Mitsumori_ID") // 新規見積カテゴリ設定
                                    ->where("Mitsumori_ID", $oldId)->get()->toArray());
                            });
                    }

                    if (isset($categories[$c["Parent_ID"]])) {
                        $cateUpdate[] = [
                            "Category_ID" => $c["Category_ID"],
                            "Parent_ID" => $categories[$c["Parent_ID"]]["Category_ID"]
                        ];
                    } else {
                        // firstParent更新
                        t_category::upsert([
                            "Category_ID" => $c["Category_ID"],
                            "Category_Nm" => $rq->Category_Nm,
                            "Sort_No" => $rq->Sort_No
                        ], ["Category_ID"]);
                    }
                }
                t_category::upsert($cateUpdate, ["Category_ID"]);
                $msg = str_replace("{p}", "複製", GetMessage::getMessageByID("error004"));
            } else {
                // ドラッグ＆ドロップ
                if ($rq->action == "move_node") {
                    // Sort_No更新
                    $this->resetSortNext($rq);
                    $this->resetSortNextMinus($rq->Old_Parent_ID, $rq->Old_Sort_No);
                }
                if ($rq->action == "create_node") {
                    $data["AdQuoNo"] = $this->AdQuoNo;
                    $Category_ID = t_category::insertGetId($data);
                    // 見積明細一覧追加
                    t_mitsumori::insert([
                        "AdQuoNo" => $this->AdQuoNo,
                        "Category_ID" => $Category_ID,
                        "DetailNo" => 1,
                        "No" => NULL // No：再設定
                    ]);
                } else {
                    t_category::upsert($data, ["Category_ID"]);
                }
            }
            DB::commit();
            return [
                "status" => true,
                "data" =>  $this->getList(),
                "msg" => $msg
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            return [
                "status" => false,
                "msg" =>  GetMessage::getMessageByID("error003")
            ];
        }
    }

    /**
     * 選択行から、Sort_No上げる
     * param Request $rq
     */
    public function resetSortNext(Request $rq)
    {
        $str = ($rq->Parent_ID == $rq->Old_Parent_ID && $rq->Sort_No < $rq->Old_Sort_No)
            || $rq->Parent_ID != $rq->Old_Parent_ID
            || $rq->action == "duplicate_node" ? ">=" : ">";
        $this->_resetSort($rq->Parent_ID, $rq->Sort_No,  $str, "+");
    }

    /**
     * 選択行から、Sort_No下げる
     * param int $Parent_ID 選択行ＩＤ
     * param int $Sort_No 選択行の位置
     */
    public function resetSortNextMinus(int $Parent_ID, int $Sort_No)
    {
        $this->_resetSort($Parent_ID, $Sort_No, ">", "-");
    }

    /**
     * 選択行から、Sort_No更新
     * param int $Parent_ID 選択行ＩＤ
     * param int $Sort_No 選択行の位置
     * param string $str > < =
     * param string $cal + -
     */
    private function _resetSort(int $Parent_ID, int $Sort_No, string $str, string $cal)
    {
        t_category::where("Parent_ID", $Parent_ID)
            ->where("Sort_No", $str, $Sort_No)->update(["Sort_No" => DB::raw("Sort_No $cal 1")]);
    }


    public function setData()
    {
        $import = new DataController();
        $import->readCsvMitsumori();
        $data = t_mitsumori::select("AdQuoNo", "Category_ID")
            ->selectRaw("Category_ID AS Sort_No")
            ->selectRaw("CASE WHEN Category_ID = 1 
                                    THEN '明細①'
                            WHEN Category_ID = 2 
                                    THEN '明細②'
                            WHEN Category_ID = 3 
                                    THEN '明細③'
                            WHEN Category_ID = 4 
                                    THEN '明細④'
                            WHEN Category_ID = 5 
                                    THEN '明細⑤'
                                    ELSE '明細'
                            END AS Category_Nm")
            ->selectRaw("0 AS Parent_ID")
            ->groupBy("AdQuoNo", "Category_ID")
            ->get()->toArray();
        // $data = DB::select("SELECT *, ROW_NUMBER() OVER(ORDER BY Parent_ID, AdQuoNo, Category_ID) Category_ID FROM (" . $sql . ") a");
        // $data = array_map(function ($value) {
        //     return (array)$value;
        // }, $data);
        t_category::query()->delete();
        foreach ($data as $c) {
            $oldCate = $c["Category_ID"];
            unset($c["Category_ID"]);
            t_mitsumori::where("AdQuoNo", $c["AdQuoNo"])
                ->where("Category_ID", $oldCate)->update(["Category_ID" => t_category::insertGetId($c)]);
        }
        // t_category::insert($data);
        // t_category::select("Category_ID", "AdQuoNo")->where("Parent_ID", 0)->each(function ($val, $key) {
        //     t_category::where("Parent_ID", $val->AdQuoNo)
        //         ->where("AdQuoNo", $val->AdQuoNo)
        //         ->update(["Parent_ID" => $val->Category_ID]);
        // });

        // $json = '[{"Category_ID":"1","Category_Nm":"\u8010\u706b\u30fb\u906e\u97f3\u5de5\u4e8b","Parent_ID":"0","Sort_No":1},{"Category_ID":"10","Category_Nm":"\u58c1(3)(23)(2343)","Parent_ID":"0","Sort_No":8},{"Category_ID":"11","Category_Nm":"\u30c9\u5de5\u4e8b","Parent_ID":"0","Sort_No":10},{"Category_ID":"14","Category_Nm":"\u65b0\u898f\u30d5\u30a9\u30eb\u30c01","Parent_ID":"0","Sort_No":11},{"Category_ID":"2","Category_Nm":"\u8010\u706b\u58c1\u4e0b\u5730\u5de5\u4e8b","Parent_ID":"0","Sort_No":4},{"Category_ID":"290","Category_Nm":"\u65b0\u898f\u30d5\u30a9\u30eb\u30c02","Parent_ID":"0","Sort_No":2},{"Category_ID":"291","Category_Nm":"\u65b0\u898f\u30d5\u30a9\u30eb\u30c0","Parent_ID":"0","Sort_No":12},{"Category_ID":"3","Category_Nm":"\u58c1","Parent_ID":"0","Sort_No":3},{"Category_ID":"30","Category_Nm":"\u65b0\u898f\u30d5\u30a9\u30eb\u30c03","Parent_ID":"0","Sort_No":6},{"Category_ID":"7","Category_Nm":"\u58c1 2","Parent_ID":"0","Sort_No":9},{"Category_ID":"8","Category_Nm":"\u30d1\u30fc\u30c6\u30a3\u30b7\u30e7\u30f3","Parent_ID":"0","Sort_No":5},{"Category_ID":"9","Category_Nm":"\u58c1(3)","Parent_ID":"0","Sort_No":7}]';
        // t_category::query()->delete();
        // t_category::insert(json_decode($json, true));
        return redirect()->back();
    }
}
