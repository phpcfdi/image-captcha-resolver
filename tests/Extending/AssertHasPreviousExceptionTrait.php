<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Extending;

use PHPUnit\Framework\Assert;
use Throwable;

trait AssertHasPreviousExceptionTrait
{
    public static function assertHasPreviousException(
        Throwable $expectedException,
        Throwable $exception,
        string $message = '',
    ): void {
        Assert::assertThat(
            $expectedException,
            static::hasPreviousException($exception),
            $message,
        );
    }

    public static function hasPreviousException(Throwable $exception): HasPreviousException
    {
        return new HasPreviousException($exception);
    }
}
