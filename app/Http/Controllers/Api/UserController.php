<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Tampilkan semua user yang merupakan petugas pasar beserta data pasar terkait.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
{
    $users = User::select('id', 'name', 'email', 'is_admin', 'is_petugas_pasar')
                ->where('is_petugas_pasar', true)
                ->get();

    return response()->json([
        'status' => 'success',
        'message' => 'Daftar petugas pasar berhasil diambil.',
        'data' => $users
    ]);
}

}
