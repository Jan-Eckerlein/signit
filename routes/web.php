<?php

use Illuminate\Support\Facades\Route;

Route::get('/editor/{vue_capture?}', function () {
    return view('welcome');
})->where('vue_capture', '[\/\w\.-]*');