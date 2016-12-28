<?php

Route::group(['middleware' => 'auth', 'prefix' => 'adm'], function()
{
    Route::get('/image/clean',   ['as' => 'clean_image',   'uses' => 'Interpro\ImageAgrTypes\Laravel\Http\ImageOperationController@clean']);
    Route::get('/image/phclean', ['as' => 'phclean_image', 'uses' => 'Interpro\ImageAgrTypes\Laravel\Http\ImageOperationController@cleanToPh']);
    Route::get('/image/refresh', ['as' => 'refresh_image', 'uses' => 'Interpro\ImageAgrTypes\Laravel\Http\ImageOperationController@refresh']);
    Route::get('/image/crop',    ['as' => 'crop_image',    'uses' => 'Interpro\ImageAgrTypes\Laravel\Http\ImageOperationController@crop']);
    Route::get('/image/upload',  ['as' => 'upload_image',  'uses' => 'Interpro\ImageAgrTypes\Laravel\Http\ImageOperationController@upload']);
});
