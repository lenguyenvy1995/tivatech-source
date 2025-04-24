<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\QuoteRequestPolicy;
use App\Models\QuoteRequest;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        QuoteRequest::class => QuoteRequestPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('role-saler', function ($user) {
            return $user->hasRole('saler');
        });
    
        Gate::define('role-quote-manager', function ($user) {
            return $user->hasRole('quote manager');
        });
    }
}
