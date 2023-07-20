<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $hidden = ["created_at", "updated_at"];


    public function invoice_shiyos(): HasMany
    {
        return $this->hasMany(invoice_shiyo::class, "Invoice_ID", "id");
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
