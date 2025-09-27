<?php

declare(strict_types=1);

namespace App\Entity\Steps;

use App\Contracts\StepInterface;

class Hurge extends BaseStep implements StepInterface
{
    public function warning(): bool
    {
        return false;
    }
}
