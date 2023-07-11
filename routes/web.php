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

Route::get("/", "InvoiceController@index")->name("home");
Route::get("/2", "InvoiceController@index2")->name("javascript");
Route::get("/invoice-action", "InvoiceController@action")->name("invoice.action");
Route::post("/invoice-action", "InvoiceController@action");
Route::get("/getListShiyo", "InvoiceController@getListShiyo")->name("getListShiyo");
Route::get('/$metadata', "InvoiceController@metadata")->name("metadata");



// データ追加
Route::get("/readAll", "DataController@readAll")->name("readAll");
Route::get("/readCsvInvoice", "DataController@readCsvInvoice")->name("readCsvInvoice");
Route::get("/readCsvShiyo", "DataController@readCsvShiyo")->name("readCsvShiyo");
Route::get("/readCsvZairyo", "DataController@readCsvZairyo")->name("readCsvZairyo");
Route::get("/readCsvTani", "DataController@readCsvTani")->name("readCsvTani");
