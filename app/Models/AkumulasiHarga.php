<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AkumulasiHarga extends Model
{
    use HasFactory;

    protected $table = 'akumulasi_harga';

    protected $fillable = [
        'id_pasar',
        'id_bahan_pokok',
        'tanggal',
        'harga_rata2',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'harga_rata2' => 'decimal:2',
    ];

    public function pasar()
    {
        return $this->belongsTo(Pasar::class, 'id_pasar');
    }

    public function bahanPokok()
    {
        return $this->belongsTo(BahanPokok::class, 'id_bahan_pokok');
    }
}
