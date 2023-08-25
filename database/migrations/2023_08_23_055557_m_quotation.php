<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 'm_quotations';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists($this->table);
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $string = "Quo_Nm,Quo_Cd,Customer_Nm,Customer_Cd,Coop_Cd,User_Nm,User_Cd,Building_Nm,Client,Quo_Type,Quo_Term,Delivery_Term,Settlement_Term,Construct_Range,Supplies,Expiry_Date,Address,Phone,Fax,Director_Nm,Manager_Nm,Quo_No,Construct_Nm,Tax,Payment_A,Payment_B,Payment_C,Payment_D,Payment_E,Create_User,Note,Information,Company_Nm,Other_1,Other_2,Other_3,Other_4,Other_5,Other_6,Other_7,Other_8,Logo,Basic_Master,Construct_Register_No,Construct_Register_Date,Company_File";
            $string = explode(",", $string);
            foreach ($string as $f) {
                $table->string($f)->nullable();
            }
            $date = "Quo_Date,Change_Date,Construct_Start,Construct_End";
            $date = explode(",", $date);
            foreach ($date as $f) {
                $table->date($f)->nullable();
            }
            $integer = "Change_Num,Building_Per,Quo_Per,Amount,Discount,Metal,Board,GL,Adiabatic_Sound,Fireproof,Mounting_Floor,Mounting_Cloth,Other,Metal_Labor_Cost,Board_Labor_Cost,GL_Labor_Cost,Absorption_Labor_Cost,Fire_Base_Labor_Cost,Fire_Board_Labor_Cost,Floor_Labor_Cost,Cloth_Labor_Cost,Other_Labor_Cost,Metal_Unit_Price,Board_Unit_Price,GL_Unit_Price,Absorption_Unit_Price,Fire_Base_Unit_Price,Fire_Board_Unit_Price,Floor_Unit_Price,Cloth_Unit_Price,Other_Unit_Price";
            $integer = explode(",", $integer);
            foreach ($integer as $f) {
                $table->integer($f)->nullable()->default(0);
            }
            
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
