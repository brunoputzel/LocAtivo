<?php

namespace App\Providers;

use App\Models\User;
use App\OpenApi\Extensions\BusinessExceptionToResponseExtension;
use App\Policies\UsuarioPolicy;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        // registro manual porque o model continua "User" (Breeze), mas a Policy
        // segue o nome de domínio em português - quebra a convenção de auto-discovery
        Gate::policy(User::class, UsuarioPolicy::class);

        // sem isso o Scramble não documenta o 400 de regra de negócio violada
        Scramble::registerExtension(BusinessExceptionToResponseExtension::class);
    }
}
