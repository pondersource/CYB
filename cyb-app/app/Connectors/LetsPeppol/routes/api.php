<?php

use App\Connectors\LetsPeppol\LetsPeppolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::name('acube-incoming')->post('/acube/incoming', function (Request $request) {
    $service = new LetsPeppolService();
    $service->newMessage(LetsPeppolService::REGISTRAR_ACUBE, true, $request->all());

    return ['message' => 'Thank you for sharing!'];
});

Route::name('acube-outgoing')->post('/acube/outgoing', function (Request $request) {
    $service = new LetsPeppolService();
    $service->newMessage(LetsPeppolService::REGISTRAR_ACUBE, false, $request->all());

    return ['message' => 'Thank you for sharing!'];
});