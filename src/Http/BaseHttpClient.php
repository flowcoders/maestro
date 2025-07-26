<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Http;

use Flowcoders\Maestro\Contracts\HttpClientInterface;
use Flowcoders\Maestro\DTOs\HttpResponseDTO;
use Flowcoders\Maestro\Exceptions\HttpClientException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Response;
use Throwable;

class BaseHttpClient implements HttpClientInterface
{
    public function __construct(
        private readonly Factory $httpFactory,
        private readonly string $baseUrl,
        private readonly array $defaultHeaders = [],
        private readonly int $timeout = 30,
        private readonly ?string $bearerToken = null,
    ) {}

    public function post(string $endpoint, array $data = [], array $headers = []): HttpResponseDTO
    {
        return $this->makeRequest('POST', $endpoint, $data, $headers);
    }

    public function get(string $endpoint, array $query = [], array $headers = []): HttpResponseDTO
    {
        return $this->makeRequest('GET', $endpoint, $query, $headers);
    }

    public function put(string $endpoint, array $data = [], array $headers = []): HttpResponseDTO
    {
        return $this->makeRequest('PUT', $endpoint, $data, $headers);
    }

    public function delete(string $endpoint, array $headers = []): HttpResponseDTO
    {
        return $this->makeRequest('DELETE', $endpoint, [], $headers);
    }

    private function makeRequest(string $method, string $endpoint, array $data = [], array $headers = []): HttpResponseDTO
    {
        try {
            $url = $this->baseUrl . $endpoint;
            $mergedHeaders = array_merge($this->defaultHeaders, $headers);
            
            // Add bearer token if provided
            if ($this->bearerToken !== null) {
                $mergedHeaders['Authorization'] = "Bearer {$this->bearerToken}";
            }

            $response = $this->httpFactory
                ->timeout($this->timeout)
                ->withHeaders($mergedHeaders)
                ->send($method, $url, [
                    'json' => $data,
                    'query' => $method === 'GET' ? $data : [],
                ]);

            return $this->buildResponseDTO($response);
        } catch (Throwable $exception) {
            throw new HttpClientException(
                "HTTP request failed: {$exception->getMessage()}",
                $exception->getCode(),
                $exception
            );
        }
    }

    private function buildResponseDTO(Response $response): HttpResponseDTO
    {
        return new HttpResponseDTO(
            data: $response->json() ?? [],
            statusCode: $response->status(),
            headers: $response->headers(),
            error: $response->successful() ? null : $response->body(),
        );
    }
}
