<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 'm_tanis';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable($this->table))
            Schema::drop($this->table);
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->integer('Tani_ID')->nullable()->index();
            $table->string('Tani_Nm')->nullable();
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
