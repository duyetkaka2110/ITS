<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 'm_zairyos';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->integer('Zairyo_ID')->nullable();
            $table->integer('Sort_No')->nullable();
            $table->integer('Shiire_Tanka_Kbn_ID')->nullable();
            $table->integer('Zairyo_Shubetsu_ID')->nullable();
            $table->string('Zairyo_Nm')->nullable();
            $table->string('Kikaku_Sunpo')->nullable();
            $table->integer('Maker_ID')->nullable();
            $table->integer('Tani_ID')->nullable();
            $table->integer('Zairyo_Menseki')->nullable();
            $table->integer('Zairyo_Length')->nullable();
            $table->integer('Zairyo_Atsumi')->nullable();
            $table->integer('Zairyo_Weight')->nullable();
            $table->integer('Sunpo_Width')->nullable();
            $table->integer('Sunpo_Length')->nullable();
            $table->integer('Konpo_Suryo')->nullable();
            $table->string('Zaishitsu')->nullable();
            $table->string('Nintei_Bango')->nullable();
            $table->string('Kikaku_Sunpo_Default')->nullable();
            $table->integer('Maker_ID_Default')->nullable();
            $table->integer('Tani_ID_Default')->nullable();
            $table->integer('Zairyo_Menseki_Default')->nullable();
            $table->integer('Zairyo_Length_Default')->nullable();
            $table->integer('Zairyo_Atsumi_Default')->nullable();
            $table->integer('Zairyo_Weight_Default')->nullable();
            $table->integer('Sunpo_Width_Default')->nullable();
            $table->integer('Sunpo_Length_Default')->nullable();
            $table->integer('Konpo_Suryo_Default')->nullable();
            $table->string('Zaishitsu_Default')->nullable();
            $table->string('Nintei_Bango_Default')->nullable();
            $table->integer('Calc_Type')->nullable();
            $table->integer('Zairyo_Length_Use_Type')->nullable();
            $table->integer('Zairyo_Atsumi_Use_Type')->nullable();
            $table->integer('Zairyo_Weight_Use_Type')->nullable();
            $table->integer('Sunpo_Width_Use_Type')->nullable();
            $table->integer('Sunpo_Length_Use_Type')->nullable();
            $table->string('Catalog_Page_No')->nullable();
            $table->string('Comment')->nullable();
            $table->integer('Sort_No_Default')->nullable();
            $table->string('Zairyo_CD')->nullable();
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
