<?php

///Route::group(['middleware' => 'auth', 'prefix' => 'adm'], function()
//{
    Route::get('/image/test',    ['as' => 'clean_image',   'uses' => 'Interpro\ImageAggr\Http\ImageOperationController@testpage']);
    Route::get('/image/clean',   ['as' => 'clean_image',   'uses' => 'Interpro\ImageAggr\Http\ImageOperationController@clean']);
    Route::get('/image/phclean', ['as' => 'phclean_image', 'uses' => 'Interpro\ImageAggr\Http\ImageOperationController@cleanToPh']);
    Route::get('/image/refresh', ['as' => 'refresh_image', 'uses' => 'Interpro\ImageAggr\Http\ImageOperationController@refresh']);
    Route::get('/image/crop',    ['as' => 'crop_image',    'uses' => 'Interpro\ImageAggr\Http\ImageOperationController@crop']);
    Route::post('/image/upload',  ['as' => 'upload_image',  'uses' => 'Interpro\ImageAggr\Http\ImageOperationController@upload']);
//});
