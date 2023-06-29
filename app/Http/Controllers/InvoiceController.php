<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use DB;

class InvoiceController extends Controller
{
    protected $AdQuoNo = 3;
    protected $DetailType = 1;

    /**
     * 見積明細画面
     */
    public function index()
    {
        $header = $this->_getTableHeader();
        $cmd = json_encode(config("const.cmd"));
        $list = $this->_getList();
        return view("invoice.index2", compact("header", "list", "cmd"));
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
        $list =  Invoice::select("*")
            ->selectRaw("CASE WHEN No != 0 THEN No END AS No")
            ->where("AdQuoNo", $this->AdQuoNo)
            ->where("DetailType", $this->DetailType)
            ->when($whereInId, function ($query, $whereInId) {
                return $query->whereIn("id", $whereInId);
            })
            ->when($column == "", function ($query) {
                return $query->orderBy("DetailNo") // 明細No
                    ->orderBy("No") // No
                    ->get();
            })
            ->when($column, function ($query, $column) {
                return $query->sum($column);
            });


        if ($jsonFLag) return $list->toJson();
        return $list;
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
        $dataBeforeNo = $rq->dataBeforeNo;
        $totalNext = $rq->totalNext;
        $prevListTotal = $rq->prevListTotal;

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
                $data = $this->_setDel($data, $dataBeforeNo, $dataSelected);
            }

            // 小計行追加
            if ($action == config("const.cmd.cmdTotal.cmd")) {
                $data = $this->_setTotal($data, $prevListTotal, $totalNext, $dataSelected);
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
        return Invoice::select("*")
            ->selectRaw("CASE WHEN No != 0 THEN No END AS No")
            ->where("AdQuoNo", $this->AdQuoNo)
            ->where("DetailType", $this->DetailType)
            ->whereBetween('DetailNo', [$DetailNoStart, $DetailNoENd])
            ->sum($key);
    }
    /**
     * 貼り付け機能
     * param $data 結果データ
     * param $prevListTotal
     * param $totalNext
     * return 配列
     */
    private function _setTotal($data, $prevListTotal, $totalNext, $dataSelected)
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
        }

        if (!$no) {
            // 7列目の「No」再設定
            $data["NoUpdate"] = ' - ' . $dataSelected["nextItemNo"] - 1;
        } else {
            if ($dataCopy["haveNoNull"] || $dataSelected["nextItemNo"] != ($no + 1)) {
                $data["NoUpdate"] = ' - ' . $dataSelected["nextItemNo"] - $no - 1;
            }
        }
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
            $data["dataNew"][] = $l;
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
     * param $dataBeforeNo　選択行前
     * param $dataSelected　選択データ
     * return 配列
     */
    private function _setDel($data, $dataBeforeNo, $dataSelected)
    {
        // 行削除
        Invoice::whereIn("id", $dataSelected["id"])->delete();
        $data["DetailNoUpdate"] = ' - ' . $dataSelected["count"];
        if (!$dataSelected["haveNoNull"]) {
            $data["NoUpdate"] = $data["DetailNoUpdate"];
        } else {
            // 削除行一覧のNoがNULLある,また削除行前にNoがNULLある場合
            if ($dataBeforeNo || (!$dataSelected["prevItemNo"] && $dataSelected["nextItemNo"] > 1)) {
                $data["NoUpdate"] = ' - ' . (end($dataSelected["No"]) ? end($dataSelected["No"]) : 0) . ' +' . ($dataBeforeNo ? $dataBeforeNo : 0);
            }
        }
        return $data;
    }
    /**
     * 見積明細一覧画面フォーマット取得
     * return ヘーダ一覧
     */
    private function _getTableHeader()
    {
        return json_encode([
            // "id" => [
            //     "name" => "id",
            //     "class" => "wj-align-center",
            //     "width" => 30
            // ],
            "DetailNo" => [
                "name" => "行No",
                "class" => "wj-align-center",
                "width" => 30
            ],
            "Type" => [
                "name" => "種別",
                "class" => "wj-align-center",
                "width" => 30
            ],
            "PartName" => [
                "name" => "部位名",
                "class" => "",
                "width" => 60
            ],

            "MaterialName" => [
                "name" => "材質名",
                "class" => "",
                "width" => 60
            ],

            "SpecName1" => [
                "name" => "仕様名１",
                "class" => "",
                "width" => 120
            ],
            "SpecName2" => [
                "name" => "仕様名２",
                "class" => "",
                "width" => 120
            ],
            "No" => [
                "name" => "No",
                "class" => "wj-align-center",
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
                "width" => 30
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

    /**
     * 見積明細書ファイルから読込し、DBに追加する
     */
    public function readCsv()
    {
        Invoice::truncate();
        $filePath = storage_path('app/見積明細書.csv');
        $file = fopen($filePath, 'r');

        $header = fgetcsv($file);
        $header[0] = "AdQuoNo";
        $count = 0;
        while ($row = fgetcsv($file)) {
            $temp = array_combine($header, $row);
            unset($temp[63]);
            foreach ($temp as $k => $r) {
                if ($r == '') {
                    unset($temp[$k]);
                } else {
                    $temp[$k] = str_replace(",", "", strval($temp[$k]));
                }
            }
            Invoice::insert($temp);
            $count++;
            if ($count == 10) {
                // break;
            }
        }

        fclose($file);
        dd($count);
    }
}
