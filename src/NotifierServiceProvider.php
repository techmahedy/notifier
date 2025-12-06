<?php

namespace Doppar\Notifier;

use Phaseolies\Providers\ServiceProvider;
use Doppar\Notifier\NotificationManager;
use Doppar\Notifier\Console\Commands\MakeNotificationCommand;

class NotifierServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(NotificationManager::class, function ($app) {
            return new NotificationManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadMigrations(__DIR__ . '/database/migrations');

        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
        ], 'migrations');

        $this->commands([
            MakeNotificationCommand::class
        ]);
    }
}
