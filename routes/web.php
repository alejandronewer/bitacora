<?php

use Illuminate\Support\Facades\Route;

// Rutas de autenticación backend (login/logout/password).
require __DIR__.'/auth.php';

// Shell SPA en raíz.
Route::view('/', 'spa')->name('spa');
Route::view('/dashboard', 'spa')->middleware('auth')->name('dashboard');

// Fallback SPA para navegación Vue Router en raíz.
Route::view('/{any}', 'spa')
    ->where('any', '^(?!api|sanctum|storage).*$');
