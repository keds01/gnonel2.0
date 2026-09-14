<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


// ANCIENNE ROUTE PAYGATEGLOBAL - CONSERVEE MAIS DESACTIVEE
// Route::post('/pg-payment-callback', 'AbonnementController@notify_url')->name('paygateglobal.notify');

// NOUVELLE ROUTE FEDAPAY WEBHOOK
Route::post('/fedapay-webhook', 'AbonnementController@fedapay_webhook')->name('fedapay.webhook')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
