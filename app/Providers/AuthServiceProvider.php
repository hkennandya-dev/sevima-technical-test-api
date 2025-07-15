<?php

namespace App\Providers;

use App\Models\User;
use App\Models\PersonalAccessToken;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     */
    public function boot()
    {
        $this->app['auth']->viaRequest('api', function ($request) {
            $token = $request->bearerToken();

            if (!$token) {
                return null;
            }

            $accessToken = PersonalAccessToken::where('token', $token)->first();

            return $accessToken ? $accessToken->user : null;
        });
    }
}
