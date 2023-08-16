<?php

namespace App\Http\Controllers;

use App\Models\t_category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    
    /**
     * 階層画面
     */
    public function index(Request $rq)
    {
        $title = "階層";
        $categories = t_category::whereNull("Parent_ID")->orWhere("Parent_ID", 0)->with("allChilds")->get()->toArray();
        $categories = json_encode($this->_getCategoryTree($categories));
        // dd($categories);
        return view("category.index", compact("title", "categories"));
    }

    public function store(Request $rq){
        
        dd($rq->all());
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
                "id" => $d["id"],
                "parentID" => $d["Parent_ID"] ? $d["Parent_ID"] : 0,
                "state" => [
                    "opened" => true
                ],
                "text" => $d["Category_Nm"],
                "icon" => "fa fa-folder",
                "children" => $this->_getCategoryTree($d["all_childs"])
            ];
        }
        return $tmp;
    }
}
