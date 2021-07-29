<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit\HttpClient;

use PhpCfdi\ImageCaptchaResolver\HttpClient\UndiscoverableClientException;
use PhpCfdi\ImageCaptchaResolver\Tests\TestCase;
use Throwable;

class UndiscoverableClientExceptionTest extends TestCase
{
    public function testObjectProperties(): void
    {
        $previous = $this->createMock(Throwable::class);
        $exception = new UndiscoverableClientException($previous);
        $this->assertSame('Cannot discover the HttpClient', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
