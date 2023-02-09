<?php

use App\Core\ApplicationManager;
use App\Core\DataType\DataTypeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
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
Route::get('/', function (Request $request) {
    $can_generate_token = false;

    if (Auth::check()) {
        $can_generate_token = true;
    }

    $connectors = ApplicationManager::getConnectors();
    $authentications = ApplicationManager::getAuthentications();

    $view_authentications = [];

    foreach ($authentications as $auth) {
        $view_auth = $auth->getAttributes();

        $connector = ApplicationManager::getConnector($auth->app_code_name);

        $view_auth['app_name'] = $connector->getName();
        $view_auth['ui'] = $connector->getAuthenticatedUI($auth);

        $data_types = $connector->getSupportedDataTypes($auth);

        $view_data_types = [];

        foreach ($data_types as $data_type) {
            $data_type_details = DataTypeManager::getDataTypeForName($data_type);
            $function = ApplicationManager::getFunction($auth['id'], $data_type);

            $view_data_type = [];
            $view_data_type['name'] = $data_type_details->getCodeName();
            $view_data_type['display_name'] = $data_type_details->getDisplayName();
            $view_data_type['read'] = $function['read'];
            $view_data_type['write'] = $function['write'];
            $view_data_types[] = $view_data_type;
        }

        $view_auth['data_types'] = $view_data_types;
        $view_authentications[] = $view_auth;
    }

    return view('welcome', compact('connectors', 'view_authentications', 'can_generate_token'));
});

Route::post('/apps/{app_code_name}/auth', function (Request $request, $app_code_name) {
    return ApplicationManager::finalizeAuthentication($request, $app_code_name);
});

Route::post('/readOn/{auth_id}/{data_type}', function ($auth_id, $data_type) {
    return ApplicationManager::readOn($auth_id, $data_type);
});

Route::post('/readOff/{auth_id}/{data_type}', function ($auth_id, $data_type) {
    return ApplicationManager::readOff($auth_id, $data_type);
});

Route::post('/writeOn/{auth_id}/{data_type}', function ($auth_id, $data_type) {
    return ApplicationManager::writeOn($auth_id, $data_type);
});

Route::post('/writeOff/{auth_id}/{data_type}', function ($auth_id, $data_type) {
    return ApplicationManager::writeOff($auth_id, $data_type);
});

Route::middleware('auth')->post('/generateToken', function (Request $request) {
    $token = $request->user()->createToken('default');

    return [
        'result' => 'ok',
        'token' => explode('|', $token->plainTextToken)[1]
    ];
});

Route::get('/test/tasks', function (Request $request) {
    $success = explode(',', $request->query('success'));
    return ApplicationManager::test($success);
});

Route::name('connector.')->prefix('connector')->group(function() {
    $connectors = ApplicationManager::getConnectors();

    foreach ($connectors as $connector) {
        $app_code = $connector->getAppCodeName();
        $app_folder = Str::studly($app_code);

        $route_file = base_path("app/Connectors/$app_folder/routes/web.php");

        if (File::exists($route_file)) {
            Route::name("$app_code.")->prefix($app_code)->group($route_file);
        }
    }
});