<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit\Resolvers;

use PhpCfdi\ImageCaptchaResolver\CaptchaAnswer;
use PhpCfdi\ImageCaptchaResolver\CaptchaImageInterface;
use PhpCfdi\ImageCaptchaResolver\Resolvers\MockResolver;
use PhpCfdi\ImageCaptchaResolver\Resolvers\MultiResolver;
use PhpCfdi\ImageCaptchaResolver\Tests\TestCase;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptcha;

final class MultiResolverTest extends TestCase
{
    public function testWithEmptyResolvers(): void
    {
        $image = $this->createMock(CaptchaImageInterface::class);
        $resolver = new MultiResolver();
        $this->assertCount(0, $resolver);
        $this->assertSame([], $resolver->getResolvers());

        $this->expectException(UnableToResolveCaptcha::class);
        $resolver->resolve($image);
    }

    public function testWithResolversThatReturnsAnswerAtEnd(): void
    {
        $image = $this->createMock(CaptchaImageInterface::class);
        $unableToResolveCaptcha = $this->createMock(UnableToResolveCaptcha::class);
        $predefinedAnswer = new CaptchaAnswer('qwerty');
        $givenResolvers = [
            new MockResolver($unableToResolveCaptcha),
            new MockResolver($unableToResolveCaptcha),
            new MockResolver($predefinedAnswer),
            new MockResolver($unableToResolveCaptcha), // this won't be executed
        ];
        $resolver = new MultiResolver(...$givenResolvers);
        $this->assertSame($givenResolvers, $resolver->getResolvers());

        $answer = $resolver->resolve($image);

        $this->assertTrue($predefinedAnswer->equalsTo($answer));

        $expectedResults = [
            $unableToResolveCaptcha,
            $unableToResolveCaptcha,
            $predefinedAnswer,
        ];

        $this->assertSame($expectedResults, $resolver->getLastResults());
    }
}
