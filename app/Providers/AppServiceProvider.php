<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use App\Providers\TelescopeServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            //$this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url') . "/contrasena/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        ResponseFactory::macro('created', function ($location = null) {

            if (func_num_args() === 0) {
                return $this->noContent(Response::HTTP_CREATED);
            }

            return $this->noContent(Response::HTTP_CREATED, ['Location' => $location]);
        });

        Gate::policy(User::class, UserPolicy::class);
        if($this->app->environment('local')) {
            Model::preventSilentlyDiscardingAttributes();
        }
    }
}
