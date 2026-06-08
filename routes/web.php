<?php

use Illuminate\Support\Facades\Route;

Route::get('/pruebas', function () {
    return file_get_contents(public_path('pruebas/index.html'));
});

Route::get('/{any?}', function () {
    return file_get_contents(public_path('index.html'));
})->where('any', '^(?!api).*$');
