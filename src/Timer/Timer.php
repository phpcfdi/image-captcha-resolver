<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Timer;

final class Timer implements TimerInterface
{
    /** @var int */
    private $initialSeconds;

    /** @var int */
    private $timeoutSeconds;

    /** @var int */
    private $waitMilliseconds;

    /** @var float */
    private $untilTime = 0;

    public function __construct(int $initialSeconds, int $timeoutSeconds, int $waitMilliseconds)
    {
        $this->initialSeconds = $initialSeconds;
        $this->timeoutSeconds = $timeoutSeconds;
        $this->waitMilliseconds = $waitMilliseconds;
    }

    public function getInitialSeconds(): int
    {
        return $this->initialSeconds;
    }

    public function getTimeoutSeconds(): int
    {
        return $this->timeoutSeconds;
    }

    public function getWaitMilliseconds(): int
    {
        return $this->waitMilliseconds;
    }

    public function now(): float
    {
        return microtime(true);
    }

    public function wait(): void
    {
        usleep($this->waitMilliseconds * 1000);
    }

    public function start(): void
    {
        $this->untilTime = $this->now() + $this->timeoutSeconds;
        $this->initialWait();
    }

    public function isExpired(): bool
    {
        return $this->now() > $this->untilTime;
    }

    public function initialWait(): void
    {
        sleep($this->initialSeconds);
    }
}
