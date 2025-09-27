<?php

declare(strict_types=1);

namespace App\Entity\Steps;

use App\Contracts\StepInterface;
use DateTimeImmutable;

class BaseStep
{
    public readonly ?string $strTime;
    public readonly \DateTimeInterface $time;
    private ?\DateTimeInterface $nextTime;

    public function __construct(?string $time, ?string $nextTime = null)
    {
        if ($time && preg_match("/1899-12-30/", $time)) $time = null;
        $this->strTime = $time;
        $this->time = new DateTimeImmutable($time ?? "now");
        $this->setNextDate($nextTime);
    }

    public function setNextDate(?string $nextTime): self
    {
        if ($nextTime && preg_match("/1899-12-30/", $nextTime)) $nextTime = null;

        $this->nextTime = ($nextTime === null)
            ? null
            : new DateTimeImmutable($nextTime);
        return $this;
    }

    public function getFormattedTime(): ?string
    {
        return ($this->strTime === null)
            ? null
            : $this->time->format("Y-m-d H:i");
    }

    public function getDiffInSeconds(): int
    {
        $x = $this->nextTime ?: new DateTimeImmutable();
        $interval = $x->diff($this->time);

        return ($interval->h * 3600) + ($interval->i *60) + $interval->s;
    }

    public function getDiffFormatted(): string
    {
        $next = $this->nextTime ?: new DateTimeImmutable();
        $interval = $next->diff($this->time);

        return $interval->format('%H h %i m');
    }

    public function toArray(): array
    {
        return [
            "fecha" => $this->getFormattedTime(),
            "diff"  => $this->getDiffInSeconds(),
            "timestamp" => $this->time->getTimestamp(),
            "formatedDiff" => $this->getDiffFormatted(),
            "warning" => ($this instanceof StepInterface)
                ? $this->warning()
                : null
        ];
    }
}
