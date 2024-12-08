<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get(
    '/specification/schema',
    fn(Request $request) => file_get_contents(app_path('../specification/specification.yaml'))
)->name('specification.schema');

Route::view('/specification', 'swagger-ui')->name('specification');
