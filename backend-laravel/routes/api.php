<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/files', 'ApiController@getFileList');
Route::get('/extensions', 'ApiController@getExtensions');
Route::post('/upload/{convert}', 'ApiController@uploadFiles');
Route::post('/convert/{id}/{protect}/{rastr}', 'ApiController@convertFile');
Route::get('/getfile/{id}', 'ApiController@getFileOriginal');
Route::get('/getpdf/{id}', 'ApiController@getFilePDF');
Route::post('/deletefile/{id}', 'ApiController@deleteFile');