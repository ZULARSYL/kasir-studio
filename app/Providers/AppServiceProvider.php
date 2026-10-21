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
        \Illuminate\Support\Facades\View::composer('template.index', function ($view) {
            if (auth()->check()) {
                $notificationService = app(\App\Services\NotificationService::class);
                $notifications = $notificationService->getNotifications(auth()->user());
                $view->with('appNotifications', $notifications);
            } else {
                $view->with('appNotifications', [
                    'unread_count' => 0,
                    'total_count' => 0,
                    'items' => [],
                ]);
            }
        });
    }
}
