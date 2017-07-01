<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
Route::get('file/output/{id}', 'FileController@getOutput')->name('file-output');
Route::get('file/download/{id}', 'FileController@getDownload')->name('file-download');
Route::get('file/related/{related_table}/{related_id}', 'FileController@getRelated')->name('file-related');
Route::get('file/related/{related_table}/{related_id}/first', 'FileController@getRelatedOutput')->name('file-related-first');


//Route::group(['middleware' => ['auth.admin']], function () {
    //檔案上傳
    Route::post('file/upload', 'FileController@upload')->name('smart-file-upload');
    Route::delete('file/delete', 'FileController@destroy')->name('smart-file-delete');
//});