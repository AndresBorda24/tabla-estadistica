<?php

declare(strict_types=1);

namespace App\Entity\Steps;

use App\Contracts\StepInterface;

class EgresoAdmision extends BaseStep implements StepInterface
{
    public function warning(): bool
    {
        return false;
    }
}
