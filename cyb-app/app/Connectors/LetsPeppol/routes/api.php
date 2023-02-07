<?php

use App\Connectors\LetsPeppol\LetsPeppolService;
use App\Core\ApplicationManager;
use App\Core\AuthInfo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::name('acube-incoming')->post('/acube/incoming', function (Request $request) {
    try {
        $service = new LetsPeppolService();
        $service->newMessage(LetsPeppolService::REGISTRAR_ACUBE, true, $request->toArray());

        return ['message' => 'Thank you for sharing!'];
    } catch (\Exception $e) {
        abort(503); // So that ACube would retry later!
    }
});

Route::name('acube-outgoing')->post('/acube/outgoing', function (Request $request) {
    try {
        $service = new LetsPeppolService();
        $service->newMessage(LetsPeppolService::REGISTRAR_ACUBE, false, $request->toArray());

        return ['message' => 'Thank you for sharing!'];
    } catch (\Exception $e) {
        abort(503); // So that ACube would retry later!
    }
});

Route::middleware('auth:sanctum')->post('/identity', function (Request $request) {
    $user = $request->user();
    $service = new LetsPeppolService();

    if (!empty($service->getIdentity($user['id']))) {
        return Response::json([
            'result' => 'failure',
            'reason' => 'An identity is already defined'
        ], 409);
    }

    $identity = $service->createIdentity($user['id'], $request->toArray());

    if (!empty(($identity))) {
        $auth_info = new AuthInfo();
        $auth_info
            ->setAppCodeName(self::CODE_NAME)
            ->setDisplayName($payload['name'])
            ->setAppUserId($identity['id'])
            ->setMetadata($identity['id']);

        ApplicationManager::createAuthentication($auth_info);

        return [
            'result' => 'OK',
            'data' => $identity
        ];
    }
    else {
        return Response::json([
            'result' => 'failure',
            'reason' => 'Failed to store identity in the database'
        ], 507);
    }
});

Route::middleware('auth:sanctum')->get('/identity', function (Request $request) {
    $user = $request->user();
    $service = new LetsPeppolService();

    $identity = $service->getIdentity($user['id']);

    if (!empty(($identity))) {
        return [
            'result' => 'OK',
            'data' => $identity
        ];
    }
    else {
        return Response::json([
            'result' => 'failure',
            'reason' => 'No identity exists'
        ], 404);
    }
});

// TODO Will have to guard against checking the validation status. And make another end point for admin role.
Route::middleware('auth:sanctum')->put('/identity', function (Request $request) {
    $user = $request->user();
    $service = new LetsPeppolService();

    $identity = $service->getIdentity($user['id']);

    if (empty($identity)) {
        return Response::json([
            'result' => 'failure',
            'reason' => 'No identity exists'
        ], 404);
    }

    foreach ($request->toArray() as $key => $value) {
        if (array_key_exists($key, $identity)) {
            $identity[$key] = $value;
        }
    }

    $success = $service->updateIdentity($identity);

    if ($success) {
        return [
            'result' => 'OK',
            'data' => $identity
        ];
    }
    else {
        return Response::json([
            'result' => 'failure',
            'reason' => 'General failure'
        ], 500);
    }
});

Route::middleware('auth:sanctum')->post('/message', function (Request $request) {
    $ubl = $request->getContent();

    $user = $request->user();
    $service = new LetsPeppolService();

    if ($service->sendMessage($user['id'], $ubl)) {
        return [ 'result' => 'OK' ];
    }
    else {
        return Response::json([
            'result' => 'failure',
            'reason' => 'General failure'
        ], 500);
    }
});