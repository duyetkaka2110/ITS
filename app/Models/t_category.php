<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class t_category extends Model
{
    use HasFactory;
    protected $hidden = ["created_at", "updated_at"];
    protected $primaryKey = 'Category_ID';
    public $incrementing = false;
    protected $keyType = 'string';

    public static function boot()
    {
        parent::boot();
        // 削除フォルダが子、孫を持っている場合、一括で削除する
        self::deleting(function (t_category $category) {
            foreach ($category->allChilds as $sub) {
                $sub->delete();
            }
        });
    }
    // string $As 短い名
    public static function getTableName(string $As = "")
    {
        if ($As) {
            return (new self())->getTable() . " AS " . $As;
        }
        return (new self())->getTable();
    }
    public function parent()
    {
        return $this->belongsTo(self::class);
    }

    public function childs()
    {
        return $this->hasMany(self::class, 'Parent_ID')->orderBy("Sort_No");
    }

    public function allParents()
    {
        return $this->parent()->with('allParents');
    }

    public function allChilds()
    {
        return $this->childs()->with('allChilds');
    }
}
