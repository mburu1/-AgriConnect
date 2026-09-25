<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define Gates for core platform roles
        Gate::define('admin', fn($user) => method_exists($user, 'hasRole') && $user->hasRole('ADMIN'));
        Gate::define('farmer', fn($user) => method_exists($user, 'hasRole') && $user->hasRole('FARMER'));
        Gate::define('customer', fn($user) => method_exists($user, 'hasRole') && $user->hasRole('CUSTOMER'));
    }
}
