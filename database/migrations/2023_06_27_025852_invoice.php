<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 'invoices';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable($this->table))
            Schema::drop($this->table);
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->integer('AdQuoNo')->index();
            $table->integer('DetailType');
            $table->integer('DetailNo')->nullable();
            $table->string('Type', 100)->nullable();
            $table->string('PartName', 100)->nullable();
            $table->string('MaterialName', 100)->nullable();
            $table->string('SpecName1', 100)->nullable();
            $table->string('SpecName2', 100)->nullable();
            $table->integer('No')->default(0)->nullable();
            $table->string('FirstName')->nullable();
            $table->string('StandDimen')->nullable();
            $table->string('MakerName')->nullable();
            $table->string('MakerNameOrg')->nullable();
            $table->integer('Unit_ID')->nullable();
            $table->string('Unit', 3)->nullable();
            $table->integer('UnitOrg_ID')->nullable();
            $table->string('UnitOrg', 3)->nullable();
            $table->decimal('Quantity', $precision = 8, $scale = 1)->nullable(); //数量
            $table->integer('UnitPrice')->nullable();
            $table->integer('UnitPriceOrg')->nullable();
            $table->float('Amount',15,2)->nullable();
            $table->text('Note')->nullable();
            $table->string('LaborOuts')->nullable();
            $table->integer('MaterUnitPrice')->nullable();
            $table->integer('LaborUnitPrice')->nullable();
            $table->integer('OutsUnitPrice')->nullable();
            $table->integer('MaterCostTotal')->nullable();
            $table->integer('LaborCostTotal')->nullable();
            $table->integer('OutsCostTotal')->nullable();
            $table->integer('MaterUnitPriceOrg')->nullable();
            $table->integer('LaborUnitPriceOrg')->nullable();
            $table->integer('OutsUnitPriceOrg')->nullable();
            $table->boolean('MaterUPChangeFlag')->default(0)->nullable();
            $table->boolean('LaborUPChangeFlag')->default(0)->nullable();
            $table->boolean('OutsUPChangeFlag')->default(0)->nullable();
            $table->boolean('FirstNameChangeFlag')->default(0)->nullable();
            $table->boolean('StandChangeFlag')->default(0)->nullable();
            $table->boolean('UnitPriceChangeFlag')->default(0)->nullable();
            $table->boolean('AmountChangeFlag')->default(0)->nullable();
            $table->boolean('MakerChangeFlag')->default(0)->nullable();
            $table->boolean('UnitChangeFlag')->default(0)->nullable();
            $table->string('SpecCode1', 20)->nullable();
            $table->string('SpecCode2', 20)->nullable();
            $table->boolean('ReadFlag')->default(0)->nullable();
            $table->integer('M_EstUP1')->nullable();
            $table->integer('M_MaterUP1')->nullable();
            $table->integer('M_LaborUP1')->nullable();
            $table->integer('M_OutsUP1')->nullable();
            $table->integer('M_EstUP2')->nullable();
            $table->integer('M_MaterUP2')->nullable();
            $table->integer('M_LaborUP2')->nullable();
            $table->integer('M_OutsUP2')->nullable();
            $table->string('MaterScaleFactor')->nullable();
            $table->integer('LaborAmountTotal')->nullable();
            $table->integer('OutsAmountTotal')->nullable();
            $table->integer('OpenUPOrg')->nullable();
            $table->integer('MaterOpenUPOrg')->nullable();
            $table->integer('LaborOpenUPOrg')->nullable();
            $table->integer('OutsOpenUPOrg')->nullable();
            $table->integer('OutsCost')->nullable();
            $table->integer('MaterCost')->nullable();
            $table->integer('LiftingCost')->nullable();
            $table->integer('LaborCost')->nullable();
            $table->integer('SiteExpense')->nullable(); //材料増減係数
            $table->string('ReadDataKey')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('invoices');
    }
};
