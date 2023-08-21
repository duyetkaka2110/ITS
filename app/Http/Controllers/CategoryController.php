<?php

namespace App\Http\Controllers;

use App\Models\t_category;
use Illuminate\Http\Request;
use DB;
use App\GetMessage;

class CategoryController extends Controller
{

    /**
     * 階層画面
     */
    public function index()
    {
        $title = "階層";
        $categories = t_category::whereNull("Parent_ID")
            ->orWhere("Parent_ID", 0)
            ->with("allChilds")
            ->orderBy("Sort_No")
            ->get()->toArray();
        $categories = json_encode($this->_getCategoryTree($categories));

        return view("category.index", compact("title", "categories"));
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
            $data = $rq->only("Category_ID", "Category_Nm", "Parent_ID", "Sort_No");
            if ($rq->action == "delete_node") {
                //　削除ボタン
                if ($rq->Category_ID && t_category::find($data["Category_ID"])) {
                    t_category::find($data["Category_ID"])->delete();
                    $this->resetSortNextMinus($rq->Parent_ID, $rq->Sort_No);
                }
            } else if ($rq->action == "duplicate_node") {
                t_category::upsert($rq->list, ["Category_ID"]);
                $this->resetSortNext($rq);
            } else {
                // ドラッグ＆ドロップ
                if ($rq->action == "move_node") {
                    // Sort_No更新
                    $this->resetSortNext($rq);
                    $this->resetSortNextMinus($rq->Old_Parent_ID, $rq->Old_Sort_No);
                }
                t_category::upsert($data, ["Category_ID"]);
            }
            DB::commit();
            return [
                "status" => true,
                "data" => t_category::pluck("Sort_No"),
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            return [
                "status" => false,
                "msg" =>  GetMessage::getMessageByID("error003")
            ];
        }
    }
    public function resetSortNext(Request $rq)
    {
        $str = ($rq->Parent_ID == $rq->Old_Parent_ID && $rq->Sort_No < $rq->Old_Sort_No) || $rq->Parent_ID != $rq->Old_Parent_ID || $rq->action == "duplicate_node" ? ">=" : ">";
        t_category::where("Parent_ID", $rq->Parent_ID)
            ->where("Sort_No", $str, $rq->Sort_No)->update(["Sort_No" => DB::raw("Sort_No+1")]);
    }

    public function resetSortNextMinus($Parent_ID, $Sort_No)
    {
        t_category::where("Parent_ID", $Parent_ID)
            ->where("Sort_No", ">", $Sort_No)->update(["Sort_No" => DB::raw("Sort_No-1")]);
    }
    /**
     * 階層一覧のjsTreeフォーマット取得
     * param $data 階層一覧
     * return 配列
     */
    private function _getCategoryTree($data)
    {
        $tmp = [];
        foreach ($data as $d) {
            $tmp[] = [
                "id" => $d["Category_ID"],
                "parentID" => $d["Parent_ID"] ? $d["Parent_ID"] : 0,
                "text" => $d["Category_Nm"],
                "children" => $this->_getCategoryTree($d["all_childs"])
            ];
        }
        return $tmp;
    }
    public function setData()
    {
        $json = '[{"Category_ID":"1","Category_Nm":"\u8010\u706b\u30fb\u906e\u97f3\u5de5\u4e8b","Parent_ID":"0","Sort_No":1},{"Category_ID":"10","Category_Nm":"\u58c1(3)(23)(2343)","Parent_ID":"0","Sort_No":8},{"Category_ID":"11","Category_Nm":"\u30c9\u5de5\u4e8b","Parent_ID":"0","Sort_No":10},{"Category_ID":"14","Category_Nm":"\u65b0\u898f\u30d5\u30a9\u30eb\u30c01","Parent_ID":"0","Sort_No":11},{"Category_ID":"2","Category_Nm":"\u8010\u706b\u58c1\u4e0b\u5730\u5de5\u4e8b","Parent_ID":"0","Sort_No":4},{"Category_ID":"290","Category_Nm":"\u65b0\u898f\u30d5\u30a9\u30eb\u30c02","Parent_ID":"0","Sort_No":2},{"Category_ID":"291","Category_Nm":"\u65b0\u898f\u30d5\u30a9\u30eb\u30c0","Parent_ID":"0","Sort_No":12},{"Category_ID":"3","Category_Nm":"\u58c1","Parent_ID":"0","Sort_No":3},{"Category_ID":"30","Category_Nm":"\u65b0\u898f\u30d5\u30a9\u30eb\u30c03","Parent_ID":"0","Sort_No":6},{"Category_ID":"7","Category_Nm":"\u58c1 2","Parent_ID":"0","Sort_No":9},{"Category_ID":"8","Category_Nm":"\u30d1\u30fc\u30c6\u30a3\u30b7\u30e7\u30f3","Parent_ID":"0","Sort_No":5},{"Category_ID":"9","Category_Nm":"\u58c1(3)","Parent_ID":"0","Sort_No":7}]';
        t_category::query()->delete();
        t_category::insert(json_decode($json, true));
        return redirect()->back();
    }
}
