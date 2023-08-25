<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 't_categories';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists($this->table);
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('Category_ID')->index();
            $table->string('Category_Nm')->nullable();
            $table->string('Parent_ID')->nullable()->default(0);
            $table->integer('AdQuoNo')->nullable()->index();
            $table->integer('Sort_No')->nullable()->default(0);
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
