<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BahanPokokController;
use App\Http\Controllers\Api\HargaBapokController;
use App\Http\Controllers\Api\PasarController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 🔓 Rute publik (tidak perlu login)
Route::post('/login', [AuthController::class, 'login']);
Route::get('/bahan-pokok', [BahanPokokController::class, 'index']);
Route::get('/bahan-pokok/{bahan_pokok}', [BahanPokokController::class, 'show']);
Route::get('/pasar', action: [PasarController::class, 'index']);
Route::get('/harga-bapok',action:[HargaBapokController::class,'index']);

// 🔐 Rute hanya untuk pengguna yang sudah login (semua role)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [UserController::class, 'index']);
    Route::get('/user/{id}', [UserController::class, 'show']);
    Route::put('/user/{id}', [UserController::class, 'update']);
    Route::delete('/user/{id}', [UserController::class, 'destroy']);
    Route::post('/register', [AuthController::class, 'create']);
});


// routes/api.php
Route::middleware('auth:sanctum')->get('/petugas-pasar', [UserController::class, 'index']);

// 🛡️ Rute CRUD hanya untuk admin & crew
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/bahan-pokok', [BahanPokokController::class, 'store']);        // CREATE
    Route::put('/bahan-pokok/{bahan_pokok}', [BahanPokokController::class, 'update']); // UPDATE
    Route::patch('/bahan-pokok/{bahan_pokok}', [BahanPokokController::class, 'update']); // UPDATE
    Route::delete('/bahan-pokok/{bahan_pokok}', [BahanPokokController::class, 'destroy']); // DELETE


    Route::post('/harga-bapok', [HargaBapokController::class, 'store']);        // CREATE
    Route::put('/harga-bapok/{harga_bapok}', [HargaBapokController::class, 'update']); // UPDATE
    Route::patch('/harga-bapok/{harga_bapok}', [HargaBapokController::class, 'update']); // UPDATE
    Route::delete('/harga-bapok/{harga_bapok}', [HargaBapokController::class, 'destroy']); // DELETE

    Route::post('/pasar', [PasarController::class, 'store']);        // CREATE
    Route::put('/pasar/{pasar}', [PasarController::class, 'update']); // UPDATE
    Route::patch('/pasar/{pasar}', [PasarController::class, 'update']); // UPDATE
    Route::delete('/pasar/{pasar}', [PasarController::class, 'destroy']); // DELETE
});