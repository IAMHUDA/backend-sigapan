<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HargaBapok extends Model
{
    use HasFactory;

    protected $table = 'harga_bapok'; // Nama tabel

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_pasar',
        'id_bahan_pokok',
        'tanggal',
        'harga',
        'created_by',
        'stok',
        'status_integrasi',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal' => 'date',
        'harga' => 'integer', 
    ];

    /**
     * Get the pasar that owns the harga bapok.
     */
    public function pasar()
    {
        return $this->belongsTo(Pasar::class, 'id_pasar');
    }

    /**
     * Get the bahan pokok that owns the harga bapok.
     */
    public function bahanPokok()
    {
        return $this->belongsTo(BahanPokok::class, 'id_bahan_pokok');
    }

    /**
     * Get the user that created the harga bapok.
     */
    public function creator()
    {
        // Asumsi 'created_by' menyimpan ID pengguna atau email pengguna
        // Jika menyimpan ID, Anda bisa menggunakan: return $this->belongsTo(User::class, 'created_by');
        // Jika menyimpan email/nama, Anda bisa mengambilnya berdasarkan kolom tersebut.
        // Untuk saat ini, kita akan mengasumsikan ini adalah string nama/email.
        // Jika Anda ingin ini menjadi relasi ke tabel users, Anda harus mengubah tipe kolom
        // 'created_by' di migrasi menjadi foreignId('created_by')->constrained('users')->onDelete('cascade');
        // dan di model menjadi: return $this->belongsTo(User::class, 'created_by');
        return $this->belongsTo(User::class, 'created_by', 'email'); // Asumsi created_by menyimpan email
    }
}