<?php

declare(strict_types=1);

namespace App\Entity;

class Paciente
{
    public function __construct(
        public readonly string $nombre,
        public readonly int $edad,
        public readonly string $documento,
        public readonly string $genero
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            nombre: $data['nombre'],
            edad: (int) $data['edad'],
            documento: $data['documento'],
            genero: $data['genero'] ?? 'F'
        );
    }
}
