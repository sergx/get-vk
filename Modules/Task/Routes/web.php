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


Route::prefix('project/{project_id}/task')->group(function() {
    $prefix = 'task';
    $controllerName = 'TaskController';

    //Route::get(     '/create',              $controllerName.'@create')     ->name($prefix.'.create');
    Route::delete(  '/{task_id}',           $controllerName.'@destroy')    ->name($prefix.'.destroy');

    Route::prefix('groups-search')->group(function() {
        $prefix = 'task.groups-search';
        $controllerName = 'TaskGroupsSearchController';

        Route::get(     '/',                         $controllerName.'@index')      ->name($prefix.'.index');
        Route::get(     '/create',                   $controllerName.'@create')     ->name($prefix.'.create');
        Route::post(    '/',                         $controllerName.'@store')      ->name($prefix.'.store');
        Route::get(     '/{task_id}',                $controllerName.'@show')       ->name($prefix.'.show');
    });

    Route::prefix('users-from-group')->group(function() {
        $prefix = 'task.users-from-group';
        $controllerName = 'TaskUsersFromGroupController';

        Route::get(     '/',                         $controllerName.'@index')          ->name($prefix.'.index');
        Route::get(     '/create',                   $controllerName.'@create')         ->name($prefix.'.create');
        Route::post(    '/',                         $controllerName.'@store')          ->name($prefix.'.store');
        Route::get(     '/{task_id}',                $controllerName.'@show')           ->name($prefix.'.show');
        Route::get('/{parse_users_task_id}/prossess',$controllerName.'@taskProssess')   ->name($prefix.'.prossess');
        Route::get(     '/global_prossess',          $controllerName.'@globalProssess') ->name($prefix.'.global_prossess');
    });
});
