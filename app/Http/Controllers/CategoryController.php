<?php

namespace App\Http\Controllers;

use App\Models\t_category;
use Illuminate\Http\Request;
use DB;

class CategoryController extends Controller
{

    /**
     * 階層画面
     */
    public function index(Request $rq)
    {
        $title = "階層";
        $categories = t_category::whereNull("Parent_ID")
            ->orWhere("Parent_ID", 0)
            ->with("allChilds")
            ->orderBy("Sort_No")
            ->orderBy("allChilds.Sort_No")
            // ->orderBy("Category_ID")
            ->get()->toArray();
        $categories = json_encode($this->_getCategoryTree($categories));

        return view("category.index", compact("title", "categories"));
    }

    public function store(Request $rq)
    {
        try {
            DB::beginTransaction();
            $data = $rq->only("Category_ID", "Category_Nm", "Parent_ID", "Sort_No");
            if ($rq->action != "delete_node") {
                // ドラッグ＆ドロップ
                if ($rq->action == "move_node") {
                    // Sort_No更新
                    $str = ($rq->Parent_ID == $rq->Old_Parent_ID && $rq->Sort_No < $rq->Old_Sort_No) || $rq->Parent_ID != $rq->Old_Parent_ID ? ">=" : ">";
                    t_category::where("Parent_ID", $rq->Parent_ID)
                        ->where("Sort_No", $str, $rq->Sort_No)->update(["Sort_No" => DB::raw("Sort_No+1")]);
                    // Sort_No更新
                    t_category::where("Parent_ID", $rq->Old_Parent_ID)
                        ->where("Sort_No", ">", $rq->Old_Sort_No)->update(["Sort_No" => DB::raw("Sort_No-1")]);
                }
                t_category::upsert($data, ["Category_ID"]);
            } else {
                //　削除ボタン
                if ($rq->Category_ID && t_category::find($data["Category_ID"])) {
                    t_category::find($data["Category_ID"])->delete();
                }
            }
            DB::commit();
            return [
                "status" => true,
                "data" => t_category::pluck("Sort_No"),
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
     * 階層一覧のjsTreeフォーマット取得
     * param $data 階層一覧
     * return 配列
     */
    private function _getCategoryTree($data)
    {
        $tmp = [];
        foreach ($data as $d) {
            $tmp[] = [
                "id" => $d["Category_ID"],
                "parentID" => $d["Parent_ID"] ? $d["Parent_ID"] : 0,
                "text" => $d["Category_Nm"],
                "children" => $this->_getCategoryTree($d["all_childs"])
            ];
        }
        return $tmp;
    }
}
