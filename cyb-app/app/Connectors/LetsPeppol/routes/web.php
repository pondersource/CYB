<?php

use App\Connectors\LetsPeppol\LetsPeppolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

Route::get('/', function() {
    return View::file(__DIR__.'/../resources/views/index.blade.php');
});

Route::name('admin-panel')->get('/admin', function() {
    $service = new LetsPeppolService();
    $identities = $service->getIdentities();
    return View::file(__DIR__.'/../resources/views/admin.blade.php', compact('identities'));
});