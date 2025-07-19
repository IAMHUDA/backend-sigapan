<?php

namespace App\Providers;

use App\Models\BahanPokok; 
use App\Models\Pasar;

use App\Policies\BahanPokokPolicy;
use App\Policies\PasarPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Pemetaan kebijakan untuk aplikasi.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        BahanPokok::class => BahanPokokPolicy::class, 
        Pasar::class => PasarPolicy::class,
    ];

    /**
     * Daftarkan layanan autentikasi/otorisasi apa pun.
     */
    public function boot(): void
    {
        //
    }
}

