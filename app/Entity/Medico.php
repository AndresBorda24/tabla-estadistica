<?php

declare(strict_types=1);

namespace App\Entity;

class Medico
{
    public function __construct(
        public readonly string $cod,
        public readonly string $nombre
    ) {}
}
