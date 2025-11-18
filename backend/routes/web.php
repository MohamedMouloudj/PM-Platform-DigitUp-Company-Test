<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::get('/status', function () {
        return json_decode('{"status": "API is running."}');
    });
});
