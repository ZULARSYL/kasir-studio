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
        // Gunakan Bootstrap 5 untuk seluruh pagination Laravel
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        // Pengaturan Timezone Jakarta (WIB / UTC+7)
        config(['app.timezone' => 'Asia/Jakarta']);
        date_default_timezone_set('Asia/Jakarta');

        // Pengaturan Bahasa & Format Tanggal Indonesia
        \Carbon\Carbon::setLocale('id');
        \Illuminate\Support\Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID.utf8', 'id_ID.UTF-8', 'id_ID', 'id', 'Indonesian_indonesia.1252', 'Indonesian');

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
