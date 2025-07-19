<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanPokok extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika tidak mengikuti konvensi Laravel (plural dari nama model)
    protected $table = 'bahan_pokok';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_api_bahan_pokok',
        'urutan',
        'nama',
        'satuan',
        'foto',
        'up_stok',
        'stok_wajib',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data tertentu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'up_stok' => 'boolean', // Cast 0/1 menjadi boolean
        'stok_wajib' => 'boolean', // Cast 0/1 menjadi boolean
    ];
}

