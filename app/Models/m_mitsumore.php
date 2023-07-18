<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class m_mitsumore extends Model
{
    use HasFactory;
    // string $As 短い名
    public static function getTableName(string $As = "")
    {
        if ($As) {
            return (new self())->getTable() . " AS " . $As;
        }
        return (new self())->getTable();
}
