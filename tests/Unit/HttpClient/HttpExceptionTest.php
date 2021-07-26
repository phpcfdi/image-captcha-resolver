<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit\HttpClient;

use LogicException;
use PhpCfdi\ImageCaptchaResolver\HttpClient\HttpException;
use PhpCfdi\ImageCaptchaResolver\Tests\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class HttpExceptionTest extends TestCase
{
    public function testObjectProperties(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $previous = $this->createMock(Throwable::class);

        $exception = new HttpException($request, $response, $previous);

        $this->assertSame($request, $exception->getRequest());
        $this->assertSame($response, $exception->getResponse());
        $this->assertTrue($exception->hasResponse());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testObjectCreationMinimal(): void
    {
        $request = $this->createMock(RequestInterface::class);

        $exception = new HttpException($request);

        $this->assertSame($request, $exception->getRequest());
        $this->assertFalse($exception->hasResponse());
        $this->assertNull($exception->getPrevious());
    }

    public function testGetResponseWithoutResponse(): void
    {
        $request = $this->createMock(RequestInterface::class);

        $exception = new HttpException($request);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The exception does not have a response');
        $exception->getResponse();
    }
}
