<?php

namespace App\Providers;

use App\Migrators\IdMatcher;
use App\Migrators\VimbAdmin\AliasMigrator;
use App\Migrators\VimbAdmin\DomainMigrator;
use App\Migrators\VimbAdmin\MailboxMigrator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * All of the container singletons that should be registered.
     *
     * @var array
     */
    public $singletons = [
        MailboxMigrator::class => MailboxMigrator::class,
        AliasMigrator::class   => AliasMigrator::class,
        IdMatcher::class       => IdMatcher::class,
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DomainMigrator::class, function ($app, $params) {
            return new DomainMigrator(...$params);
        });
    }
}
