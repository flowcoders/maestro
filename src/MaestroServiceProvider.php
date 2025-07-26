<?php

namespace Flowcoders\Maestro;

use Flowcoders\Maestro\Commands\MaestroCommand;
use Flowcoders\Maestro\Contracts\PaymentServiceProviderInterface;
use Flowcoders\Maestro\Factories\PaymentServiceProviderFactory;
use Illuminate\Http\Client\Factory as HttpFactory;
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
        // Register facade alias
        $this->app->alias(PaymentServiceProviderInterface::class, 'maestro');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(PaymentServiceProviderFactory::class, function ($app) {
            return new PaymentServiceProviderFactory(
                $app->make(HttpFactory::class)
            );
        });

        $this->app->singleton(PaymentServiceProviderInterface::class, function ($app) {
            $config = $app['config']['maestro'];
            $factory = $app->make(PaymentServiceProviderFactory::class);

            $defaultProvider = $config['default'];
            $providerConfig = $config['providers'][$defaultProvider];

            return $factory->create($defaultProvider, $providerConfig);
        });
    }
}
