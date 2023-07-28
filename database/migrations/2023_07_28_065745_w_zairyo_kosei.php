<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 'w_zairyo_koseis';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable($this->table))
            Schema::drop($this->table);
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->integer('Shiyo_ID')->nullable()->index();
            $table->string('Zairyo_Shiyo_Type')->nullable();
            $table->integer('Zairyo_Shiyo_ID')->nullable();
            $table->integer('Shubetsu_ID')->nullable();
            $table->integer('Tani_ID')->nullable();
            $table->integer('Old_Flg')->nullable();
            $table->float('AtariSuryo', 10, 4)->nullable();
            $table->integer('Tanka')->nullable();
            $table->integer('Sort_No')->nullable();
            $table->boolean('Default_Flg')->nullable();
            $table->boolean('Disable_Flg')->nullable();
            $table->boolean('Delete_Flg')->nullable();
            $table->string('UPDATE_STAFF')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop($this->table);
    }
};
