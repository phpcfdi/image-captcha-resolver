<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit\Resolvers;

use Countable;
use OutOfRangeException;
use PhpCfdi\ImageCaptchaResolver\CaptchaAnswer;
use PhpCfdi\ImageCaptchaResolver\CaptchaImageInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use PhpCfdi\ImageCaptchaResolver\Resolvers\MockResolver;
use PhpCfdi\ImageCaptchaResolver\Tests\TestCase;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptchaException;

final class MockResolverTest extends TestCase
{
    public function testMinimalMockResolver(): void
    {
        $resolver = new MockResolver();
        $this->assertInstanceOf(CaptchaResolverInterface::class, $resolver);
        $this->assertInstanceOf(Countable::class, $resolver);
        $this->assertCount(0, $resolver);
        $this->assertSame(0, $resolver->count());
        $this->assertTrue($resolver->isEmpty());
    }

    public function testCountWithPredefinedValues(): void
    {
        $image = $this->createMock(CaptchaImageInterface::class);

        $resolver = new MockResolver(...[
            $first = new CaptchaAnswer('foo'),
            $second = $this->createMock(UnableToResolveCaptchaException::class),
            $third = new CaptchaAnswer('bar'),
        ]);
        $this->assertFalse($resolver->isEmpty());
        $this->assertCount(3, $resolver);

        $response = $resolver->resolve($image);
        $this->assertSame($first, $response);
        $this->assertCount(2, $resolver);

        $catchedException = null;
        try {
            $resolver->resolve($image);
        } catch (UnableToResolveCaptchaException $exception) {
            $catchedException = $exception;
        }
        $this->assertSame($second, $catchedException);
        $this->assertCount(1, $resolver);

        $response = $resolver->resolve($image);
        $this->assertSame($third, $response);
        $this->assertCount(0, $resolver);
    }

    public function testResolveThrowsExceptionIfIsEmpty(): void
    {
        $image = $this->createMock(CaptchaImageInterface::class);
        $resolver = new MockResolver();
        $this->assertTrue($resolver->isEmpty());

        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('MockResolver does not have any response to process');
        $resolver->resolve($image);
    }
}
