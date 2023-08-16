<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class t_category extends Model
{
    use HasFactory;
    protected $hidden = ["created_at", "updated_at"];

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
        return $this->hasMany(self::class, 'Parent_ID',"id");
    }
    
    public function allParents()
    {
        return $this->parent()->with('allParents');
    }

    public function allChilds()
    {
        return $this->childs()->with('allChilds');
    }
    // public function children(){
    //     return $this->allChilds();
    // }
}
