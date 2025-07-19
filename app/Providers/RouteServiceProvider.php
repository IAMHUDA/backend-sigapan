<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Path ke direktori "home" untuk pengguna aplikasi Anda.
     *
     * Biasanya digunakan oleh Laravel untuk mengarahkan pengguna setelah autentikasi.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Daftarkan layanan aplikasi apa pun.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrapping layanan aplikasi apa pun.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            
            Route::middleware('api')
                ->prefix('api') 
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}