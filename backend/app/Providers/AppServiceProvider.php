<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind repository interfaces
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(
            \App\Repositories\Contracts\ProjectRepositoryInterface::class,
            \App\Repositories\Eloquent\ProjectRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\TaskRepositoryInterface::class,
            \App\Repositories\Eloquent\TaskRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\TeamRepositoryInterface::class,
            \App\Repositories\Eloquent\TeamRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\CommentRepositoryInterface::class,
            \App\Repositories\Eloquent\CommentRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
