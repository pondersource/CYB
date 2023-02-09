<?php

use App\Core\ApplicationManager;
use App\Service\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
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

Route::post('/register', function (Request $request) {
    $user_service = new UserService();
    $user_service->register($request);
    return ['result' => 'ok'];
});

Route::post('/generateToken', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $token = $request->user()->createToken('default');
 
        return [
            'result' => 'ok',
            'token' => explode('|', $token->plainTextToken)[1]
        ];
    }

    return response()->json([
        'result' => 'failure',
        'reason' => 'Invalid credentials'
    ], 401);
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