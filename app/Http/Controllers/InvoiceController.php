<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\invoice_shiyo;
use App\Models\m_bui;
use App\Models\m_koshu;
use App\Models\m_seko_tanka;
use App\Models\m_shiyo;
use App\Models\m_shiyo_shubetsu;
use App\Models\m_shiyo_shubetsu_kbn;
use App\Models\m_tani;
use DB;
use Form;
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\ZairyoController;
use App\Models\m_maker;

class InvoiceController extends Controller
{
    protected $AdQuoNo = 3;
    protected $DetailType = 1;

    /**
     * 見積明細画面
     */
    public function index(Request $rq)
    {
        $header = $this->_getTableHeader();
        $cmd = json_encode(config("const.cmd"));
        $list = $this->_getList();
        $tanis = m_tani::orderBy("Sort_No")->pluck("Tani_Nm", "Tani_ID")->toArray();

        $headerShiyo = $this->_getTableHeaderShiyo();
        $headerShiyoSelected = $this->_getTableHeaderShiyoSelected();

        $zairyo = new ZairyoController();
        $headerZairyo = $zairyo->getTableHeaderZairyo();
        $headerZairyoSelected = $zairyo->getTableHeaderZairyoSelected();

        return view("invoice.index", compact("header", "list", "cmd", "headerShiyo", "headerShiyoSelected", "tanis", "headerZairyo", "headerZairyoSelected"));
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
            }
            $RowAdd = 1; // 選択行に登録の時
            if ($rq->btn == "btnSaveNew") {
                // 新規行として追加
                $RowAdd = $rq->RowAdd;
            }
            // DB更新
            for ($i = 1; $i <= $RowAdd; $i++) {
                $this->upsertInvoiceShiyo($rq, $data, $dataIS);
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
     * param varray $dataIS　見積詳細＿仕様データ
     */
    public function upsertInvoiceShiyo(Request $rq, array $data, array $dataIS)
    {
        $id = $rq->id;
        if ($rq->btn == "btnSave") {
            Invoice::where("id", $id)->update($data);
            Invoice::find($id)->invoice_shiyos()->delete();
        } else {
            $data["AdQuoNo"] = $this->AdQuoNo;
            $data["DetailType"] = $this->DetailType;
            $data["DetailNo"] = Invoice::max("DetailNo") + 1;
            $id = Invoice::insertGetId($data);
        }
        if ($dataIS) {
            $dataIS = array_map(function ($arr) use ($id) {
                return $arr + ['Invoice_ID' => $id];
            }, $dataIS);
            invoice_shiyo::insert($dataIS);
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
    public function getMitsumoreDetail(Request $rq)
    {
        if ($rq->filled("Invoice_ID")) {
            return [
                "status" => true,
                "data" => $this->getListShiyoSelected($rq->Invoice_ID),
                "dataShiyo" => $this->getListShiyo($rq)
            ];
        }
        return [
            "status" => false
        ];
    }

    /**
     * 工事仕様の選択の緑画面の一覧取得
     * param int $Invoice_ID 見積詳細ID
     * return 配列
     */
    public function getListShiyoSelected(int $Invoice_ID)
    {
        $data = invoice_shiyo::select(
            invoice_shiyo::getTableName() . ".id",
            "S.Shiyo_ID",
            "S.Shiyo_Nm",
            "S.Shiyo_Nm",
            "SS.Shiyo_Shubetsu_Nm",
            "B.Bui_NM",
            "T.Tani_Nm",
            "T.Tani_ID",
            "ST.M_Tanka_IPN",
            "ST.Z_Tanka_IPN",
            "ST.R_Tanka_IPN",
            "M.Maker_ID",
            "M.Maker_Nm"
        )
            ->selectRaw("format(ifnull(AtariSuryo,0),1) as AtariSuryo")
            ->selectRaw("CONCAT(K.Koshu_Cd,'　',K.Koshu_Nm) as Koshu_Nm")
            ->join(m_shiyo::getTableName("S"), "S.Shiyo_ID", invoice_shiyo::getTableName() . ".Shiyo_ID")
            ->leftJoin(m_maker::getTableName("M"), "S.Maker_ID", "M.Maker_ID")
            ->leftJoin(m_shiyo_shubetsu::getTableName("SS"), "S.Shiyo_Shubetsu_ID", "SS.Shiyo_Shubetsu_ID")
            ->join(m_bui::getTableName("B"), "S.Bui_ID", "B.Bui_ID")
            ->join(m_tani::getTableName("T"), "S.Tani_ID", "T.Tani_ID")
            ->join(m_koshu::getTableName("K"), "S.Koshu_ID", "K.Koshu_ID")
            ->join(m_seko_tanka::getTableName("ST"), "S.Shiyo_ID", "ST.Shiyo_ID")
            ->where(invoice_shiyo::getTableName() . ".Invoice_ID", $Invoice_ID)
            ->orderBy(invoice_shiyo::getTableName() . ".Sort_No")
            ->get()->toArray();
        return $data;
    }

    /**
     * 工事仕様の選択の赤画面の一覧取得
     * param Request $rq
     * return 配列/json
     */
    public function getListShiyo(Request $rq)
    {
        $listObj = m_shiyo::select(
            m_shiyo::getTableName() . ".Shiyo_ID",
            m_shiyo::getTableName() . ".Shiyo_Nm",
            "SS.Shiyo_Shubetsu_Nm",
            "B.Bui_NM",
            "T.Tani_Nm",
            "T.Tani_ID",
            "ST.M_Tanka_IPN",
            "ST.Z_Tanka_IPN",
            "ST.R_Tanka_IPN",
            "M.Maker_ID",
            "M.Maker_Nm"
        )
            ->selectRaw("0 as AtariSuryo")
            ->selectRaw("CONCAT(K.Koshu_Cd,'　',K.Koshu_Nm) as Koshu_Nm")
            ->join(m_koshu::getTableName("K"), m_shiyo::getTableName() . ".Koshu_ID", "K.Koshu_ID")
            ->leftJoin(m_maker::getTableName("M"), m_shiyo::getTableName() . ".Maker_ID", "M.Maker_ID")
            ->join(m_bui::getTableName("B"), m_shiyo::getTableName() . ".Bui_ID", "B.Bui_ID")
            ->leftJoin(m_shiyo_shubetsu_kbn::getTableName("SS"), function ($join) {
                $join->on(m_shiyo::getTableName() . ".Shiyo_Shubetsu_ID", "SS.Shiyo_Shubetsu_ID");
                $join->on(m_shiyo::getTableName() . ".Koshu_ID", "SS.Koshu_ID");
            })
            ->join(m_tani::getTableName("T"), m_shiyo::getTableName() . ".Tani_ID", "T.Tani_ID")
            ->join(m_seko_tanka::getTableName("ST"), m_shiyo::getTableName() . ".Shiyo_ID", "ST.Shiyo_ID")
            ->when($rq->filled("Koshu_ID"), function ($q) use ($rq) {
                return $q->where('K.Koshu_ID',  $rq->Koshu_ID);
            })
            ->when($rq->filled("Bui_ID"), function ($q) use ($rq) {
                return $q->where('B.Bui_ID',  $rq->Bui_ID);
            })
            ->when($rq->filled("Shiyo_Shubetsu_ID"), function ($q) use ($rq) {
                return $q->where(m_shiyo::getTableName() . '.Shiyo_Shubetsu_ID',  explode("_", $rq->Shiyo_Shubetsu_ID)[1]);
            })
            ->when($rq->filled("Shiyo_Nm"), function ($q) use ($rq) {
                return $q->where(m_shiyo::getTableName() . '.Shiyo_Nm', 'LIKE',  "%{$rq->Shiyo_Nm}%");
            })
            ->orderBy(m_shiyo::getTableName() . ".Sort_No");
        $perPage = 10;
        $list = $listObj->paginate($perPage);
        $lastPage = $list->lastPage();
        if ($rq->page > $lastPage) {
            // 表示ページが存在しないページとなった場合、最終ページを表示するよう
            Paginator::currentPageResolver(function () use ($lastPage) {
                return $lastPage;
            });
            $list = $listObj->paginate($perPage);
        }
        return  [
            "status" => true,
            "data" => $list->items(),
            "pagi" => $list->links("vendor.pagination.bootstrap-4")->toHtml(),
        ];
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
        $list =  Invoice::select("*");
        if (!$whereInId) {
            $list =  Invoice::select(
                Invoice::getTableName() . ".*",
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
                ->leftJoin($this->_getRawSortNo(1), "A1.Invoice_ID", Invoice::getTableName() . ".id")
                ->leftJoin($this->_getRawSortNo(2), "A2.Invoice_ID",  Invoice::getTableName() . ".id")
                ->leftJoin($this->_getRawSortNo(3), "A3.Invoice_ID",  Invoice::getTableName() . ".id")
                ->leftJoin(m_shiyo::getTableName("S"), "S.Shiyo_ID", "A1.Shiyo_ID")
                ->leftJoin(m_koshu::getTableName("K"), "K.Koshu_ID",  "S.Koshu_ID")
                ->leftJoin(m_bui::getTableName("B"), "B.Bui_ID", "S.Bui_ID")
                ->leftJoin(m_shiyo_shubetsu::getTableName("SK"), "SK.Shiyo_Shubetsu_ID", "S.Shiyo_Shubetsu_ID");
        }
        $list = $list->where(Invoice::getTableName() . ".AdQuoNo", $this->AdQuoNo)
            ->where(Invoice::getTableName() . ".DetailType", $this->DetailType)
            ->when($whereInId, function ($query, $whereInId) {
                return $query->whereIn(Invoice::getTableName() . ".id", $whereInId);
            })
            ->when($column == "", function ($query) {
                return $query->orderBy(Invoice::getTableName() . ".DetailNo") // 明細No
                    ->orderBy(Invoice::getTableName() . ".No") // No
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
        return DB::raw("(SELECT ISs.Invoice_ID, ISs.Sort_No, ISs.Shiyo_ID, S.Shiyo_Nm FROM invoice_shiyos AS ISs LEFT JOIN m_shiyos AS S ON S.Shiyo_ID = ISs.Shiyo_ID WHERE ISs.Sort_No = $number ) as A$number");
    }

    /**
     * 見積明細画面
     */
    public function index2()
    {
        $header = $this->_getTableHeader();
        $list = json_encode(Invoice::select(array_keys(json_decode($header, true)))->take(1)->get());
        $headerkey = (array_keys(json_decode($header, true)));
        $headername = (($header));
        return view("invoice.javascript", compact("headerkey", "headername", "list"));
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
            if ($action == config("const.cmd.cmdNew.cmd")) {
                $data = $this->_setNew($data, $dataSelected);
            }
            // 削除
            if ($action == config("const.cmd.cmdDel.cmd")) {
                $data = $this->_setDel($data, $dataSelected);
            }

            // 小計行追加
            if ($action == config("const.cmd.cmdTotal.cmd")) {
                $data = $this->_setTotal($data, $dataSelected);
            }

            // 明細No:再設定
            if ($data["DetailNoUpdate"]) {
                Invoice::where('DetailNo', '>=', $dataSelected["DetailNo"][0])
                    ->update(['DetailNo' => DB::Raw("DetailNo" . $data["DetailNoUpdate"])]);
            }
            // 7列目の「No」更新
            if ($dataNoChange && $data["NoUpdate"]) {
                Invoice::whereIn('id', $dataNoChange)
                    ->update(['No' => DB::Raw("No" . $data["NoUpdate"])]);
            }
            if ($data["dataNew"]) {
                Invoice::insert($data["dataNew"]);
            }
            // 小計再設定
            $this->_resetTotal();
            DB::commit();
            return [
                "status" => true,
                "data" => $this->_getList()
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
     * 小計再設定
     */
    private function _resetTotal()
    {
        $list = Invoice::select("id", "DetailNo", "Amount", "AdQuoNo", "DetailType")
            ->where("AdQuoNo", $this->AdQuoNo)
            ->where("DetailType", $this->DetailType)
            ->where("FirstName", config("const.cmd.cmdTotal.text"))
            ->orderBy("DetailNo") // 明細No
            ->get()->toArray();
        if ($list) {
            $preDetailNo = 0;
            foreach ($list as $k => $l) {
                $list[$k]["Amount"] =    $this->_getSum("Amount", $preDetailNo, $l["DetailNo"] - 1);
                $preDetailNo = $l["DetailNo"] + 1;
            }
            Invoice::upsert($list, "id");
        }
    }

    /**
     * 小計取得
     * param string $key
     * param int $DetailNoStart
     * param int $DetailNoENd
     * return int
     */
    private function _getSum(string $key, int $DetailNoStart, int $DetailNoENd)
    {
        return Invoice::where("AdQuoNo", $this->AdQuoNo)
            ->where("DetailType", $this->DetailType)
            ->whereBetween('DetailNo', [$DetailNoStart, $DetailNoENd])
            ->sum($key);
    }
    /**
     * 貼り付け機能
     * param $data 結果データ
     * return 配列
     */
    private function _setTotal($data,  $dataSelected)
    {
        // 小計行追加
        $data["dataNew"][] = [
            "AdQuoNo" => $this->AdQuoNo,
            "DetailType" => $this->DetailType,
            "DetailNo" => $dataSelected["first"] + 1,
            "FirstName" => config("const.cmd.cmdTotal.text"),
            "SpecName1" => "小計",
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
            Invoice::where("id", $dataSelected["id"][$k])->update($l);
            Invoice::find($dataSelected["id"][$k])->invoice_shiyos()->delete();
            invoice_shiyo::insert(invoice_shiyo::select("Shiyo_ID", "AtariSuryo", "Sort_No")
                ->selectRaw($dataSelected["id"][$k] . " as Invoice_ID")->where("Invoice_ID", $dataCopy["id"][$k])->get()->toArray());
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
        foreach ($list as $k => $l) {
            unset($l["id"]);
            $l["DetailNo"] = $dataSelected["DetailNo"][$k];
            // 7列目の「No」更新
            if ($l["No"]) {
                $no++;
            } else {
                $no = 0;
            }
            $l["No"] = $no;
            $id = Invoice::insertGetId($l);
            invoice_shiyo::insert(invoice_shiyo::select("Shiyo_ID", "AtariSuryo", "Sort_No")
                ->selectRaw($id . " as Invoice_ID")->where("Invoice_ID", $dataCopy["id"][$k])->get()->toArray());
        }
        $data["NoUpdate"] = ' - ' . $dataSelected["firstNo"] - $no - 1;
        $data["DetailNoUpdate"] = ' + ' . $dataSelected["count"];
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
        foreach ($dataSelected["DetailNo"] as $item) {
            $data["dataNew"][] = [
                "AdQuoNo" => $this->AdQuoNo,
                "DetailType" => $this->DetailType,
                "DetailNo" => $item,
                "No" => NULL // No：再設定
            ];
        }
        $data["DetailNoUpdate"] = ' +  ' . $dataSelected["count"];
        $data["NoUpdate"] = ' - ' . (reset($dataSelected["No"]) - 1);
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
        Invoice::whereIn("id", $dataSelected["id"])->delete();
        invoice_shiyo::whereIn("Invoice_ID", $dataSelected["id"])->delete();
        $data["DetailNoUpdate"] = ' - ' . $dataSelected["count"];
        $data["NoUpdate"] = ' - ' . (end($dataSelected["No"]) ? end($dataSelected["No"]) : 0) - ($dataSelected["prevItemNo"] ? $dataSelected["prevItemNo"] : 0);
        return $data;
    }

    /**
     * 仕様選択した行フォーマット取得
     * return ヘーダ一覧
     */
    private function _getTableHeaderShiyoSelected()
    {
        return json_encode([
            "button" => [
                "name" => " ",
                "class" => "wj-align-center",
                "width" => 100,
            ],
            "note" => [
                "name" => " ",
                "class" => "",
                "width" => 30,
            ],
            "row" => [
                "name" => " ",
                "class" => "wj-align-center",
                "width" => 30,
            ],
            "Koshu_Nm" => [
                "name" => "種別",
                "class" => "wj-align-left-im",
                "width" => 100,
            ],
            "Bui_NM" => [
                "name" => "部位",
                "class" => "align-items-baseline",
                "width" => 80,
            ],
            "Shiyo_Shubetsu_Nm" => [
                "name" => "材質",
                "class" => "",
                "width" => 80,
            ],
            "Shiyo_Nm" => [
                "name" => "仕様",
                "class" => "",
                "width" => 200,
            ],
            "Tani_Nm" => [
                "name" => "単位",
                "class" => "wj-align-center",
                "width" => 50,
            ],
            "AtariSuryo" => [
                "name" => "見積あたり数量",
                "class" => "wj-align-right form-control AtariSuryo",
                "width" => 120,
            ],
            "M_Tanka_IPN2" => [
                "name" => "見積単価",
                "class" => "wj-align-center",
                "width" => 80,
            ],
            "Z_Tanka_IPN2" => [
                "name" => "材料単価",
                "class" => "wj-align-center",
                "width" => 80,
            ],
            "R_Tanka_IPN2" => [
                "name" => "労務単価",
                "class" => "wj-align-center",
                "width" => 80,
            ],
            "a" => [
                "name" => "揚重費",
                "class" => "",
                "width" => 80,
            ],
            "b" => [
                "name" => "現場経費",
                "class" => "",
                "width" => 80,
            ],
            "c" => [
                "name" => "利益率",
                "class" => "",
                "width" => 80,
            ],
        ]);
    }

    /**
     * 仕様フォーマット取得
     * return ヘーダ一覧
     */
    private function _getTableHeaderShiyo()
    {
        // 種別
        $m_koshus = m_koshu::select("Koshu_ID", "Bui_Kbn_ID")
            ->selectRaw("CONCAT(Koshu_Cd,' ',Koshu_Nm) as Koshu_Nm")->orderBy("Sort_No")->get();
        $koshus =  $m_koshus->pluck("Koshu_Nm", "Koshu_ID")->toArray();
        $koshus_attr = $m_koshus->mapWithKeys(function ($item) {
            return [$item->Koshu_ID => ['class' => "a a" . $item->Bui_Kbn_ID, 'data-bui' =>  $item->Bui_Kbn_ID]];
        })->toArray();

        // 部位
        $m_bui = m_bui::select("Bui_Kbn_ID", "Bui_Nm", "Bui_ID")->orderBy("Sort_No")->get();
        $buis = $m_bui->pluck("Bui_Nm", "Bui_ID")->toArray();
        $buis_attr = $m_bui->mapWithKeys(function ($item) {
            return [$item->Bui_ID => ['class' =>  "a a" . $item->Bui_Kbn_ID]];
        })->toArray();

        // 材質
        $m_shiyo = m_shiyo_shubetsu_kbn::select("Koshu_ID", "Shiyo_Shubetsu_ID", "Shiyo_Shubetsu_Nm")
            ->selectRaw("CONCAT(Koshu_ID,'_',Shiyo_Shubetsu_ID) AS Shiyo_Shubetsu_ID")
            ->get();
        $shiyo_shubetsus = $m_shiyo->pluck("Shiyo_Shubetsu_Nm", "Shiyo_Shubetsu_ID")->toArray();
        $shiyo_shubetsus_attr = $m_shiyo->mapWithKeys(function ($item) {
            return [$item->Shiyo_Shubetsu_ID => ['class' =>  "a a" . $item->Koshu_ID]];
        })->toArray();

        return json_encode([
            "Koshu_Nm" => [
                "name" => "種別",
                "class" => "wj-align-left-im",
                "width" => 160,
                "line1" => Form::select('Koshu_ID', ["" => ""] + $koshus, null, ["class" => "form-control p-1 btn-search "], $koshus_attr)->toHtml()
            ],
            "Bui_NM" => [
                "name" => "部位",
                "class" => "align-items-baseline",
                "width" => 140,
                "line1" => Form::select('Bui_ID', ["" => ""] + $buis, null, ["class" => "form-control p-1 btn-search"], $buis_attr)->toHtml()
            ],
            "Shiyo_Shubetsu_Nm" => [
                "name" => "材質",
                "class" => "",
                "width" => 120,
                "line1" => Form::select('Shiyo_Shubetsu_ID', ["" => ""] + $shiyo_shubetsus, null, ["class" => "form-control p-1 btn-search"], $shiyo_shubetsus_attr)->toHtml()
            ],
            "Shiyo_Nm" => [
                "name" => "仕様",
                "class" => "",
                "width" => 378,
                "line1" => "<input type='text' name='Shiyo_Nm' class='w-100 form-control pl-1 btn-search' />"
            ],
            "Tani_Nm" => [
                "name" => "単位",
                "class" => "wj-align-center",
                "width" => 50,
                "line1" => ""
            ],
            "M_Tanka_IPN" => [
                "name" => "見積単価",
                "class" => "wj-align-center",
                "width" => "",
            ],
            "Z_Tanka_IPN" => [
                "name" => "材料単価",
                "class" => "wj-align-center",
                "width" => "",
            ],
            "R_Tanka_IPN" => [
                "name" => "労務単価",
                "class" => "wj-align-center",
                "width" => "",
            ],
        ]);
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
                "class" => "",
                "width" => 60
            ],

            "Shubetsu_Nm" => [
                "name" => "材質名",
                "class" => "",
                "width" => 60
            ],

            "SpecName1" => [
                "name" => "仕様名1",
                "class" => "",
                "width" => 120
            ],
            "SpecName2" => [
                "name" => "仕様名2",
                "class" => "",
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
                "class" => "text-danger",
                "width" => 120
            ],
            "StandDimen" => [
                "name" => "規格・寸法",
                "class" => "text-danger",
                "width" => 120
            ],
            "MakerName" => [
                "name" => "メーカー名",
                "class" => "text-danger",
                "width" => 50
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
                "width" => 90
            ],
            "Note" => [
                "name" => "備考",
                "class" => "note",
                "width" => 120
            ],
            "M_EstUP1" => [
                "name" => "見積単価*",
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
                "width" => 80
            ],
            "LaborCost" => [
                "name" => "労務費",
                "class" => "wj-align-right",
                "width" => 80
            ],
            "LiftingCost" => [
                "name" => "揚重費",
                "class" => "wj-align-right",
                "width" => 60
            ],
            "SiteExpense" => [
                "name" => "現場経費",
                "class" => "wj-align-right",
                "width" => 60
            ],
            "OutsCost" => [
                "name" => "外注費",
                "class" => "wj-align-center",
                "width" => 60
            ],
            "LaborOuts" => [
                "name" => "労務•外注",
                "class" => "wj-align-center bg-green",
                "width" => 50
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
