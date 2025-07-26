<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Contracts;

use Flowcoders\Maestro\DTOs\HttpResponseDTO;

interface HttpClientInterface
{
    public function post(string $endpoint, array $data = [], array $headers = []): HttpResponseDTO;

    public function get(string $endpoint, array $query = [], array $headers = []): HttpResponseDTO;

    public function put(string $endpoint, array $data = [], array $headers = []): HttpResponseDTO;

    public function delete(string $endpoint, array $headers = []): HttpResponseDTO;
}
