<?php

use Illuminate\Support\Facades\Route;
use App\Core\ApplicationManager;
use App\Core\DataType\DataTypeManager;
use Illuminate\Http\Request;

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

    $view_authentications = [];

    foreach ($authentications as $auth) {
        $view_auth = [];
        $view_auth['id'] = $auth->id;
        $view_auth['display_name'] = $auth->display_name;
        $view_auth['app_code_name'] = $auth->app_code_name;


        $app = ApplicationManager::getApplication($auth->app_code_name);
        $data_types = $app->getSupportedDataTypes($auth);

        $view_data_types = [];

        foreach ($data_types as $data_type) {
            $data_type_details = DataTypeManager::getDataTypeForName($data_type);
            $function = ApplicationManager::getFunction($auth->id, $data_type);

            $view_data_type = [];
            $view_data_type['name'] = $data_type_details->getCodeName();
            $view_data_type['display_name'] = $data_type_details->getDisplayName();
            $view_data_type['read'] = $function->read;
            $view_data_type['write'] = $function->write;
            $view_data_types[] = $view_data_type;
        }

        $view_auth['data_types'] = $view_data_types;
        $view_authentications[] = $view_auth;
    }

    return view('welcome', compact('applications', 'view_authentications'));
});

Route::post('/apps/{app_code_name}/auth', function(Request $request, $app_code_name) {
    return ApplicationManager::finalizeAuthentication($request, $app_code_name);
});

Route::post('/hookOn/{auth_id}/{data_type}', function($auth_id, $data_type) {
    return ApplicationManager::hookOn($auth_id, $data_type);
});