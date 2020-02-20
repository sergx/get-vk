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

Route::prefix('vkapi')->group(function() {
    Route::get('/',                             'VkApiController@index')               ->name('vkapi.index');
    Route::post('/token-retrieve',              'VkApiController@tokenSave')           ->name('vkapi.token-save');
    Route::post('/request-handler/{method}',    'VkApiController@vkRequestHandler')    ->name('vkapi.request-handler');
});
