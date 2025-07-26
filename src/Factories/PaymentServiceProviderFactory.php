<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Factories;

use Flowcoders\Maestro\Adapters\MercadoPagoAdapter;
use Flowcoders\Maestro\Contracts\PaymentServiceProviderInterface;
use Flowcoders\Maestro\Exceptions\MaestroException;
use Flowcoders\Maestro\Http\BaseHttpClient;
use Flowcoders\Maestro\Mappers\MercadoPagoPaymentMapper;
use Illuminate\Http\Client\Factory as HttpFactory;
use InvalidArgumentException;

class PaymentServiceProviderFactory
{
    public function __construct(
        private readonly HttpFactory $httpFactory
    ) {}

    public function create(string $provider, array $credentials): PaymentServiceProviderInterface
    {
        return match ($provider) {
            'mercadopago' => $this->createMercadoPagoAdapter($credentials),
            default => throw new InvalidArgumentException("Unsupported payment provider: {$provider}"),
        };
    }

    private function createMercadoPagoAdapter(array $credentials): MercadoPagoAdapter
    {
        $this->validateMercadoPagoCredentials($credentials);

        // MercadoPago uses the same base URL for both sandbox and production
        // The environment is determined by the access token type (TEST- vs APP-)
        $baseUrl = 'https://api.mercadopago.com';

        $httpClient = new BaseHttpClient(
            httpFactory: $this->httpFactory,
            baseUrl: $baseUrl,
            defaultHeaders: [
                'Content-Type' => 'application/json',
                'User-Agent' => 'Maestro-PHP-SDK/1.0',
            ],
            timeout: 30,
            bearerToken: $credentials['access_token']
        );

        $mapper = new MercadoPagoPaymentMapper();

        return new MercadoPagoAdapter(
            httpClient: $httpClient,
            mapper: $mapper,
            credentials: $credentials
        );
    }

    private function validateMercadoPagoCredentials(array $credentials): void
    {
        if (!isset($credentials['access_token']) || empty($credentials['access_token'])) {
            throw new MaestroException('MercadoPago access_token is required');
        }
    }
}
