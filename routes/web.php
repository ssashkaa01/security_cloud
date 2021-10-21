<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\SecurityCloudController::class, 'Main']);
