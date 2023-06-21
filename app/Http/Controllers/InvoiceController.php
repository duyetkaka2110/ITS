<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    
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
