<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 'm_zairyo_koseis';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable($this->table))
            Schema::drop($this->table);
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->integer('Zairyo_Kosei_ID')->nullable();
            $table->integer('Shiyo_ID')->nullable();
            $table->integer('Sort_No')->nullable();
            $table->integer('Zairyo_ID')->nullable()->index();
            $table->float('Zairyo_Keisu',10,4)->nullable();
            $table->integer('Teisyaku')->nullable();
            $table->integer('Tani_ID')->nullable();
            $table->boolean('Konpo_Calc_Flg')->nullable();
            $table->string('Setsumei')->nullable();
            $table->float('Zairyo_Keisu_Default',10,4)->nullable();
            $table->integer('Teisyaku_Default')->nullable();
            $table->integer('Tani_ID_Default')->nullable();
            $table->boolean('Konpo_Calc_Flg_Default')->nullable();
            $table->string('Setsumei_Default')->nullable();
            $table->integer('Zairyo_Tanka_Ref_Col1')->nullable();
            $table->integer('Zairyo_Tanka_Ref_Col2')->nullable();
            $table->integer('Zairyo_Tanka_Ref_Col3')->nullable();
            $table->integer('Zairyo_Tanka_Ref_Col4')->nullable();
            $table->integer('Sort_No_Default')->nullable();
            $table->boolean('Default_Flg')->nullable();
            $table->boolean('Disable_Flg')->nullable();
            $table->boolean('Delete_Flg')->nullable();
            $table->date('UPDATE_DATE')->nullable();
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
