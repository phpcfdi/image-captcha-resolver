<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Timer;

interface TimerInterface
{
    /**
     * Calculate the time of expiration, it could create an initial sleep
     */
    public function start(): void;

    /**
     * Wait for some time
     */
    public function wait(): void;

    /**
     * Return true if the timer is expired
     */
    public function isExpired(): bool;

    /**
     * The total time to wait to define that the timer is expired
     */
    public function getTimeoutSeconds(): int;
}
