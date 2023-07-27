<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\m_zairyo;
use App\Models\m_zairyo_shubetsu;
use App\Models\m_tani;
use App\Models\m_zairyo_kosei;
use DB;
use Form;
use Illuminate\Pagination\Paginator;

class ZairyoController extends Controller
{

    /**
     * 仕様の構成の編集の材料リスト画面の一覧取得
     * param Request $rq
     * return 配列/json
     */
    public function getListZairyoSelected(Request $rq)
    {
        $list = m_zairyo_kosei::select(
            m_zairyo_kosei::getTableName() . ".Shiyo_ID",
            m_zairyo_kosei::getTableName() . ".Zairyo_ID",
            m_zairyo_kosei::getTableName() . ".Zairyo_Keisu",
            m_zairyo_kosei::getTableName() . ".Teisyaku",
            "ZS.Zairyo_Shubetsu_ID",
            "ZS.Zairyo_Shubetsu_Nm",
            "Z.Zairyo_Nm",
            "T.Tani_Nm"
        )
            ->selectRaw(m_zairyo_kosei::getTableName() . ".Zairyo_Keisu as AtariSuryo")
            ->join(m_zairyo::getTableName("Z"), "Z.Zairyo_ID", m_zairyo_kosei::getTableName() . ".Zairyo_ID")
            ->join(m_zairyo_shubetsu::getTableName("ZS"), function ($join) {
                $join->on(m_zairyo::getTableName("Z") . ".Zairyo_Shubetsu_ID", "ZS.Zairyo_Shubetsu_ID");
            })
            ->leftJoin(m_tani::getTableName("T"), m_zairyo::getTableName("Z") . ".Tani_ID", "T.Tani_ID")
            ->when($rq->filled("Shiyo_ID"), function ($q) use ($rq) {
                return $q->where(m_zairyo_kosei::getTableName() . '.Shiyo_ID',  $rq->Shiyo_ID);
            })
            ->orderBy(m_zairyo_kosei::getTableName() . ".Sort_No")->get()->toArray();
        return  [
            "status" => true,
            "data" => $list,
        ];
    }


    /**
     * 仕様の構成の編集の材料画面の一覧取得
     * param Request $rq
     * return 配列/json
     */
    public function getListZairyo(Request $rq)
    {
        $listObj = m_zairyo::select(
            m_zairyo::getTableName() . ".Zairyo_ID",
            m_zairyo::getTableName() . ".Zairyo_Nm",
            "ZS.Zairyo_Shubetsu_ID",
            "ZS.Zairyo_Shubetsu_Nm",
            "T.Tani_Nm"
        )
            ->selectRaw("0 as AtariSuryo")
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
            ->orderBy(m_zairyo::getTableName() . ".Sort_No");
        $perPage = 10;
        $list = $listObj->paginate($perPage);
        return  [
            "status" => true,
            "data" => $list->items(),
            "pagi" => $list->links("vendor.pagination.bootstrap-4")->toHtml(),
        ];
    }

    /**
     * 仕様フォーマット取得
     * return ヘーダ一覧
     */
    public function getTableHeaderZairyo()
    {
        // 種別
        $m_zairyo_shubetsu = m_zairyo_shubetsu::pluck("Zairyo_Shubetsu_Nm", "Zairyo_Shubetsu_ID")->toArray();

        return json_encode([
            "Zairyo_Shubetsu_Nm" => [
                "name" => "材料種別",
                "class" => "wj-align-left-im",
                "width" => 250,
                "line1" => Form::select('Zairyo_Shubetsu_ID', ["" => ""] + $m_zairyo_shubetsu, null, ["class" => "form-control p-1 btn-search-zairyo "])->toHtml()
            ],
            "Zairyo_Nm" => [
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
