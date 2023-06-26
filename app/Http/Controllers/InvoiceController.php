<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use DB;

class InvoiceController extends Controller
{
    /**
     * 見積明細画面
     */
    public function index()
    {

        $header = $this->_getTableHeader();
        $key = array_keys(json_decode($header, true));
        $cmd = json_encode(config("const.cmd"));
        $list = Invoice::where("AdQuoNo", 3)
            ->where("DetailType", 1)
            ->orderBy("id")
            ->orderBy("DetailType") // 明細区分
            ->orderBy("DetailNo") // 明細No
            ->orderBy("No")
            ->get();
        $list = json_encode($list);
        return view("invoice.index2", compact("header", "list", "cmd"));
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
    public function action(Request $rq)
    {
        $action = $rq->action;
        $data = $rq->data;
        $dataOldId = $rq->dataOldId;
        $id = null;

        try {
            DB::beginTransaction();
            // 貼り付け
            if ($action == config("const.cmd.cmdPaste.cmd")) {
                unset($data["id"]);
                unset($data["SortNo"]);
                Invoice::where("id", $dataOldId)->update($data);
            }

            // コピーした行の挿入
            if ($action == config("const.cmd.cmdPasteNew.cmd")) {
                unset($data["id"]);
                $id = Invoice::insertGetId($data);
            }
            // 挿入
            if ($action == config("const.cmd.cmdNew.cmd")) {
                $id = Invoice::insertGetId($data);
            }
            // 削除
            if ($action == config("const.cmd.cmdDel.cmd")) {
                Invoice::where("id", $data["id"])->delete();
            }

            DB::commit();
            return [
                "status" => true,
                "id" => $id
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
     * 見積明細一覧画面フォーマット取得
     * return ヘーダ一覧
     */
    private function _getTableHeader()
    {
        return json_encode([
            "id" => [
                "name" => "id",
                "class" => "wj-align-center",
                "width" => 30
            ],
            "SortNo" => [
                "name" => "SortNo",
                "class" => "wj-align-center",
                "width" => 50
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
            "FisrtName" => [
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
                "class" => "wj-align-center",
                "width" => 50
            ],
            "MaterUnitPrice" => [
                "name" => "材料単価*",
                "class" => "wj-align-right",
                "width" => 50
            ],
            "LaborUnitPrice" => [
                "name" => "労務単価*",
                "class" => "wj-align-right",
                "width" => 50
            ],
            "OutsUnitPrice" => [
                "name" => "外注単価",
                "class" => "wj-align-right",
                "width" => 70
            ],
            "MaterScaleFactor" => [
                "name" => "材料増減係数",
                "class" => "wj-align-right",
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
