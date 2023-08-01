<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 't_shiyos';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable($this->table))
            Schema::drop($this->table);
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->integer('Shiyo_ID')->index();
            $table->integer('Sort_No');
            $table->string('Shiyo_Nm')->nullable();
            $table->string('Kikaku_Sunpo')->nullable();
            $table->integer('Koshu_ID')->nullable();
            $table->integer('Bui_ID')->nullable();
            $table->integer('Shiyo_Shubetsu_ID')->nullable();
            $table->integer('Seko_Tanka_Kbn_ID')->nullable();
            $table->integer('Maker_ID')->nullable();
            $table->integer('Tani_ID')->nullable();
            $table->integer('TaikaKabe')->nullable();
            $table->integer('Combi_Kbn')->nullable();
            $table->integer('Type_Side')->nullable();
            $table->integer('Sunpo_Loss_ID')->nullable();
            $table->integer('Keisu_Use_Flg')->nullable();
            $table->integer('Category_1_Use_Flg')->nullable();
            $table->integer('Category_2_Use_Flg')->nullable();
            $table->integer('Category_3_Use_Flg')->nullable();
            $table->integer('Category_4_Use_Flg')->nullable();
            $table->integer('Keisu_Kijun_Flg')->nullable();
            $table->integer('Ref_Keisu_Kijun_Shiyo_ID')->nullable();
            $table->integer('Bairitsu')->nullable();
            $table->string('Shiyo_CD')->nullable();
            $table->integer('Proc_Seq_No')->nullable();
            $table->integer('Sort_No_Default')->nullable();
            $table->integer('Recalc_Flg')->nullable();
            $table->integer('Default_Flg')->nullable();
            $table->integer('Disable_Flg')->nullable();
            $table->integer('Delete_Flg')->nullable();
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
