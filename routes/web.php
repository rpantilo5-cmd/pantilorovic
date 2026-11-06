<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'project' => 'pantilorovic',
        'framework' => 'Laravel 13',
        'message' => 'Secure API is running.',
    ]);
});
