<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class t_mitsumori extends Model
{
    use HasFactory;

    protected $hidden = ["created_at", "updated_at"];


    public function meisais(): HasMany
    {
        return $this->hasMany(t_mitsumori_meisai::class, "Invoice_ID", "id");
    }
    // string $As 短い名
    public static function getTableName(string $As = "")
    {
        if ($As) {
            return (new self())->getTable() . " AS " . $As;
        }
        return (new self())->getTable();
    }
}
