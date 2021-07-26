<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit;

use PhpCfdi\ImageCaptchaResolver\CaptchaImageInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use PhpCfdi\ImageCaptchaResolver\Tests\TestCase;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptcha;
use Throwable;

final class UnableToResolveCaptchaTest extends TestCase
{
    public function testConstructWithProperties(): void
    {
        $resolver = $this->createMock(CaptchaResolverInterface::class);
        $image = $this->createMock(CaptchaImageInterface::class);
        $previous = $this->createMock(Throwable::class);

        $exception = new UnableToResolveCaptcha($resolver, $image, $previous);

        $this->assertSame($resolver, $exception->getResolver());
        $this->assertSame($image, $exception->getImage());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame('Unable to resolve captcha image', $exception->getMessage());
    }

    public function testConstructWithoutPrevious(): void
    {
        $resolver = $this->createMock(CaptchaResolverInterface::class);
        $image = $this->createMock(CaptchaImageInterface::class);

        $exception = new UnableToResolveCaptcha($resolver, $image);

        $this->assertNull($exception->getPrevious());
    }
}
