<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

Route::get('/', function() {
    return View::file(__DIR__.'/../resources/views/welcome.blade.php');
});