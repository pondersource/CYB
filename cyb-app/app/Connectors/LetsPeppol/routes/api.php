<?php

use App\Connectors\LetsPeppol\LetsPeppolService;
use Illuminate\Http\Request;
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