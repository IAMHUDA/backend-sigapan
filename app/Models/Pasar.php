<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pasar extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika tidak mengikuti konvensi Laravel (plural dari nama model)
    protected $table = 'pasar'; // Laravel secara default akan mencari 'pasar'

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'alamat',
        'foto',
        'jumlah_pedagang',
        'jumlah_kios',
        'jumlah_mck',
        'jumlah_bango',
        'jumlah_kantor',
        'tps',
        'keterangan',
        'latitude',
        'longitude',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    // Relasi ke HargaBapok (akan dibuat nanti)
    public function hargaBapok()
    {
        return $this->hasMany(HargaBapok::class, 'id_pasar');
    }
}

