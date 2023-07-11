<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\m_shiyo;
use App\Models\m_zairyo;
use App\Models\m_tani;
use App\Models\m_bui;
use App\Models\m_koshu;
use App\Models\m_shiyo_shubetsu;
use App\Models\m_zairyo_shubetsu;

class DataController extends Controller
{
    protected $appPath = 'app/';
    protected $fileExe = ".csv";

    public function readAll(){
        // $this->_read($this->appPath . m_shiyo::getTableName() . $this->fileExe, m_shiyo::select("*"), m_shiyo::getTableName());
        // $this->_read($this->appPath . m_zairyo::getTableName() . $this->fileExe, m_zairyo::select("*"), m_zairyo::getTableName());
        // $this->_read($this->appPath . m_tani::getTableName() . $this->fileExe, m_tani::select("*"), m_tani::getTableName());
        $this->_read($this->appPath . Invoice::getTableName() . $this->fileExe, Invoice::select("*"), Invoice::getTableName());
        $this->_read($this->appPath . m_bui::getTableName() . $this->fileExe, m_bui::select("*"), m_bui::getTableName());
        $this->_read($this->appPath . m_koshu::getTableName() . $this->fileExe, m_koshu::select("*"), m_koshu::getTableName());
        $this->_read($this->appPath . m_zairyo_shubetsu::getTableName() . $this->fileExe, m_zairyo_shubetsu::select("*"), m_zairyo_shubetsu::getTableName());
        $this->_read($this->appPath . m_shiyo_shubetsu::getTableName() . $this->fileExe, m_shiyo_shubetsu::select("*"), m_shiyo_shubetsu::getTableName());
    }

    /**
     * 仕様ファイルから読込し、DBに追加する
     */
    public function readCsvShiyo()
    {
        $this->_read($this->appPath . m_shiyo::getTableName() . $this->fileExe, m_shiyo::select("*"), m_shiyo::getTableName());
    }

    /**
     * 材料ファイルから読込し、DBに追加する
     */
    public function readCsvZairyo()
    {
        $this->_read($this->appPath . m_zairyo::getTableName() . $this->fileExe, m_zairyo::select("*"), m_zairyo::getTableName());
    }
    /**
     * 単位ファイルから読込し、DBに追加する
     */
    public function readCsvTani()
    {
        $this->_read($this->appPath . m_tani::getTableName() . $this->fileExe, m_tani::select("*"), m_tani::getTableName());
    }

    /**
     * 見積詳細ファイルから読込し、DBに追加する
     */
    public function readCsvInvoice()
    {
        $this->_read($this->appPath . Invoice::getTableName() . $this->fileExe, Invoice::select("*"), Invoice::getTableName());
    }

    /**
     * CSVファイルから読込し、DBに追加する
     */
    private function _read($filename, $table, $tblName)
    {
        echo "starting....";
        $table->truncate();
        $filePath = storage_path($filename);
        $file = fopen($filePath, 'r');

        $header = fgetcsv($file);
        $header[0] = trim($header[0], "\xEF\xBB\xBF");
        $count = 0;
        while ($row = fgetcsv($file)) {
            $temp = array_combine($header, $row);
            foreach ($temp as $k => $r) {
                if ($r == '') {
                    unset($temp[$k]);
                } else {
                    if ($tblName == Invoice::getTableName())
                        $temp[$k] = str_replace(",", "", strval($temp[$k]));
                }
            }
            if (isset($temp["UPDATE_DATE"]))
                $temp["UPDATE_DATE"] = \Carbon\Carbon::parse($temp["UPDATE_DATE"])->format("Y-m-d");

            $table->insert($temp);
            $count++;
            if ($count == 500) {
                // dd($temp);
                // break;
            }
        }

        fclose($file);
        echo "done: " . $count;
    }
}