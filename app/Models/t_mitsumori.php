<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use DB;

class t_mitsumori extends Model
{
    use HasFactory;

    protected $hidden = ["id", "created_at", "updated_at"];


    public function meisais(): HasMany
    {
        return $this->hasMany(t_mitsumori_meisai::class, "Mitsumori_ID", "id");
    }
    // string $As 短い名
    public static function getTableName(string $As = "")
    {
        if ($As) {
            return (new self())->getTable() . " AS " . $As;
        }
        return (new self())->getTable();
    }
    public function scopeWithRowNumber($query, $column = 'id', $order = 'asc')
    {
        $sub = static::selectRaw('*, row_number() OVER () as row_number')
            ->orderBy($column, $order)
            ->toSql();
        $query->from(DB::raw("({$sub}) as sub"));
    }
}
