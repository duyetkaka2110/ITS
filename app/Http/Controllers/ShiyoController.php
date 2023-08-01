<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\t_mitsumori_meisai;
use App\Models\m_shiyo_shubetsu;
use App\Models\m_tani;
use App\Models\m_maker;
use App\Models\m_bui;
use Form;
use App\Models\m_koshu;
use App\Models\m_shiyo;
use App\Models\m_seko_tanka;
use App\Models\m_shiyo_shubetsu_kbn;
use App\Models\t_seko_tanka;
use App\Models\t_shiyo;

class ShiyoController extends Controller
{

    /**
     * 工事仕様の選択の緑画面の一覧取得
     * param int $Invoice_ID 見積詳細ID
     * return 配列
     */
    public function getListShiyoSelected(int $Invoice_ID)
    {

        $data = t_mitsumori_meisai::select(
            t_mitsumori_meisai::getTableName() . ".id",
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
            // 反映
            ->join(t_shiyo::getTableName("S"), "S.Shiyo_ID", t_mitsumori_meisai::getTableName() . ".Shiyo_ID")
            ->leftJoin(m_maker::getTableName("M"), "S.Maker_ID", "M.Maker_ID")
            ->leftJoin(m_shiyo_shubetsu::getTableName("SS"), "S.Shiyo_Shubetsu_ID", "SS.Shiyo_Shubetsu_ID")
            ->join(m_bui::getTableName("B"), "S.Bui_ID", "B.Bui_ID")
            ->join(m_tani::getTableName("T"), "S.Tani_ID", "T.Tani_ID")
            ->join(m_koshu::getTableName("K"), "S.Koshu_ID", "K.Koshu_ID")
            ->join(t_seko_tanka::getTableName("ST"), "S.Shiyo_ID", "ST.Shiyo_ID")
            ->where(t_mitsumori_meisai::getTableName() . ".Invoice_ID", $Invoice_ID)
            ->orderBy(t_mitsumori_meisai::getTableName() . ".Sort_No")
            ->get()->toArray();
        if (!$data) {
            $data = t_mitsumori_meisai::select(
                t_mitsumori_meisai::getTableName() . ".id",
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
                // マスタ
                ->join(m_shiyo::getTableName("S"), "S.Shiyo_ID", t_mitsumori_meisai::getTableName() . ".Shiyo_ID")
                ->leftJoin(m_maker::getTableName("M"), "S.Maker_ID", "M.Maker_ID")
                ->leftJoin(m_shiyo_shubetsu::getTableName("SS"), "S.Shiyo_Shubetsu_ID", "SS.Shiyo_Shubetsu_ID")
                ->join(m_bui::getTableName("B"), "S.Bui_ID", "B.Bui_ID")
                ->join(m_tani::getTableName("T"), "S.Tani_ID", "T.Tani_ID")
                ->join(m_koshu::getTableName("K"), "S.Koshu_ID", "K.Koshu_ID")
                ->join(m_seko_tanka::getTableName("ST"), "S.Shiyo_ID", "ST.Shiyo_ID")
                ->where(t_mitsumori_meisai::getTableName() . ".Invoice_ID", $Invoice_ID)
                ->orderBy(t_mitsumori_meisai::getTableName() . ".Sort_No")
                ->get()->toArray();
        }
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
            "K.Koshu_ID",
            "M.Maker_ID",
            "M.Maker_Nm"
        )
            ->selectRaw("'仕様' AS Zairyo_Shiyo_Type")
            ->selectRaw("0 as AtariSuryo")
            ->selectRaw("0 as Old_Flg")
            ->selectRaw("CONCAT(K.Koshu_Cd,'　',K.Koshu_Nm) as Koshu_Nm")
            ->selectRaw("CASE WHEN tST.M_Tanka_IPN IS NULL 
                            THEN mST.M_Tanka_IPN
                            ELSE tST.M_Tanka_IPN
                    END AS M_Tanka_IPN")
            ->selectRaw("CASE WHEN tST.Z_Tanka_IPN IS NULL 
                            THEN mST.Z_Tanka_IPN
                            ELSE tST.Z_Tanka_IPN
                    END AS Z_Tanka_IPN")
            ->selectRaw("CASE WHEN tST.R_Tanka_IPN IS NULL 
                            THEN mST.R_Tanka_IPN
                            ELSE tST.R_Tanka_IPN
                    END AS R_Tanka_IPN")
            ->join(m_koshu::getTableName("K"), m_shiyo::getTableName() . ".Koshu_ID", "K.Koshu_ID")
            ->leftJoin(m_maker::getTableName("M"), m_shiyo::getTableName() . ".Maker_ID", "M.Maker_ID")
            ->join(m_bui::getTableName("B"), m_shiyo::getTableName() . ".Bui_ID", "B.Bui_ID")
            ->leftJoin(m_shiyo_shubetsu_kbn::getTableName("SS"), function ($join) {
                $join->on(m_shiyo::getTableName() . ".Shiyo_Shubetsu_ID", "SS.Shiyo_Shubetsu_ID");
                $join->on(m_shiyo::getTableName() . ".Koshu_ID", "SS.Koshu_ID");
            })
            ->join(m_tani::getTableName("T"), m_shiyo::getTableName() . ".Tani_ID", "T.Tani_ID")
            ->join(m_seko_tanka::getTableName("mST"), m_shiyo::getTableName() . ".Shiyo_ID", "mST.Shiyo_ID")
            // 反映がある場合
            ->leftJoin(t_seko_tanka::getTableName("tST"), m_shiyo::getTableName() . ".Shiyo_ID", "tST.Shiyo_ID")
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
            ->when($rq->filled("Shiyo_ID"), function ($q) use ($rq) {
                // 仕様の構成が循環参照
                return $q->where(m_shiyo::getTableName() . '.Shiyo_ID', '!=',  $rq->Shiyo_ID);
            })
            ->orderBy(m_shiyo::getTableName() . ".Sort_No");
        $perPage = 10;
        $list = $listObj->paginate($perPage);
        return  [
            "status" => true,
            "data" => $list->items(),
            "pagi" => $list->links("vendor.pagination.bootstrap-4")->toHtml(),
        ];
    }

    /**
     * 仕様選択した行フォーマット取得
     * return ヘーダ一覧
     */
    public function getTableHeaderShiyoSelected()
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
    public function getTableHeaderShiyo()
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
}
