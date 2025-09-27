<?php

declare(strict_types=1);

namespace App\Entity\Steps;

use App\Contracts\StepInterface;

class EgresoUrge extends BaseStep implements StepInterface
{
    public function __construct(
        public readonly string $destino,
        ?string $time,
        ?string $nextTime = null
    ) {
        parent::__construct($time, $nextTime);
    }

    public function warning(): bool
    {
        return false;
    }
}
