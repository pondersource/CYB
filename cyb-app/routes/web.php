<?php

use Illuminate\Support\Facades\Route;
use App\Core\Applications;

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
Route::get('/', function () {
    $applications = Applications::getApplications();
    $authentications = Applications::getAuthentications();
    return view('welcome', compact('applications', 'authentications'));
});

Route::post('/apps/{app_code_name}/auth', function($app_code_name) {
    return Applications::finalizeAuthentication($app_code_name);
});

Route::post('/hookOn/{auth_id}/{data_type}', function($auth_id, $data_type) {
    return Applications::hookOn($auth_id, $data_type);
});