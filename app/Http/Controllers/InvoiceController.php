<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    /**
     * 見積明細画面
     */
    public function index()
    {
        $header = $this->_getTableHeader();
        $list = json_encode(Invoice::select(array_keys($header))->take(10000)->get());
        $headerkey = json_encode(array_keys($header));
        $headername = json_encode(($header));
        return view("invoice.index2", compact("headerkey","headername", "list"));
    }
    /**
     * 見積明細画面
     */
    public function index2()
    {
        $header = $this->_getTableHeader();
        $list = json_encode(Invoice::select(array_keys($header))->take(1)->get());
        $headerkey = json_encode(array_keys($header));
        $headername = json_encode(($header));
        return view("invoice.javascript", compact("headerkey","headername", "list"));
    }

    /**
     * 見積明細一覧画面フォーマット取得
     * return ヘーダ一覧
     */
    private function _getTableHeader()
    {
        return [
            "Type" => [
                "name" => "種別",
                "class" => "wj-align-center",
                "width" => 50
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
                "class" => "",
                "width" => 120
            ],
            "StandDimen" => [
                "name" => "規格・寸法",
                "class" => "",
                "width" => 120
            ],
            "MakerName" => [
                "name" => "メーカー名",
                "class" => "",
                "width" => 30
            ],

            "Unit" => [
                "name" => "単位",
                "class" => "wj-align-center",
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
                "width" => 70
            ],
            "Note" => [
                "name" => "備考",
                "class" => "note",
                "width" => 80
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
                "width" => 60
            ],
            "LaborCost" => [
                "name" => "労務費",
                "class" => "wj-align-right",
                "width" => 60
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
                "width" => 60
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
                "width" => 50
            ],
            "MaterScaleFactor" => [
                "name" => "材料増減係数",
                "class" => "wj-align-right",
                "width" => 50
            ]
        ];
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
