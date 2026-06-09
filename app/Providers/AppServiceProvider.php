<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

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
        Vite::prefetch(concurrency: 3);
        
        if (app()->environment('local')) {
            // Em desenvolvimento, evitar falhas intermitentes de CSRF na rota de login
            VerifyCsrfToken::except(['login']);
        }

        // Regra para Administrador
        Gate::before(function (User $user, string $ability) {
            if ($user->role === 'admin') {
                return true;
            }
        });

        // Regra para Gestor
        Gate::define('isGestor', function (User $user) {
            return $user->role === 'gestor';
        });
    }
}
