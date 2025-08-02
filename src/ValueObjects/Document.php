<?php

namespace Flowcoders\Maestro\ValueObjects;

use Flowcoders\Maestro\Enums\DocumentType;
use InvalidArgumentException;

readonly class Document
{
    public function __construct(
        public DocumentType $type,
        public string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->value)) {
            throw new InvalidArgumentException('Document value cannot be empty.');
        }

        if ($this->type->isCpf() && strlen($this->value) !== 11) {
            throw new InvalidArgumentException("Invalid CPF: {$this->value}");
        }

        if ($this->type->isCnpj() && strlen($this->value) !== 14) {
            throw new InvalidArgumentException("Invalid CNPJ: {$this->value}");
        }
    }
}
