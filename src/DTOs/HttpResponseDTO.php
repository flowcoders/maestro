<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

readonly class HttpResponseDTO
{
    public function __construct(
        public array $data,
        public int $statusCode,
        public array $headers = [],
        public ?string $error = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }
}
