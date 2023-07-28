<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\m_zairyo;
use App\Models\m_zairyo_shubetsu;
use App\Models\m_tani;
use App\Models\w_zairyo_kosei;
use App\Models\m_zairyo_kosei;
use Form;
use DB;
use App\GetMessage;
use App\Models\m_koshu;
use App\Models\m_shiyo;

class ZairyoController extends Controller
{
    public function store(Request $rq)
    {
        try {
            DB::beginTransaction();
            if ($rq->Shiyo_ID) {
                w_zairyo_kosei::where("Shiyo_ID", $rq->Shiyo_ID)->delete();
                $data = [];
                foreach ($rq->Zairyo_Shiyo_ID as $k => $v) {
                    $data[] = [
                        "Shiyo_ID" => $rq->Shiyo_ID,
                        "Zairyo_Shiyo_ID" => $rq->Zairyo_Shiyo_ID[$k],
                        "Zairyo_Shiyo_Type" => $rq->Zairyo_Shiyo_Type[$k],
                        "Shubetsu_ID" => $rq->Shubetsu_ID[$k],
                        "Old_Flg" => $rq->Old_Flg[$k],
                        "AtariSuryo" => $rq->AtariSuryo[$k],
                        "Tani_ID" => $rq->Tani_ID[$k],
                        "Sort_No" => $rq->Sort_No[$k],
                    ];
                }
                if ($data) {
                    w_zairyo_kosei::insert($data);
                }
            }
            DB::commit();
            return [
                "status" => true,
                "data" => $this->getListZairyoSelected($rq),
                "msg" => str_replace("{p}", "反映", GetMessage::getMessageByID("error004"))
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
     * 仕様の構成の編集の材料リスト画面の一覧取得
     * param Request $rq
     * return 配列/json
     */
    public function getListZairyoSelected(Request $rq)
    {
        if ($rq->filled("Shiyo_ID")) {
            $listZairyo =  w_zairyo_kosei::select(
                w_zairyo_kosei::getTableName() . ".*",
                "Z.Zairyo_Nm as Name",
                "T.Tani_Nm",
                "ZS.Zairyo_Shubetsu_Nm as Shubetsu_Nm",
            )
                ->join(m_zairyo::getTableName("Z"), "Z.Zairyo_ID", w_zairyo_kosei::getTableName() . ".Zairyo_Shiyo_ID")
                ->join(m_zairyo_shubetsu::getTableName("ZS"), "Z.Zairyo_Shubetsu_ID", "ZS.Zairyo_Shubetsu_ID")
                ->leftJoin(m_tani::getTableName("T"), "T.Tani_ID", w_zairyo_kosei::getTableName() . ".Tani_ID")
                ->where(w_zairyo_kosei::getTableName() . '.Shiyo_ID',  $rq->Shiyo_ID)
                ->where("Zairyo_Shiyo_Type", "材料");
            $list =  w_zairyo_kosei::select(
                w_zairyo_kosei::getTableName() . ".*",
                "S.Shiyo_Nm as Name",
                "T.Tani_Nm",
            )
                ->selectRaw("CONCAT(K.Koshu_Cd,'　',K.Koshu_Nm) as Shubetsu_Nm")
                ->join(m_shiyo::getTableName("S"), "S.Shiyo_ID", w_zairyo_kosei::getTableName() . ".Zairyo_Shiyo_ID")
                ->leftJoin(m_koshu::getTableName("K"), "K.Koshu_ID", w_zairyo_kosei::getTableName() . ".Shubetsu_ID")
                ->leftJoin(m_tani::getTableName("T"), "T.Tani_ID", w_zairyo_kosei::getTableName() . ".Tani_ID")
                ->where(w_zairyo_kosei::getTableName() . '.Shiyo_ID',  $rq->Shiyo_ID)
                ->where("Zairyo_Shiyo_Type", "仕様")
                ->union($listZairyo)
                ->orderBy("Sort_No")
                ->get()->toArray();
            if (!$list) {
                $list = m_zairyo_kosei::select(
                    m_zairyo_kosei::getTableName() . ".id",
                    m_zairyo_kosei::getTableName() . ".Shiyo_ID",
                    m_zairyo_kosei::getTableName() . ".Zairyo_ID as Zairyo_Shiyo_ID",
                    m_zairyo_kosei::getTableName() . ".Teisyaku",
                    m_zairyo_kosei::getTableName() . ".Old_Flg",
                    "ZS.Zairyo_Shubetsu_ID AS Shubetsu_ID",
                    "ZS.Zairyo_Shubetsu_Nm as Shubetsu_Nm",
                    "Z.Zairyo_Nm as Name",
                    "T.Tani_ID",
                    "T.Tani_Nm",
                )
                    ->selectRaw("'材料' AS Zairyo_Shiyo_Type")
                    ->selectRaw(m_zairyo_kosei::getTableName() . ".Zairyo_Keisu as AtariSuryo")
                    ->join(m_zairyo::getTableName("Z"), "Z.Zairyo_ID", m_zairyo_kosei::getTableName() . ".Zairyo_ID")
                    ->join(m_zairyo_shubetsu::getTableName("ZS"), function ($join) {
                        $join->on("Z.Zairyo_Shubetsu_ID", "ZS.Zairyo_Shubetsu_ID");
                    })
                    ->leftJoin(m_tani::getTableName("T"), "Z.Tani_ID", "T.Tani_ID")
                    ->when($rq->filled("Shiyo_ID"), function ($q) use ($rq) {
                        return $q->where(m_zairyo_kosei::getTableName() . '.Shiyo_ID',  $rq->Shiyo_ID);
                    })
                    ->orderBy(m_zairyo_kosei::getTableName() . ".Sort_No")->get()->toArray();
            }
            return  [
                "status" => true,
                "data" => $list,
                "dataZairyo" => $this->getListZairyo($rq)
            ];
        }
    }


    /**
     * 仕様の構成の編集の材料画面の一覧取得
     * param Request $rq
     * return 配列/json
     */
    public function getListZairyo(Request $rq)
    {
        $perPage = 10;
        $list = m_zairyo::select(
            m_zairyo::getTableName() . ".Zairyo_ID as Zairyo_Shiyo_ID",
            m_zairyo::getTableName() . ".Zairyo_Nm AS Name",
            "ZS.Zairyo_Shubetsu_ID AS Shubetsu_ID",
            "ZS.Zairyo_Shubetsu_Nm AS Shubetsu_Nm",
            "T.Tani_ID",
            "T.Tani_Nm"
        )
            ->selectRaw("'材料' AS Zairyo_Shiyo_Type")
            ->selectRaw("0 as AtariSuryo")
            ->selectRaw("0 as Old_Flg")
            ->join(m_zairyo_shubetsu::getTableName("ZS"), function ($join) {
                $join->on(m_zairyo::getTableName() . ".Zairyo_Shubetsu_ID", "ZS.Zairyo_Shubetsu_ID");
            })
            ->leftJoin(m_tani::getTableName("T"), m_zairyo::getTableName() . ".Tani_ID", "T.Tani_ID")
            // ->join(m_zairyo_value::getTableName("ST"), m_zairyo::getTableName() . ".Shiyo_ID", "ST.Shiyo_ID")
            ->when($rq->filled("Zairyo_Shubetsu_ID"), function ($q) use ($rq) {
                return $q->where('ZS.Zairyo_Shubetsu_ID',  $rq->Zairyo_Shubetsu_ID);
            })
            ->when($rq->filled("Zairyo_Nm"), function ($q) use ($rq) {
                return $q->where(m_zairyo::getTableName() . '.Zairyo_Nm', 'LIKE',  "%{$rq->Zairyo_Nm}%");
            })
            ->orderBy(m_zairyo::getTableName() . ".Sort_No")->paginate($perPage);
        return  [
            "status" => true,
            "data" => $list->items(),
            "pagi" => $list->links("vendor.pagination.bootstrap-4")->toHtml(),
        ];
    }

    /**
     * 材料リストフォーマット取得
     * return ヘーダ一覧
     */
    public function getTableHeaderZairyoSelected()
    {
        return json_encode([
            "btn" => [
                "name" => " ",
                "class" => "wj-align-center",
                "width" => 50,
            ],
            "no" => [
                "name" => " ",
                "class" => "wj-align-center",
                "width" => 40,
            ],
            "Zairyo_Shiyo_Type" => [
                "name" => "材料/仕様",
                "class" => "wj-align-center",
                "width" => 100,
            ],
            "Shubetsu_Nm" => [
                "name" => "種別",
                "class" => "wj-align-left-im",
                "width" => 150,
            ],
            "Name" => [
                "name" => "仕様/材料名称",
                "class" => "",
                "width" => 300,
            ],
            "Tani_Nm" => [
                "name" => "単位",
                "class" => "wj-align-center",
                "width" => 60,
            ],
            "AtariSuryo" => [
                "name" => "あたり数量",
                "class" => "wj-align-right form-control AtariSuryo",
                "width" => 120,
            ],
            "M_Tanka_IPN" => [
                "name" => "材料単価",
                "class" => "wj-align-center",
                "width" => 200,
            ]
        ]);
    }
    /**
     * 材料フォーマット取得
     * return ヘーダ一覧
     */
    public function getTableHeaderZairyo()
    {
        // 種別
        $m_zairyo_shubetsu = m_zairyo_shubetsu::pluck("Zairyo_Shubetsu_Nm", "Zairyo_Shubetsu_ID")->toArray();

        return json_encode([
            "Shubetsu_Nm" => [
                "name" => "材料種別",
                "class" => "wj-align-left-im",
                "width" => 250,
                "line1" => Form::select('Zairyo_Shubetsu_ID', ["" => ""] + $m_zairyo_shubetsu, null, ["class" => "form-control p-1 btn-search-zairyo "])->toHtml()
            ],
            "Name" => [
                "name" => "材料名称",
                "class" => "",
                "width" => 300,
                "line1" => "<input type='text' name='Zairyo_Nm' class='w-100 form-control pl-1 btn-search-zairyo' />"
            ],
            "Tani_Nm" => [
                "name" => "単位",
                "class" => "wj-align-center",
                "width" => "",
                "line1" => ""
            ],
            "M_Tanka_IPN" => [
                "name" => "材料単価",
                "class" => "wj-align-center",
                "width" => 280,
            ]
        ]);
    }
}
