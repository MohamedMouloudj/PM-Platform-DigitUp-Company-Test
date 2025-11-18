<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/about', function () {
    return json_decode('{"message": "This is the about page."}');
});
Route::prefix('api')->group(function () {
    Route::get('/status', function () {
        return json_decode('{"status": "API is running."}');
    });
});
