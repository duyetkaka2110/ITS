<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 'm_makers';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable($this->table))
            Schema::drop($this->table);
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->integer('Maker_ID')->nullable()->index();
            $table->string('Maker_Nm')->nullable();
            $table->string('Maker_Nm_Kn')->nullable();
            $table->string('Maker_Nm_Rk')->nullable();
            $table->boolean('Shitaji_Flg')->nullable();
            $table->boolean('Board_Flg')->nullable();
            $table->boolean('Cloth_Flg')->nullable();
            $table->boolean('Yuka_Flg')->nullable();
            $table->integer('Sort_No')->nullable();
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
