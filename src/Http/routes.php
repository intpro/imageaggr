<?php

Route::group(['middleware' => ['web', 'auth'], 'prefix' => 'adm'], function()
{
    Route::get('/image/test',     ['as' => 'clean_image',   'uses' => 'Interpro\ImageAggr\Http\ImageOperationController@testpage']);
    Route::post('/image/clean',   ['as' => 'clean_image',   'uses' => 'Interpro\ImageAggr\Http\ImageOperationController@clean']);
    Route::post('/image/phclean', ['as' => 'phclean_image', 'uses' => 'Interpro\ImageAggr\Http\ImageOperationController@cleanToPh']);
    Route::post('/image/refresh', ['as' => 'refresh_image', 'uses' => 'Interpro\ImageAggr\Http\ImageOperationController@refresh']);
    Route::post('/image/crop',    ['as' => 'crop_image',    'uses' => 'Interpro\ImageAggr\Http\ImageOperationController@crop']);
    Route::post('/image/upload',  ['as' => 'upload_image',  'uses' => 'Interpro\ImageAggr\Http\ImageOperationController@upload']);
});
