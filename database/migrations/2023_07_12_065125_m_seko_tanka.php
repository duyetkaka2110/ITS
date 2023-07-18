<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 'm_seko_tankas';
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
            $table->integer('ZairyoLossRitsu')->nullable();
            $table->integer('RiekiRitsu')->nullable();
            $table->integer('MitsumoriKakeRitsu')->nullable();
            $table->integer('KanMinZogenRitsu')->nullable();
            $table->integer('ZairyoHi_IPN')->nullable();
            $table->integer('Z_Tanka_IPN')->nullable();
            $table->integer('R_Tanka_IPN')->nullable();
            $table->integer('GenbaKeihiRitsu')->nullable();
            $table->integer('G_Tanka_IPN')->nullable();
            $table->integer('M_Tanka_IPN')->nullable();
            $table->integer('ZairyoHi_JIS')->nullable();
            $table->integer('Z_Tanka_JIS')->nullable();
            $table->integer('R_Tanka_JIS')->nullable();
            $table->integer('G_Tanka_JIS')->nullable();
            $table->integer('M_Tanka_JIS')->nullable();
            $table->integer('ZairyoHi_GIB')->nullable();
            $table->integer('Z_Tanka_GIB')->nullable();
            $table->integer('R_Tanka_GIB')->nullable();
            $table->integer('G_Tanka_GIB')->nullable();
            $table->integer('M_Tanka_GIB')->nullable();
            $table->integer('ZairyoHi_SUS')->nullable();
            $table->integer('Z_Tanka_SUS')->nullable();
            $table->integer('R_Tanka_SUS')->nullable();
            $table->integer('G_Tanka_SUS')->nullable();
            $table->integer('M_Tanka_SUS')->nullable();

            $flag = "ZairyoLossRitsu_MT_Flg,GenbaKeihiRitsu_MT_Flg,RiekiRitsu_MT_Flg,MitsumoriKakeRitsu_MT_Flg,Z_Tanka_IPN_MT_Flg,R_Tanka_IPN_MT_Flg,G_Tanka_IPN_MT_Flg,M_Tanka_IPN_MT_Flg,Z_Tanka_JIS_MT_Flg,R_Tanka_JIS_MT_Flg,G_Tanka_JIS_MT_Flg,M_Tanka_JIS_MT_Flg,Z_Tanka_GIB_MT_Flg,R_Tanka_GIB_MT_Flg,G_Tanka_GIB_MT_Flg,M_Tanka_GIB_MT_Flg,Z_Tanka_SUS_MT_Flg,R_Tanka_SUS_MT_Flg,G_Tanka_SUS_MT_Flg,M_Tanka_SUS_MT_Flg,Zairyo_IPN_Check_Flg,Zairyo_JIS_Check_Flg,Zairyo_GIB_Check_Flg,Zairyo_SUS_Check_Flg";
            $flag = explode(",", $flag);
            foreach ($flag as $f) {
                $table->boolean($f)->nullable();
            }

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
