<?php

use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;

Route::prefix('notes')->group(function () {
    Route::get('/', [NoteController::class, 'index']);
    Route::post('/', [NoteController::class, 'store']);
    Route::get('/search', [NoteController::class, 'search']);
    Route::get('/{id}', [NoteController::class, 'show']);
    Route::put('/{id}', [NoteController::class, 'update']);
    Route::delete('/{id}', [NoteController::class, 'destroy']);
    Route::post('/{id}/summary', [NoteController::class, 'summary']);
});
