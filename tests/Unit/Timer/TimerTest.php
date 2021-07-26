<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit\Timer;

use PhpCfdi\ImageCaptchaResolver\Tests\TestCase;
use PhpCfdi\ImageCaptchaResolver\Timer\Timer;
use PhpCfdi\ImageCaptchaResolver\Timer\TimerInterface;

final class TimerTest extends TestCase
{
    public function testObjectCreation(): void
    {
        $initialSeconds = 1;
        $timeoutSeconds = 2;
        $waitMilliseconds = 3;

        $timer = new Timer($initialSeconds, $timeoutSeconds, $waitMilliseconds);

        $this->assertInstanceOf(TimerInterface::class, $timer);
        $this->assertSame($initialSeconds, $timer->getInitialSeconds());
        $this->assertSame($timeoutSeconds, $timer->getTimeoutSeconds());
        $this->assertSame($waitMilliseconds, $timer->getWaitMilliseconds());
    }

    public function testExpiredExpectingNotExpired(): void
    {
        $timer = new Timer(0, 1, 0);
        $timer->start();
        $this->assertFalse($timer->isExpired());
    }

    public function testExpiredExpectingExpired(): void
    {
        $timer = new Timer(0, 0, 1);
        $timer->start();
        $timer->wait();
        $this->assertTrue($timer->isExpired());
    }
}
