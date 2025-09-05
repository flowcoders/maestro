<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Factories;

use InvalidArgumentException;
use Flowcoders\Maestro\Http\BaseHttpClient;
use Flowcoders\Maestro\Adapters\AsaasAdapter;
use Illuminate\Http\Client\Factory as HttpFactory;
use Flowcoders\Maestro\Adapters\MercadoPagoAdapter;
use Flowcoders\Maestro\Exceptions\MaestroException;
use Flowcoders\Maestro\Mappers\MercadoPagoPaymentMapper;
use Flowcoders\Maestro\Mappers\AsaasPaymentMapper;
use Flowcoders\Maestro\Contracts\PaymentServiceProviderInterface;

readonly class PaymentServiceProviderFactory
{
    public function __construct(
        private HttpFactory $httpFactory
    ) {
    }

    /**
     * @throws MaestroException
     */
    public function create(string $provider, array $credentials): PaymentServiceProviderInterface
    {
        return match ($provider) {
            'mercadopago' => $this->createMercadoPagoAdapter($credentials),
            'asaas' => $this->createAsaasAdapter($credentials),
            default => throw new InvalidArgumentException("Unsupported payment provider: {$provider}"),
        };
    }

    /**
     * @throws MaestroException
     */
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
            mapper: $mapper
        );
    }

    /**
     * @throws MaestroException
     */
    private function createAsaasAdapter(array $credentials): AsaasAdapter
    {
        $this->validateAsaasCredentials($credentials);

        // Asaas API uses /v3 as part of the base URL
        // Example: https://api.asaas.com/v3 or https://api-sandbox.asaas.com/v3
        $baseUrl = rtrim($credentials['base_url'], '/');
        if (!str_ends_with($baseUrl, '/v3')) {
            $baseUrl .= '/v3';
        }

        $accessToken = $credentials['access_token'];

        $httpClient = new BaseHttpClient(
            httpFactory: $this->httpFactory,
            baseUrl: $baseUrl,
            defaultHeaders: [
                'Content-Type' => 'application/json',
                'User-Agent' => 'Maestro-PHP-SDK/1.0',
                'access_token' => $accessToken,
            ],
            timeout: 30
        );

        $mapper = new AsaasPaymentMapper();

        return new AsaasAdapter(
            httpClient: $httpClient,
            mapper: $mapper
        );
    }

    private function validateAsaasCredentials(array $credentials): void
    {
        if (!isset($credentials['access_token']) || empty($credentials['access_token'])) {
            throw new MaestroException('Asaas access_token is required');
        }

        if (!isset($credentials['base_url']) || empty($credentials['base_url'])) {
            throw new MaestroException('Asaas base_url is required');
        }
    }

    private function validateMercadoPagoCredentials(array $credentials): void
    {
        if (!isset($credentials['access_token']) || empty($credentials['access_token'])) {
            throw new MaestroException('MercadoPago access_token is required');
        }
    }
}
