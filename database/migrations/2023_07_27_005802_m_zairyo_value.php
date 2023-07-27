<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 'm_zairyo_values';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable($this->table))
            Schema::drop($this->table);
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->integer('Zairyo_Value_ID')->nullable();
            $table->integer('Zairyo_ID')->nullable()->index();
            $table->integer('Tanka_Kbn_ID')->nullable();
            $table->integer('Teika')->nullable();
            $table->integer('Tanka')->nullable();
            $table->integer('ZairyoTanka')->nullable();
            $table->integer('KakeRitsu')->nullable();
            $table->boolean('Unavailable_Flg')->nullable();
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
