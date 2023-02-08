<?php

use App\Connectors\LetsPeppol\Models\Identity;
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

Route::name('register')->middleware('auth:sanctum')->post('/identity', function (Request $request) {
    $service = new LetsPeppolService();

    $identity = $service->createIdentity($request->toArray());

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

Route::name('get-identity')->middleware('auth:sanctum')->get('/identity/{identity_id}', function (Request $request, $identity_id) {
    $service = new LetsPeppolService();

    $identity = $service->getIdentity($identity_id);

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

Route::name('update-identity')->middleware('auth:sanctum')->put('/identity/{identity_id}', function (Request $request, $identity_id) {
    $service = new LetsPeppolService();

    $identity = $service->getIdentity($identity_id);

    if (empty($identity)) {
        return Response::json([
            'result' => 'failure',
            'reason' => 'No identity exists'
        ], 404);
    }

    if ($identity['kyc_status'] === Identity::KYC_STATUS_APPROVED) {
        return Response::json([
            'result' => 'failure',
            'reason' => 'Can not modify identity after it has been approved'
        ], 403);
    }

    foreach ($request->toArray() as $key => $value) {
        if (array_key_exists($key, ['kyc_status', 'identifier_scheme', 'identifier_value', 'registrar', 'reference'])) {
            return Response::json([
                'result' => 'failure',
                'reason' => 'Only admin can change identity protected fields'
            ], 403);
        }

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

// TODO Check for admin
Route::name('admin-update-identity')->put('/admin/identity', function (Request $request) {
    $service = new LetsPeppolService();

    $identity = $service->getIdentity($request['id']);

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

Route::name('send-message')->middleware('auth:sanctum')->post('/message/{identity_id}', function (Request $request, $identity_id) {
    $ubl = $request->getContent();
    
    $service = new LetsPeppolService();

    $authentications = ApplicationManager::getAuthentications();
    $authentications = array_filter($array_filter, function ($auth) {
        return $auth['id'] === $identity_id;
    });

    if (empty($authentications)) {
        return Response::json([
            'result' => 'failure',
            'reason' => 'Identity does not exist or not allowed for this user'
        ], 403);
    }

    if ($service->sendMessage($identity_id, $ubl)) {
        return [ 'result' => 'OK' ];
    }
    else {
        return Response::json([
            'result' => 'failure',
            'reason' => 'General failure'
        ], 500);
    }
});