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

Route::get('/', 'ProductsController@index');
Route::get('/products', 'ProductsController@index');
Route::get('/products/vote', 'ProductsController@vote');
Route::get('/carts', 'CartsController@index');
Route::get('/carts/update', 'CartsController@update');
Route::get('/carts/destroy', 'CartsController@destroy');
Route::get('/carts/pay', 'CartsController@pay');
Route::get('/carts/billing', 'CartsController@billing');
