<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// データ追加
Route::get("/readAll", "DataController@readAll")->name("readAll");
Route::get("/readCsvMitsumori", "DataController@readCsvMitsumori")->name("readCsvMitsumori");
Route::get("/readCsvShiyo", "DataController@readCsvShiyo")->name("readCsvShiyo");
Route::get("/readCsvZairyo", "DataController@readCsvZairyo")->name("readCsvZairyo");
Route::get("/readCsvTani", "DataController@readCsvTani")->name("readCsvTani");

Route::get("/2", "MitsumoriController@index2")->name("home");
Route::get("/", "MitsumoriController@index")->name("home");
Route::get("/mitsumore-action", "MitsumoriController@action")->name("mitsumore.action");
Route::post("/mitsumore-action", "MitsumoriController@action");

Route::get("/getListShiyo", "ShiyoController@getListShiyo")->name("getListShiyo");
Route::get('/setMitsumoreShiyo', "MitsumoriController@setMitsumoreShiyo")->name("setMitsumoreShiyo");
Route::get('/getMitsumoreMeisai', "MitsumoriController@getMitsumoreMeisai")->name("getMitsumoreMeisai");
Route::get('/mitsumoreStore', "MitsumoriController@store")->name("m.store");


Route::get("/getListZairyo", "ZairyoController@getListZairyo")->name("getListZairyo");
Route::get("/getListZairyoSelected", "ZairyoController@getListZairyoSelected")->name("getListZairyoSelected");
Route::get('/zairyoStore', "ZairyoController@store")->name("z.store");
Route::get('/checkPort', "MitsumoriController@checkPort")->name("checkPort");
