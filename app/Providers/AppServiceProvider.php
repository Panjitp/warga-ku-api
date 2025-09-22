<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // <-- Tambahkan ini

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Daftarkan Gate di sini
        Gate::define('is-pengurus', function (User $user) {
            return $user->role === 'rw' || $user->role === 'rt';
        });

        Gate::define('is-pengurus-rw', function (User $user) {
            return $user->role === 'rw';
        });

        Gate::define('is-pengurus-rt', function (User $user) {
            return $user->role === 'rt';
        });
        
        Gate::define('is-warga', function (User $user) {
            return $user->role === 'warga';
        });
    }
}