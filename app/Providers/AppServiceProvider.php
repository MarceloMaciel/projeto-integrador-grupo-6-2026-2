<?php

namespace App\Providers;

use App\Services\Fiscal\NfceXmlBuilder;
use App\Services\Fiscal\TestCertificate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TestCertificate::class, fn () => new TestCertificate(
            config('fiscal.certificate.path'),
            config('fiscal.certificate.password'),
        ));

        $this->app->bind(NfceXmlBuilder::class, fn ($app) => new NfceXmlBuilder(
            config('fiscal'),
            $app->make(TestCertificate::class),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
