<?php

namespace App\Providers;

use App\Models\CustomObject;
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
        //CustomObject::observe(CustomObjectObserver::class);

        Gate::before(fn ($user) => $user->hasRole('admin') ? true : null);
    }
}
