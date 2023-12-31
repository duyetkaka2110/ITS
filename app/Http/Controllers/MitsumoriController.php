<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\t_mitsumori;
use App\Models\t_mitsumori_meisai;
use App\Models\m_bui;
use App\Models\m_koshu;
use App\Models\m_shiyo;
use App\Models\m_shiyo_shubetsu;
use App\Models\m_tani;
use App\Models\m_seko_tanka;
use App\Models\t_seko_tanka;
use App\Models\t_shiyo;
use App\Http\Controllers\ShiyoController;
use App\Http\Controllers\ZairyoController;
use App\Http\Controllers\CategoryController;
use App\Models\t_category;
use Session;

class MitsumoriController extends Controller
{
    protected $AdQuoNo = 7;
    protected $Category_ID = 0;

    public function __construct()
    {
        // カテゴリ選択した取得
        if (Session::get("Category_ID")) {
            $this->Category_ID = Session::get("Category_ID");
        }
        if (Session::get("AdQuoNo")) {
            $this->AdQuoNo = Session::get("AdQuoNo");
        }
    }
    /**
     * datatable
     */
    public function index3(Request $rq)
    {
        $header = $this->_getTableHeader();
        $cmd = json_encode(config("const.cmd"));
        $list = $this->_getList();
        $tanis = m_tani::orderBy("Sort_No")->pluck("Tani_Nm", "Tani_ID")->toArray();

        $shiyo = new ShiyoController();
        $headerShiyo = $shiyo->getTableHeaderShiyo();
        $headerShiyoSelected = $shiyo->getTableHeaderShiyoSelected();

        $zairyo = new ZairyoController();
        $headerZairyo = $zairyo->getTableHeaderZairyo();
        $headerZairyoSelected = $zairyo->getTableHeaderZairyoSelected();

        return view("mitsumori.index3", compact("header", "list", "cmd", "headerShiyo", "headerShiyoSelected", "tanis", "headerZairyo", "headerZairyoSelected"));
    }
    /**
     * tabulator
     */
    public function index2(Request $rq)
    {
        $header = $this->_getTableHeader();
        $cmd = json_encode(config("const.cmd"));
        $list = $this->_getList();
        $tanis = m_tani::orderBy("Sort_No")->pluck("Tani_Nm", "Tani_ID")->toArray();

        $shiyo = new ShiyoController();
        $headerShiyo = $shiyo->getTableHeaderShiyo();
        $headerShiyoSelected = $shiyo->getTableHeaderShiyoSelected();

        $zairyo = new ZairyoController();
        $headerZairyo = $zairyo->getTableHeaderZairyo();
        $headerZairyoSelected = $zairyo->getTableHeaderZairyoSelected();

        return view("mitsumori.index2", compact("header", "list", "cmd", "headerShiyo", "headerShiyoSelected", "tanis", "headerZairyo", "headerZairyoSelected"));
    }

    /**
     * 見積明細画面
     */
    public function index(Request $rq)
    {

        $ctg = new CategoryController();
        $categories = $ctg->getList();
        $cateIDMax = t_category::max("Category_ID");
        $header = $this->_getTableHeader();
        $cmd = json_encode(config("const.cmd"));
        $list = $this->_getList();
        $tanis = m_tani::orderBy("Sort_No")->pluck("Tani_Nm", "Tani_ID")->toArray();

        $shiyo = new ShiyoController();
        $headerShiyo = $shiyo->getTableHeaderShiyo();
        $headerShiyoSelected = $shiyo->getTableHeaderShiyoSelected();

        $zairyo = new ZairyoController();
        $headerZairyo = $zairyo->getTableHeaderZairyo();
        $headerZairyoSelected = $zairyo->getTableHeaderZairyoSelected();

        return view("mitsumori.index", compact("categories", "cateIDMax", "header", "list", "cmd", "headerShiyo", "headerShiyoSelected", "tanis", "headerZairyo", "headerZairyoSelected"));
    }

    /**
     * 工事仕様の選択データ登録をクリック時
     * param Request $rq
     * return 配列
     */
    public function store(Request $rq)
    {
        try {
            DB::beginTransaction();
            $data = $rq->only("Type", "PartName", "FirstName", "StandDimen", "MakerName", "UnitOrg_ID", "Quantity", "UnitPrice", "Amount", "Note");
            $data["Quantity"] = $this->getNumber($data["Quantity"]);
            $data["UnitPrice"] = $this->getNumber($data["UnitPrice"]);
            $data["Amount"] = $this->getNumber($data["Amount"]);
            // 緑画面データ取得
            $dataIS = [];
            if ($rq->filled("Shiyo_ID")) {
                foreach ($rq->Shiyo_ID as $k => $s) {
                    $dataIS[] = [
                        "Shiyo_ID" => $s,
                        "AtariSuryo" => $this->getNumber($rq->AtariSuryo[$k]),
                        "Sort_No" => $rq->Sort_No[$k]
                    ];
                }
                // m_shiyoからt_shiyoへ反映する
                t_shiyo::insert(m_shiyo::select("*")->whereIn("Shiyo_ID", $rq->Shiyo_ID)
                    ->whereNotIn("Shiyo_ID", t_shiyo::whereIn("Shiyo_ID", $rq->Shiyo_ID)->pluck("Shiyo_ID")->all())->get()->toArray());

                // m_seko_tankaからt_seko_tankaへ反映する
                t_seko_tanka::insert(m_seko_tanka::select("*")->whereIn("Shiyo_ID", $rq->Shiyo_ID)
                    ->whereNotIn("Shiyo_ID", t_seko_tanka::whereIn("Shiyo_ID", $rq->Shiyo_ID)->pluck("Shiyo_ID")->all())->get()->toArray());
            }
            // 選択行に登録の時、新規行として追加
            $RowAdd = $rq->btn == "btnSaveNew" ? $rq->RowAdd : 1;
            // No更新
            $dataSelected = json_decode($rq->dataSelected, true);
            $dataNoChange = json_decode($rq->dataNoChange, true);
            $lastNo = $rq->btn == "btnSaveNew" ? (t_mitsumori::where("AdQuoNo", $this->AdQuoNo)->where("Category_ID", $this->Category_ID)->orderBy("DetailNo", "DESC")->value("No")) : ($dataSelected["prevItemNo"] ? $dataSelected["prevItemNo"] : 0);
            $lastNo = $rq->FirstName ? $lastNo : 0;
            // DB更新
            for ($i = 1; $i <= $RowAdd; $i++) {
                $data["No"] = $lastNo = $rq->FirstName ? $lastNo + 1 : 0;
                $this->upsertMitsumoriShiyo($rq, $data, $dataIS, $dataNoChange);
            }
            // 「上書き保存」ボタンと「挿入して登録」ボタンをクリック時
            if ($rq->btn == "btnSave" && $dataNoChange) {
                $FirstNameOld = t_mitsumori::where("id", $rq->id)->value("FirstName");
                if ($FirstNameOld != $rq->FirstName && (!$FirstNameOld || !$rq->FirstName)) {
                    // No再設定
                    $NoUpdate = $rq->FirstName ? ' + ' . $lastNo : " - " . ($dataSelected["lastNo"] ? $dataSelected["lastNo"] : 0);
                    t_mitsumori::whereIn('id', $dataNoChange)
                        ->update(['No' => DB::Raw("No" . $NoUpdate)]);
                }
            }

            // 全て計再設
            if ($rq->btn == "btnSave" || $rq->btn == "btnSaveAdd") {
                $this->_resetAllTotal();
            }
            DB::commit();
            return [
                "status" => true,
                "data" => $this->_getList(),
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
     * 工事仕様の選択データ登録
     * param Request $rq
     * param array $data 見積詳細データ
     * param array $dataIS　見積詳細＿仕様データ
     */
    public function upsertMitsumoriShiyo(Request $rq, array $data, array $dataIS, array $dataNoChange)
    {
        $id = $rq->id;
        if ($rq->btn == "btnSave") {
            t_mitsumori::where("id", $id)->update($data);
            t_mitsumori::find($id)->meisais()->delete();
        } else {
            // 挿入して登録ボタン
            if ($rq->btn == "btnSaveAdd") {
                $DetailNo = t_mitsumori::where("id", $id)->value("DetailNo");
                $data["DetailNo"] = $DetailNo;
                // 明細No:再設定
                t_mitsumori::where('DetailNo', '>=', $DetailNo)
                    ->where("AdQuoNo", $this->AdQuoNo)
                    ->where("Category_ID", $this->Category_ID)
                    ->update(['DetailNo' => DB::Raw("DetailNo + 1")]);
                // No再設定
                $dataNoChange[] = $id;
                t_mitsumori::whereIn('id', $dataNoChange)
                    ->update(['No' => DB::Raw("No + 1")]);
            } else {
                // 最下行に登録ボタン
                $data["DetailNo"] = t_mitsumori::where("AdQuoNo", $this->AdQuoNo)->where("Category_ID", $this->Category_ID)->max("DetailNo") + 1;
            }
            $data["AdQuoNo"] = $this->AdQuoNo;
            $data["Category_ID"] = $this->Category_ID;
            $id = t_mitsumori::insertGetId($data);
        }
        if ($dataIS) {
            $dataIS = array_map(function ($arr) use ($id) {
                return $arr + ['Mitsumori_ID' => $id];
            }, $dataIS);
            t_mitsumori_meisai::insert($dataIS);
        }
    }

    /**
     * string to int, float
     * param $number
     * return 配列
     */
    public function getNumber($number)
    {
        if (!$number) return NULL;
        return preg_replace("/[^-0-9\.]/", "", $number);
    }


    /**
     * 工事仕様の選択画面表示時
     * param Request $rq
     * return 配列
     */
    public function getMitsumoreMeisai(Request $rq)
    {
        $shiyo = new ShiyoController;
        if ($rq->filled("Mitsumori_ID")) {
            return [
                "status" => true,
                "data" => $shiyo->getListShiyoSelected($rq->Mitsumori_ID),
                "dataShiyo" => $shiyo->getListShiyo($rq)
            ];
        }
        return [
            "status" => false
        ];
    }

    /**
     * カテゴリ選択後、見積詳細一覧取得
     * param $Category_ID　カテゴリID
     * return 配列
     */
    public function getListMitsumore($Category_ID)
    {
        $cate = $this->_setCategorySelected($Category_ID);
        return [
            "status" => true,
            "data" => $this->_getList(),
            "cate" => $cate,
        ];
    }

    /**
     * カテゴリ選択後、選択行設定
     * param $Category_ID　カテゴリID
     * return 配列
     */
    private function _setCategorySelected($Category_ID)
    {
        $cate = t_category::select("AdQuoNo", "Category_ID")->where("Category_ID", $Category_ID)->first();
        if ($cate) {
            $this->AdQuoNo = $cate->AdQuoNo;
            $this->Category_ID = $cate->Category_ID;
            Session::put("AdQuoNo", $cate->AdQuoNo);
            Session::put("Category_ID", $cate->Category_ID);
        }
        return $cate;
    }

    /**
     * 見積明細一覧取得
     * param array $whereInId id一覧の検索条件
     * param bool $jsonFLag デフォルト true
     * param string $column デフォルト null
     * return 配列/json
     */
    private function _getList(array $whereInId = [], bool $jsonFLag = true, string $column = "")
    {
        $list =  t_mitsumori::select("*");
        if (!$whereInId) {
            $list =  t_mitsumori::select(
                t_mitsumori::getTableName() . ".id",
                t_mitsumori::getTableName() . ".DetailNo",
                t_mitsumori::getTableName() . ".No",
                t_mitsumori::getTableName() . ".FirstName",
                t_mitsumori::getTableName() . ".StandDimen",
                t_mitsumori::getTableName() . ".Unit",
                t_mitsumori::getTableName() . ".Quantity",
                t_mitsumori::getTableName() . ".UnitPrice",
                t_mitsumori::getTableName() . ".Amount",
                t_mitsumori::getTableName() . ".Note",
                t_mitsumori::getTableName() . ".M_EstUP1",
                t_mitsumori::getTableName() . ".M_MaterUP1",
                t_mitsumori::getTableName() . ".M_OutsUP1",
                t_mitsumori::getTableName() . ".MaterCost",
                t_mitsumori::getTableName() . ".LaborCost",
                t_mitsumori::getTableName() . ".LiftingCost",
                t_mitsumori::getTableName() . ".SiteExpense",
                t_mitsumori::getTableName() . ".OutsCost",
                t_mitsumori::getTableName() . ".LaborOuts",
                t_mitsumori::getTableName() . ".MaterUnitPrice",
                t_mitsumori::getTableName() . ".LaborUnitPrice",
                t_mitsumori::getTableName() . ".OutsUnitPrice",
                t_mitsumori::getTableName() . ".MaterScaleFactor",
                "S.Koshu_ID",
                "S.Bui_ID",
                "B.Bui_Nm",
                "K.Bui_Kbn_ID",
                "K.Koshu_Cd",
                "A1.Shiyo_Nm as SpecName1",
                "A2.Shiyo_Nm as SpecName2",
                "SK.Shiyo_Shubetsu_Nm as Shubetsu_Nm",
                "SK.Shiyo_Shubetsu_ID"
            )
                ->selectRaw("CASE WHEN A3.Shiyo_Nm IS NOT NULL 
                                THEN '有'
                                ELSE ''
                        END AS SpecName3")
                ->selectRaw("CASE WHEN No != 0 THEN No END AS No")
                ->leftJoin($this->_getRawSortNo(1), "A1.Mitsumori_ID", t_mitsumori::getTableName() . ".id")
                ->leftJoin($this->_getRawSortNo(2), "A2.Mitsumori_ID",  t_mitsumori::getTableName() . ".id")
                ->leftJoin($this->_getRawSortNo(3), "A3.Mitsumori_ID",  t_mitsumori::getTableName() . ".id")
                ->leftJoin(m_shiyo::getTableName("S"), "S.Shiyo_ID", "A1.Shiyo_ID")
                ->leftJoin(m_koshu::getTableName("K"), "K.Koshu_ID",  "S.Koshu_ID")
                ->leftJoin(m_bui::getTableName("B"), "B.Bui_ID", "S.Bui_ID")
                ->leftJoin(m_shiyo_shubetsu::getTableName("SK"), "SK.Shiyo_Shubetsu_ID", "S.Shiyo_Shubetsu_ID");
        }
        $list = $list->where(t_mitsumori::getTableName() . ".AdQuoNo", $this->AdQuoNo)
            ->where(t_mitsumori::getTableName() . ".Category_ID", $this->Category_ID)
            ->when($whereInId, function ($query, $whereInId) {
                return $query->whereIn(t_mitsumori::getTableName() . ".id", $whereInId);
            })
            ->when($column == "", function ($query) {
                return $query->orderBy(t_mitsumori::getTableName() . ".DetailNo") // 明細No
                    ->orderBy(t_mitsumori::getTableName() . ".No") // No
                    ->get();
            })
            ->when($column, function ($query, $column) {
                return $query->sum($column);
            });

        if ($jsonFLag) return $list->toJson();
        return $list;
    }

    private function _getRawSortNo(int $number)
    {
        return DB::raw("(SELECT ISs.Mitsumori_ID, ISs.Sort_No, ISs.Shiyo_ID, S.Shiyo_Nm FROM " . t_mitsumori_meisai::getTableName("ISs") . "  LEFT JOIN " . m_shiyo::getTableName("S") . " ON S.Shiyo_ID = ISs.Shiyo_ID WHERE ISs.Sort_No = $number ) as A$number");
    }

    /**
     * 見積明細更新
     * param Request $rq
     * return 配列
     */
    public function action(Request $rq)
    {
        $action = $rq->action;
        $dataCopy = $rq->dataCopy;
        $dataSelected = $rq->dataSelected;
        $dataNoChange = $rq->dataNoChange;

        try {
            DB::beginTransaction();
            $data = [
                "DetailNoUpdate" => null,
                "NoUpdate" => NULL,
                "dataNew" => []
            ];
            // 貼り付け
            if ($action == config("const.cmd.cmdPaste.cmd")) {
                $data = $this->_setPaste($data, $dataCopy, $dataSelected);
            }

            // コピーした行の挿入
            if ($action == config("const.cmd.cmdPasteNew.cmd")) {
                $data = $this->_setPasteNew($data, $dataCopy, $dataSelected);
            }
            // 挿入
            if ($action == config("const.cmd.cmdNew.cmd") || $action == config("const.cmd.cmdNewEdit.cmd")) {
                $data = $this->_setNew($data, $dataSelected);
            }
            // 削除
            if ($action == config("const.cmd.cmdDel.cmd")) {
                $data = $this->_setDel($data, $dataSelected);
            }

            // 小計行追加
            if (
                $action == config("const.cmd.cmdTotal.cmd")
                || $action == config("const.cmd.cmdTotalKe.cmd")
                || $action == config("const.cmd.cmdTotalGo.cmd")
            ) {
                $data = $this->_setTotal($data, $dataSelected, $action);
            }

            // 明細No:再設定
            if ($data["DetailNoUpdate"]) {
                t_mitsumori::where('DetailNo', '>=', $dataSelected["DetailNo"][0])
                    ->where("AdQuoNo", $this->AdQuoNo)
                    ->where("Category_ID", $this->Category_ID)
                    ->update(['DetailNo' => DB::Raw("DetailNo" . $data["DetailNoUpdate"])]);
            }
            // 7列目の「No」更新
            if ($dataNoChange && $data["NoUpdate"]) {
                t_mitsumori::whereIn('id', $dataNoChange)
                    ->where("AdQuoNo", $this->AdQuoNo)
                    ->where("Category_ID", $this->Category_ID)
                    ->update(['No' => DB::Raw("No" . $data["NoUpdate"])]);
            }
            if ($data["dataNew"]) {
                t_mitsumori::insert($data["dataNew"]);
            }
            $this->_resetAllTotal();
            DB::commit();
            return [
                "status" => true,
                "data" => $this->_getList(),
                "Category_ID" => $this->Category_ID
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
     * 全て計再設定
     */
    private function _resetAllTotal()
    {
        // 小計再設定
        $this->_resetTotal(config("const.cmd.cmdTotal.text"));
        // 計再設定
        $this->_resetTotal(config("const.cmd.cmdTotalKe.text"));
        // 合計再設定
        $this->_resetTotal(config("const.cmd.cmdTotalGo.text"));
    }

    /**
     * 計再設定
     * params string $cmdText コマンドテキスト
     */
    private function _resetTotal(string $cmdText)
    {
        $keyTotal = "Amount";
        $list = t_mitsumori::select("id", "DetailNo", "AdQuoNo", "Category_ID")
            ->where("AdQuoNo", $this->AdQuoNo)
            ->where("Category_ID", $this->Category_ID)
            ->where("FirstName", $cmdText)
            ->orderBy("DetailNo") // 明細No
            ->get()->toArray();
        if ($list) {
            $preDetailNo = 0;
            foreach ($list as $k => $l) {
                $list[$k][$keyTotal] =    $this->_getSum($keyTotal, $preDetailNo, $l["DetailNo"] - 1);
                $preDetailNo = $l["DetailNo"] + 1;
            }
            t_mitsumori::upsert($list, "id");
        }
    }

    /**
     * 計取得
     * param string $key
     * param int $DetailNoStart
     * param int $DetailNoENd
     * return int
     */
    private function _getSum(string $key, int $DetailNoStart, int $DetailNoEnd)
    {
        return t_mitsumori::where("AdQuoNo", $this->AdQuoNo)
            ->where("Category_ID", $this->Category_ID)
            ->whereBetween('DetailNo', [$DetailNoStart, $DetailNoEnd])
            ->where(function ($q) {
                $q->whereNotIn("FirstName", [config("const.cmd.cmdTotal.text"), config("const.cmd.cmdTotalKe.text"), config("const.cmd.cmdTotalGo.text")]);
                $q->orWhereNull("FirstName");
            })->sum($key);
    }
    /**
     * 貼り付け機能
     * param $data 結果データ
     * param  string $cmdKey
     * return 配列
     */
    private function _setTotal($data,  $dataSelected, string $cmdKey)
    {
        // 小計行追加
        $data["dataNew"][] = [
            "AdQuoNo" => $this->AdQuoNo,
            "Category_ID" => $this->Category_ID,
            "DetailNo" => $dataSelected["first"] + 1,
            "FirstName" => config("const.cmd.$cmdKey.text"),
            "SpecName1" => config("const.cmd.$cmdKey.text"),
        ];

        $data["NoUpdate"] = ' - ' . $dataSelected["firstNo"] - 1;
        $data["DetailNoUpdate"] = ' + 1';
        return $data;
    }

    /**
     * 貼り付け機能
     * param $data 結果データ
     * param $dataCopy　コピーデータ
     * param $dataSelected　選択データ
     * return 配列
     */
    private function _setPaste($data, $dataCopy, $dataSelected)
    {
        // コピーデータ取得
        $list = $this->_getList($dataCopy["id"], false)->toArray();

        // 新しいデータ更新
        $no = $dataSelected["prevItemNo"] ? ($dataSelected["prevItemNo"]) : 0;
        foreach ($list as $k => $l) {
            unset($l["id"]);
            unset($l["DetailNo"]);
            // 7列目の「No」更新
            if ($l["No"]) {
                $no++;
            } else {
                $no = 0;
            }
            $l["No"] = $no;
            t_mitsumori::where("id", $dataSelected["id"][$k])->update($l);
            t_mitsumori::find($dataSelected["id"][$k])->meisais()->delete();
            t_mitsumori_meisai::insert(t_mitsumori_meisai::select("Shiyo_ID", "AtariSuryo", "Sort_No")
                ->selectRaw($dataSelected["id"][$k] . " as Mitsumori_ID")->where("Mitsumori_ID", $dataCopy["id"][$k])->get()->toArray());
        }

        $data["NoUpdate"] = ' - ' . $dataSelected["nextItemNo"] - $no;
        return $data;
    }

    /**
     * コピーした行の挿入機能
     * param $data 結果データ
     * param $dataCopy　コピーデータ
     * param $dataSelected　選択データ
     * return 配列
     */
    private function _setPasteNew($data, $dataCopy, $dataSelected)
    {
        // コピーデータ取得
        $list = $this->_getList($dataCopy["id"], false)->toArray();
        $no = $dataSelected["prevItemNo"] ? ($dataSelected["prevItemNo"]) : 0;
        $DetailNo = $dataSelected["DetailNo"][0];
        // 明細No:再設定
        if (count($dataCopy["id"])) {
            t_mitsumori::where('DetailNo', '>=', $dataSelected["DetailNo"][0])
                ->where("AdQuoNo", $this->AdQuoNo)
                ->where("Category_ID", $this->Category_ID)
                ->update(['DetailNo' => DB::Raw("DetailNo" .  ' + ' . count($dataCopy["id"]))]);
        }
        foreach ($list as $k => $l) {
            unset($l["id"]);
            $l["DetailNo"] = $DetailNo;
            $DetailNo++;
            // 7列目の「No」更新
            if ($l["No"]) {
                $no++;
            } else {
                $no = 0;
            }
            $l["No"] = $no;
            $id = t_mitsumori::insertGetId($l);
            t_mitsumori_meisai::insert(t_mitsumori_meisai::select("Shiyo_ID", "AtariSuryo", "Sort_No")
                ->selectRaw($id . " as Mitsumori_ID")->where("Mitsumori_ID", $dataCopy["id"][$k])->get()->toArray());
        }
        $data["NoUpdate"] = ' - ' . $dataSelected["firstNo"] - $no - 1;
        return $data;
    }


    /**
     * 挿入機能
     * param $data 結果データ
     * param $dataSelected　選択データ
     * return 配列
     */
    private function _setNew($data, $dataSelected)
    {
        if (isset($dataSelected["DetailNo"])) {
            foreach ($dataSelected["DetailNo"] as $item) {
                $data["dataNew"][] = [
                    "AdQuoNo" => $this->AdQuoNo,
                    "Category_ID" => $this->Category_ID,
                    "DetailNo" => $item,
                    "No" => NULL // No：再設定
                ];
            }
            $data["DetailNoUpdate"] = ' +  ' . $dataSelected["count"];
            $data["NoUpdate"] = ' - ' . (reset($dataSelected["No"]) - 1);
        } else {
            $data["dataNew"][] = [
                "AdQuoNo" => $this->AdQuoNo,
                "Category_ID" => $this->Category_ID,
                "DetailNo" => 1,
                "No" => NULL // No：再設定
            ];
        }
        return $data;
    }


    /**
     * 削除機能
     * param $data 結果データ
     * param $dataSelected　選択データ
     * return 配列
     */
    private function _setDel($data, $dataSelected)
    {
        // 行削除
        t_mitsumori::whereIn("id", $dataSelected["id"])->delete();
        t_mitsumori_meisai::whereIn("Mitsumori_ID", $dataSelected["id"])->delete();
        $data["DetailNoUpdate"] = ' - ' . $dataSelected["count"];
        $data["NoUpdate"] = ' - ' . (end($dataSelected["No"]) ? end($dataSelected["No"]) : 0) - ($dataSelected["prevItemNo"] ? $dataSelected["prevItemNo"] : 0);
        return $data;
    }

    /**
     * 見積明細一覧画面フォーマット取得
     * return ヘーダ一覧
     */
    private function _getTableHeader()
    {
        return json_encode([
            "DetailNo" => [
                "name" => "行No",
                "class" => "align-center-im",
                "width" => 40
            ],
            "Koshu_Cd" => [
                "name" => "種別",
                "class" => "wj-align-center",
                "width" => 30
            ],
            "Bui_Nm" => [
                "name" => "部位名",
                "class" => "wj-align-center",
                "width" => 60
            ],

            "Shubetsu_Nm" => [
                "name" => "材質名",
                "class" => "wj-align-center",
                "width" => 60
            ],

            "SpecName1" => [
                "name" => "仕様名1",
                "class" => "wj-align-center",
                "width" => 120
            ],
            "SpecName2" => [
                "name" => "仕様名2",
                "class" => "wj-align-center",
                "width" => 120
            ],
            "SpecName3" => [
                "name" => "仕様名3~",
                "class" => "wj-align-center",
                "width" => 65
            ],
            "No" => [
                "name" => "No",
                "class" => "wj-align-center align-center-im",
                "width" => 40
            ],
            "FirstName" => [
                "name" => "名称",
                "class" => "text-danger wj-align-center",
                "width" => 120
            ],
            "StandDimen" => [
                "name" => "規格・寸法",
                "class" => "text-danger wj-align-center",
                "width" => 160
            ],
            "Unit" => [
                "name" => "単位",
                "class" => "text-danger wj-align-center",
                "width" => 40
            ],
            "Quantity" => [
                "name" => "数量",
                "class" => "wj-align-right",
                "width" => 60
            ],
            "UnitPrice" => [
                "name" => "単価",
                "class" => "wj-align-right",
                "width" => 60
            ],
            "Amount" => [
                "name" => "金額",
                "class" => "wj-align-right",
                "width" => 100
            ],
            "Note" => [
                "name" => "備考",
                "class" => " wj-align-center",
                "width" => 120
            ],
            "M_EstUP1" => [
                "name" => "見積原価*",
                "class" => "wj-align-right",
                "width" => 90
            ],
            "M_MaterUP1" => [
                "name" => "利益高*",
                "class" => "wj-align-right",
                "width" => 60
            ],
            "M_OutsUP1" => [
                "name" => "利益率*",
                "class" => "wj-align-right",
                "width" => null
            ],
            "MaterCost" => [
                "name" => "材料費",
                "class" => "wj-align-right",
                "width" => 100
            ],
            "LaborCost" => [
                "name" => "労務費",
                "class" => "wj-align-right",
                "width" => 100
            ],
            "LiftingCost" => [
                "name" => "揚重費",
                "class" => "wj-align-right",
                "width" => 100
            ],
            "SiteExpense" => [
                "name" => "現場経費",
                "class" => "wj-align-right",
                "width" => 100
            ],
            "OutsCost" => [
                "name" => "外注費",
                "class" => "wj-align-center",
                "width" => 70
            ],
            "LaborOuts" => [
                "name" => "労務•外注",
                "class" => "wj-align-center bg-green",
                "width" => 60
            ],
            "MaterUnitPrice" => [
                "name" => "材料単価*",
                "class" => "wj-align-right bg-green",
                "width" => 50
            ],
            "LaborUnitPrice" => [
                "name" => "労務単価*",
                "class" => "wj-align-right bg-green",
                "width" => 50
            ],
            "OutsUnitPrice" => [
                "name" => "外注単価",
                "class" => "wj-align-right bg-green",
                "width" => 70
            ],
            "MaterScaleFactor" => [
                "name" => "材料増減係数",
                "class" => "wj-align-right bg-green",
                "width" => 50
            ]
        ]);
    }
}
