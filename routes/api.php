<?php

use Illuminate\Support\Facades\Route;

Route::post('security-cloud/get', [\App\Http\Controllers\SecurityCloudController::class, 'GetData'])->name('security.cloud.get');
Route::post('security-cloud/crypt', [\App\Http\Controllers\SecurityCloudController::class, 'Crypt'])->name('security.cloud.crypt');
