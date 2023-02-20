<?php

use App\Connectors\LetsPeppol\AS4Direct\AS4DirectService;
use App\Connectors\LetsPeppol\Models\Identity;
use App\Connectors\LetsPeppol\KeyStore;
use App\Connectors\LetsPeppol\LetsPeppolConnector;
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

Route::name('as4-direct.')->prefix('as4direct')->group(function() {
    Route::name('info')->get('/info', function (Request $request) {
        $service = new AS4DirectService();
        return $service->getInfo();
    });
    
    Route::name('endpoint')->post('/endpoint', function (Request $request) {
        $service = new AS4DirectService();
        return $service->endpointMessage($request);
    }); 
});

Route::name('register')->post('/identity', function (Request $request) {
    $service = new LetsPeppolService();

    $identity = $service->createIdentity($request->toArray());

    if (!empty(($identity))) {
        $auth_info = new AuthInfo();
        $auth_info
            ->setAppCodeName(LetsPeppolConnector::CODE_NAME)
            ->setDisplayName($request['name'])
            ->setAppUserId($identity['id'])
            ->setMetadata($identity['id']);

        ApplicationManager::createAuthentication($auth_info);

        $token = Auth::user()->createToken('default');

        return [
            'identity' => $identity,
            'api_token' => explode('|', $token->plainTextToken)[1]
        ];
    }
    else {
        return response()->json([
            'result' => 'failure',
            'reason' => 'Failed to store identity in the database'
        ], 507);
    }
});

Route::name('get-identity')->get('/identity/{identity_id}', function (Request $request, $identity_id) {
    $service = new LetsPeppolService();

    $identity = $service->getIdentity($identity_id);

    if (!empty(($identity))) {
        if (Auth::check()) {
            $authentications = ApplicationManager::getAuthentications();
            $authentications = array_filter($authentications, function ($auth) use ($identity_id) {
                return $auth['metadata'] === $identity_id;
            });

            if (empty($authentications)) {
                return response()->json([
                    'result' => 'failure',
                    'reason' => 'Not allowed for this user'
                ], 403);
            }
        }
        else {
            $signature = $request->query('signature');

            if (!isset($signature)) {
                return response()->json([
                    'result' => 'failure',
                    'reason' => 'Authentication or signature is required'
                ], 401);
            }

            $signature = base64_decode($signature);
            $expected_signed_message = 'I want identity id '.$identity_id;
            
            if (!KeyStore::verify($identity['as4direct_public_key'], $expected_signed_message, $signature)) {
                return response()->json([
                    'result' => 'failure',
                    'reason' => 'Could not verify the signature'
                ], 401);
            }
        }
        
        return $identity;
    }
    else {
        return response()->json([
            'result' => 'failure',
            'reason' => 'No identity exists'
        ], 404);
    }
});

Route::name('update-identity')->put('/identity/{identity_id}', function (Request $request, $identity_id) {
    $service = new LetsPeppolService();

    $identity = $service->getIdentity($identity_id);

    if (empty($identity)) {
        return response()->json([
            'result' => 'failure',
            'reason' => 'No identity exists'
        ], 404);
    }

    if (Auth::check()) {
        $authentications = ApplicationManager::getAuthentications();
        $authentications = array_filter($authentications, function ($auth) use ($identity_id) {
            return $auth['metadata'] === $identity_id;
        });

        if (empty($authentications)) {
            return response()->json([
                'result' => 'failure',
                'reason' => 'Not allowed for this user'
            ], 403);
        }
    }
    else {
        $signature = $request->query('signature');

        if (!isset($signature)) {
            return response()->json([
                'result' => 'failure',
                'reason' => 'Authentication or signature is required'
            ], 401);
        }

        $signature = base64_decode($signature);
        $expected_signed_message = $request->getContent();
        
        if (!KeyStore::verify($identity['as4direct_public_key'], $expected_signed_message, $signature)) {
            return response()->json([
                'result' => 'failure',
                'reason' => 'Could not verify the signature'
            ], 401);
        }
    }

    $only_as4direct = false;

    if ($identity['kyc_status'] === Identity::KYC_STATUS_APPROVED) {
        $only_as4direct = true;
    }

    foreach ($request->toArray() as $key => $value) {
        if (array_key_exists($key, ['kyc_status', 'identifier_scheme', 'identifier_value', 'registrar', 'reference'])) {
            return response()->json([
                'result' => 'failure',
                'reason' => 'Only admin can change identity protected fields'
            ], 403);
        }

        if ($only_as4direct) {
            if ($key === 'as4direct_endpoint') {
                $identity[$key] = $value;    
            }
            else {
                return response()->json([
                    'result' => 'failure',
                    'reason' => 'Only AS4 direct endpoint can be modified after KYC approval'
                ], 403);
            }
        }
        else {
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
        return response()->json([
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
        return response()->json([
            'result' => 'failure',
            'reason' => 'No identity exists'
        ], 404);
    }

    foreach ($request->toArray() as $key => $value) {
        $identity[$key] = $value;
    }

    $success = $service->updateIdentity($identity);

    if ($success) {
        return [
            'result' => 'OK',
            'data' => $identity
        ];
    }
    else {
        return response()->json([
            'result' => 'failure',
            'reason' => 'General failure'
        ], 500);
    }
});

Route::name('send-message')->middleware('auth:sanctum')->post('/message/{identity_id}', function (Request $request, $identity_id) {
    $ubl = $request->getContent();
    
    $service = new LetsPeppolService();

    $authentications = ApplicationManager::getAuthentications();
    $authentications = array_filter($authentications, function ($auth) use ($identity_id) {
        return $auth['metadata'] === $identity_id;
    });

    if (empty($authentications)) {
        return response()->json([
            'result' => 'failure',
            'reason' => 'Identity does not exist or not allowed for this user'
        ], 403);
    }

    if ($service->sendMessage($identity_id, $ubl)) {
        return [ 'result' => 'OK' ];
    }
    else {
        return response()->json([
            'result' => 'failure',
            'reason' => 'General failure'
        ], 500);
    }
});