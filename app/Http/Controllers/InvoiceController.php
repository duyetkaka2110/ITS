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
        $cmd = json_encode(config("const.cmd"));
        $list = $this->_getList();
        return view("invoice.index2", compact("header", "list", "cmd"));
    }

    /**
     * 見積明細一覧取得
     */
    private function _getList()
    {
        return Invoice::select("*")
            ->selectRaw("CASE WHEN No != 0 THEN No END AS No")
            ->where("AdQuoNo", 3)
            ->where("DetailType", 1)
            ->orderBy("DetailType") // 明細区分
            ->orderBy("DetailNo") // 明細No
            ->orderBy("No")
            ->get()->toJson();
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
        $dataSelected = $rq->dataSelected;
        $dataNoChange = $rq->dataNoChange;
        $dataBeforeSel = $rq->dataBeforeSel;
        $id = null;
        $NoUpdate = null;
        if (isset($data["created_at"])) {
            unset($data["created_at"]);
        }
        if (isset($data["updated_at"])) {
            unset($data["updated_at"]);
        }

        try {
            DB::beginTransaction();
            $DetailNoUpdate = null;
            // 貼り付け
            if ($action == config("const.cmd.cmdPaste.cmd")) {
                unset($data["id"]);
                unset($data["DetailNo"]);
                unset($data["No"]);
                if ($dataBeforeSel) {
                    $data["No"] = $dataBeforeSel["No"] + 1;
                    $NoUpdate = ' + ' . ($dataBeforeSel["No"] + 1);
                }
                Invoice::where("id", $dataSelected["id"])->update($data);
            }

            // コピーした行の挿入
            if ($action == config("const.cmd.cmdPasteNew.cmd")) {
                unset($data["id"]);
                $data["DetailNo"] = $dataSelected["DetailNo"];
                $data["No"] = $dataSelected["No"];
                $id = Invoice::insertGetId($data);
                $DetailNoUpdate = ' + 1';
            }
            // 挿入
            if ($action == config("const.cmd.cmdNew.cmd")) {
                $dataNew = [
                    "AdQuoNo" => 3,
                    "DetailType" => 1,
                    "DetailNo" => $data["DetailNo"],
                    "No" => 0 // No：再設定
                ];
                $id = Invoice::insertGetId($dataNew);
                $DetailNoUpdate = ' +1 ';
                $NoUpdate = ' - ' . ($data["No"] - 1);
            }
            // 削除
            if ($action == config("const.cmd.cmdDel.cmd")) {
                Invoice::findOrFail($data["id"])->delete();
                $DetailNoUpdate = ' - 1';
                // 削除行のNoがNULL場合
                if ($dataNoChange && !$data["No"]) {
                    $NoUpdate = ' + ' . $dataBeforeSel["No"];
                }
            }

            // 明細No:再設定
            if ($DetailNoUpdate) {
                Invoice::where('DetailNo', '>=', $data["DetailNo"])
                    ->where("id", "!=", $id) // 行追加の外
                    ->update(['DetailNo' => DB::Raw("DetailNo" . $DetailNoUpdate)]);
            }
            // 7列目の「No」更新
            if ($dataNoChange && ($DetailNoUpdate || $NoUpdate)) {
                Invoice::whereIn('id', $dataNoChange)
                    ->where("id", "!=", $id) // 行追加の外
                    ->update(['No' => DB::Raw("No" . ($NoUpdate ? $NoUpdate : $DetailNoUpdate))]);
            }
            DB::commit();
            return [
                "status" => true,
                "id" => $id,
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
