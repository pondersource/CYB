<?php

use App\Core\ApplicationManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::name('connector.')->prefix('connector')->group(function() {
    $connectors = ApplicationManager::getConnectors();

    foreach ($connectors as $connector) {
        $app_code = $connector->getAppCodeName();
        $app_folder = Str::studly($app_code);
        $route_file = base_path("app/Connectors/$app_folder/routes/api.php");

        if (File::exists($route_file)) {
            Route::name("$app_code.")->prefix($app_code)->group($route_file);
        }
    }
});