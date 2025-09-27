<?php

declare(strict_types=1);

namespace App\Contracts;

interface StepInterface
{
    public function warning(): bool;
}
