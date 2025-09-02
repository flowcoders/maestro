<?php

namespace Flowcoders\Maestro;

use Flowcoders\Maestro\Commands\MaestroCommand;
use Flowcoders\Maestro\Contracts\PaymentServiceProviderInterface;
use Flowcoders\Maestro\Factories\PaymentServiceProviderFactory;
use Flowcoders\Maestro\Utils\TimezoneHelper;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MaestroServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('maestro')
            ->hasConfigFile()
            ->hasCommand(MaestroCommand::class);
    }

    public function packageBooted(): void
    {
        $this->app->alias(PaymentServiceProviderInterface::class, 'maestro');

        $config = $this->app['config']['maestro'];
        $timezone = $config['timezone'] ?? config('app.timezone', date_default_timezone_get());
        TimezoneHelper::setTimezone($timezone);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(PaymentServiceProviderInterface::class, function ($app) {
            $config = $app['config']['maestro'];
            $factory = $app->make(PaymentServiceProviderFactory::class);

            $defaultProvider = $config['default'];
            $providerConfig = $config['providers'][$defaultProvider];

            return $factory->create($defaultProvider, $providerConfig);
        });
    }
}
