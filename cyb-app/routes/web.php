<?php

use Illuminate\Support\Facades\Route;
use App\Core\ApplicationManager;

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
    $applications = ApplicationManager::getApplications();
    $authentications = ApplicationManager::getAuthentications();
    return view('welcome', compact('applications', 'authentications'));
});

Route::post('/apps/{app_code_name}/auth', function($app_code_name) {
    return ApplicationManager::finalizeAuthentication($app_code_name);
});

Route::post('/hookOn/{auth_id}/{data_type}', function($auth_id, $data_type) {
    return ApplicationManager::hookOn($auth_id, $data_type);
});