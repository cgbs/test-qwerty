<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers;

Route::get('/', 'HomeController@index');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/home/convertdialog/{id}', 'HomeController@convertDialog');
Route::post('/files/upload', 'UFilesController@upload')->name('upload');
Route::delete('/files/delete/{id}', 'UFilesController@delete');
Route::get('/files/getoriginal/{id}', 'UFilesController@getOriginal');
Route::get('/files/getpdf/{id}', 'UFilesController@getPdf');
Route::post('/files/convert-exists/{id}', 'UFilesController@convertExists');