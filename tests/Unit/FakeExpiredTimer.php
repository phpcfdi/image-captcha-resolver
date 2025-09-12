<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit;

use PhpCfdi\ImageCaptchaResolver\Timer\TimerInterface;

class FakeExpiredTimer implements TimerInterface
{
    private int|float $waitCount = 0;

    public function __construct(public int $expireAfter = 0)
    {
    }

    public function getTimeoutSeconds(): int
    {
        return 0;
    }

    public function wait(): void
    {
        $this->waitCount = $this->waitCount + 1;
    }

    public function start(): void
    {
        $this->waitCount = 0;
    }

    public function isExpired(): bool
    {
        return $this->waitCount >= $this->expireAfter;
    }
}
