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
Route::get('/pasar', [PasarController::class, 'index']);
Route::get('/harga-bapok', [HargaBapokController::class, 'index']);
Route::get('/bahan-pokoks', [HargaBapokController::class, 'summary']);
Route::get('/tabel-stok', [HargaBapokController::class, 'table']);
Route::get('/bahan-pokok/grafik/{id_bahan_pokok}', [HargaBapokController::class, 'getHargaBahanPokok']);
Route::get('/harga-bapok-acc',[HargaBapokController::class, 'getTableAcc']);

// 🔐 Rute hanya untuk pengguna yang sudah login (semua role)
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profil
    Route::get('/user/me', [UserController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/register', [AuthController::class, 'create']);

    // Manajemen User
    Route::get('/user/{id}', [UserController::class, 'show']);
    Route::put('/user/{id}', [UserController::class, 'update']);
    Route::delete('/user/{id}', [UserController::class, 'destroy']);

    // Petugas Pasar
    Route::get('/petugas-pasar', [UserController::class, 'index']);

    // 🔧 Bahan Pokok (CRUD)
    Route::post('/bahan-pokok', [BahanPokokController::class, 'store']);
    Route::put('/bahan-pokok/{id}', [BahanPokokController::class, 'update']);
    Route::patch('/bahan-pokok/{id}', [BahanPokokController::class, 'update']);
    Route::delete('/bahan-pokok/{id}', [BahanPokokController::class, 'destroy']);

    // 📊 Harga Bapok (CRUD + Table)
    Route::post('/harga-bapok', [HargaBapokController::class, 'store']);
    Route::put('/harga-bapok/{harga_bapok}', [HargaBapokController::class, 'update']);
    Route::patch('/harga-bapok/{harga_bapok}', [HargaBapokController::class, 'update']);
    Route::delete('/harga-bapok/{harga_bapok}', [HargaBapokController::class, 'destroy']);
    Route::get('/harga-bapok-table', [HargaBapokController::class, 'table']);

    // 🏪 Pasar (CRUD)
    Route::post('/pasar', [PasarController::class, 'store']);
    Route::put('/pasar/{pasar}', [PasarController::class, 'update']);
    Route::patch('/pasar/{pasar}', [PasarController::class, 'update']);
    Route::delete('/pasar/{pasar}', [PasarController::class, 'destroy']);
});