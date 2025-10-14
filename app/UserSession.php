<?php

declare(strict_types=1);

namespace App;

final class UserSession
{
    public function __construct(
        public readonly string $usuario,
        public readonly int $id,
        public readonly int $cargo,
        public readonly string $area,
        public readonly int $areaId,
        public readonly string $grupo,
        public readonly ?int $medicoId,
        public readonly string $nombre,
    ) {}
}