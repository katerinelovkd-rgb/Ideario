<?php

namespace App\Providers;

use App\Models\Categoria;
use App\Models\Idea;
use App\Policies\CategoriaPolicy;
use App\Policies\IdeaPolicy;
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
        Gate::policy(Idea::class, IdeaPolicy::class);
        Gate::policy(Categoria::class, CategoriaPolicy::class);
    }
}
