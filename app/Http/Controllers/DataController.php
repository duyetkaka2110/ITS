<?php

namespace App\Http\Controllers;

use App\Models\t_mitsumori;
use App\Models\t_mitsumori_meisai;
use App\Models\m_shiyo;
use App\Models\m_zairyo;
use App\Models\m_tani;
use App\Models\m_bui;
use App\Models\m_bui_kbn;
use App\Models\m_koshu;
use App\Models\m_maker;
use App\Models\m_quotation;
use App\Models\m_seko_tanka;
use App\Models\m_shiyo_shubetsu;
use App\Models\m_shiyo_shubetsu_kbn;
use App\Models\m_zairyo_kosei;
use App\Models\m_zairyo_shubetsu;
use App\Models\m_zairyo_value;

class DataController extends Controller
{
    protected $appPath = 'app/';
    protected $fileExe = ".csv";

    public function readAll()
    {
        // $this->_read($this->appPath . m_seko_tanka::getTableName() . $this->fileExe, m_seko_tanka::select("*"), m_seko_tanka::getTableName());
        // $this->_read($this->appPath . m_bui_kbn::getTableName() . $this->fileExe, m_bui_kbn::select("*"), m_bui_kbn::getTableName());

        // $this->_read($this->appPath . m_shiyo::getTableName() . $this->fileExe, m_shiyo::select("*"), m_shiyo::getTableName());

        // $this->_read($this->appPath . m_zairyo::getTableName() . $this->fileExe, m_zairyo::select("*"), m_zairyo::getTableName());
        // $this->_read($this->appPath . m_tani::getTableName() . $this->fileExe, m_tani::select("*"), m_tani::getTableName());

        // $this->_read($this->appPath . t_mitsumori::getTableName() . $this->fileExe, t_mitsumori::select("*"), t_mitsumori::getTableName());
        // $this->_read($this->appPath . m_bui::getTableName() . $this->fileExe, m_bui::select("*"), m_bui::getTableName());
        // $this->_read($this->appPath . m_koshu::getTableName() . $this->fileExe, m_koshu::select("*"), m_koshu::getTableName());

        // $this->_read($this->appPath . m_zairyo_shubetsu::getTableName() . $this->fileExe, m_zairyo_shubetsu::select("*"), m_zairyo_shubetsu::getTableName());
        // $this->_read($this->appPath . m_shiyo_shubetsu::getTableName() . $this->fileExe, m_shiyo_shubetsu::select("*"), m_shiyo_shubetsu::getTableName());
        // $this->_read($this->appPath . m_shiyo_shubetsu_kbn::getTableName() . $this->fileExe, m_shiyo_shubetsu_kbn::select("*"), m_shiyo_shubetsu_kbn::getTableName());

        // $this->_read($this->appPath . m_zairyo_kosei::getTableName() . $this->fileExe, m_zairyo_kosei::select("*"), m_zairyo_kosei::getTableName());

        // $this->_read($this->appPath . m_zairyo_value::getTableName() . $this->fileExe, m_zairyo_value::select("*"), m_zairyo_value::getTableName());

        // $this->_read($this->appPath . m_maker::getTableName() . $this->fileExe, m_maker::select("*"), m_maker::getTableName());
        $this->_read($this->appPath . m_quotation::getTableName() . $this->fileExe, m_quotation::select("*"), m_quotation::getTableName());
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
    public function readCsvMitsumori()
    {
        $this->_read($this->appPath . t_mitsumori::getTableName() . $this->fileExe, t_mitsumori::select("*"), t_mitsumori::getTableName());
    }

    /**
     * CSVファイルから読込し、DBに追加する
     */
    private function _read($filename, $table, $tblName)
    {
        echo "<br>starting....";
        $table->truncate();
        if ($tblName == t_mitsumori::getTableName()) {
            t_mitsumori_meisai::truncate();
        }
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
                    if ($tblName == t_mitsumori::getTableName() || $tblName == m_quotation::getTableName()) {
                        $temp[$k] = str_replace(",", "", strval($temp[$k]));
                        $temp[$k] = str_replace("¥", "", strval($temp[$k]));
                    }
                }
            }
            if (isset($temp["UPDATE_DATE"]))
                $temp["UPDATE_DATE"] = \Carbon\Carbon::parse($temp["UPDATE_DATE"])->format("Y-m-d");
            // m_quotation
            if (isset($temp["Quo_Date"]) && $temp["Quo_Date"]) {
                $temp["Quo_Date"] = self::j2e($temp["Quo_Date"], "y-m-d");
            }
            if (isset($temp["Change_Date"]) && $temp["Change_Date"])
                $temp["Change_Date"] =  self::j2e($temp["Change_Date"], "y-m-d");
            if (isset($temp["Construct_Start"]) && $temp["Construct_Start"])
                $temp["Construct_Start"] =  self::j2e($temp["Construct_Start"], "y-m-d");
            if (isset($temp["Construct_End"]) && $temp["Construct_End"])
                $temp["Construct_End"] =  self::j2e($temp["Construct_End"], "y-m-d");

            if ($tblName == t_mitsumori::getTableName()) {
                if (isset($temp["Unit"])) $temp["Unit_ID"] = m_tani::where("Tani_Nm", $temp["Unit"])->value("Tani_ID");
                if (isset($temp["UnitOrg"])) $temp["UnitOrg_ID"] = m_tani::where("Tani_Nm", $temp["UnitOrg"])->value("Tani_ID");
                if (isset($temp["Type"])) {
                    $temp["Type"] = m_koshu::where("Koshu_Cd", explode(" ", $temp["Type"])[0])->value("Koshu_ID");
                }
                if (isset($temp["PartName"])) {
                    $temp["PartName"] = m_bui::where("Bui_Nm", $temp["PartName"])->value("Bui_ID");
                }
            }

            if ($tblName == m_zairyo_kosei::getTableName()) {
                // マスタで定義している材料、仕様の構成を変更することはできない
                $temp["Old_Flg"] = 1;
            }
            $id = $table->insertGetId($temp);

            if ($tblName == t_mitsumori::getTableName()) {
                if (isset($temp["SpecName1"])) {
                    t_mitsumori_meisai::insert([
                        "Shiyo_ID" => m_shiyo::where("Shiyo_Nm", $temp["SpecName1"])->value("Shiyo_ID"),
                        "Mitsumori_ID" => $id,
                        "Sort_No" => 1
                    ]);
                }
                if (isset($temp["SpecName2"])) {
                    t_mitsumori_meisai::insert([
                        "Shiyo_ID" => m_shiyo::where("Shiyo_Nm", $temp["SpecName2"])->value("Shiyo_ID"),
                        "Mitsumori_ID" => $id,
                        "Sort_No" => 2
                    ]);
                }
            }
            $count++;
            if ($count == 500) {
                // dd($temp);
                // break;
            }
        }

        fclose($file);
        echo "done: " . $count;
    }
    public static $era_pattern = array(
        "明治" => 1867,
        "大正" => 1911,
        "昭和" => 1925,
        "平成" => 1988,
        "令和" => 2018
    );

    // 和暦（元号）を西暦に変換 : 昭和４７年３月１日 -> 1972.3.1
    public static function j2e($era_string = "", $format = "")
    {
        if (!$era_string) {
            return null;
        }
        preg_match('/^(明治|大正|昭和|平成|令和)(.+?)年?(.+?)月(.+?)日$/', trim($era_string), $match);
        if ($match && isset(self::$era_pattern[$match[1]])) {
            $num = mb_convert_kana($match[2], "KVa");
            return self::output_ymd_format(
                $format,
                self::$era_pattern[$match[1]] + (int)$num,
                mb_convert_kana($match[3], "KVa"),
                mb_convert_kana($match[4], "KVa")
            );
        } else {
            return null;
        }
    }

    public static function output_ymd_format($format = "", $year, $month, $day)
    {
        if ($format) {
            $format = str_replace("y", $year, $format);
            $format = str_replace("m", $month, $format);
            $format = str_replace("d", $day, $format);
            return $format;
        } else {
            return array(
                "year"  => $year,
                "month" => $month,
                "day"   => $day
            );
        }
    }
}
