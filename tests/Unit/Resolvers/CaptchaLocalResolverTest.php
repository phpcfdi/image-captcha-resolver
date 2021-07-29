<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit\Resolvers;

use Exception;
use PhpCfdi\ImageCaptchaResolver\CaptchaAnswer;
use PhpCfdi\ImageCaptchaResolver\CaptchaImageInterface;
use PhpCfdi\ImageCaptchaResolver\Resolvers\CaptchaLocalResolver;
use PhpCfdi\ImageCaptchaResolver\Resolvers\CaptchaLocalResolver\CaptchaLocalResolverConnector;
use PhpCfdi\ImageCaptchaResolver\Tests\TestCase;
use PhpCfdi\ImageCaptchaResolver\Tests\Unit\FakeExpiredTimer;
use PhpCfdi\ImageCaptchaResolver\Timer\Timer;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptchaException;
use PHPUnit\Framework\MockObject\MockObject;

final class CaptchaLocalResolverTest extends TestCase
{
    public function testCreate(): void
    {
        $baseUrl = 'http://localhost:9095';

        $resolver = CaptchaLocalResolver::create($baseUrl);

        /** @var Timer $timer */
        $timer = $resolver->getTimer();

        $this->assertSame($baseUrl, $resolver->getConnector()->getBaseUrl());
        $this->assertSame(CaptchaLocalResolver::DEFAULT_INITIAL_WAIT, $timer->getInitialSeconds());
        $this->assertSame(CaptchaLocalResolver::DEFAULT_TIMEOUT, $timer->getTimeoutSeconds());
        $this->assertSame(CaptchaLocalResolver::DEFAULT_WAIT, $timer->getWaitMilliseconds());
    }

    public function testResolveWithValue(): void
    {
        $captchaAnswer = new CaptchaAnswer('captcha-value');
        $image = $this->createMock(CaptchaImageInterface::class);
        /** @var CaptchaLocalResolverConnector&MockObject $connector */
        $connector = $this->createMock(CaptchaLocalResolverConnector::class);
        $connector->expects($this->once())->method('resolveImage')->willReturn($captchaAnswer);
        $resolver = new CaptchaLocalResolver($connector, new FakeExpiredTimer());

        $this->assertSame($captchaAnswer, $resolver->resolve($image));
    }

    public function testResolveWithException(): void
    {
        $error = new Exception('Dummy Exception');
        $image = $this->createMock(CaptchaImageInterface::class);
        /** @var CaptchaLocalResolverConnector&MockObject $connector */
        $connector = $this->createMock(CaptchaLocalResolverConnector::class);
        $connector->expects($this->once())->method('resolveImage')->willThrowException($error);
        $resolver = new CaptchaLocalResolver($connector, new FakeExpiredTimer());

        $catchedException = null;
        try {
            $resolver->resolve($image);
        } catch (UnableToResolveCaptchaException $exception) {
            $catchedException = $exception;
        }

        if (null === $catchedException) {
            $this->fail('Resolver::resolve does not thow expected exception');
        }
        $this->assertSame($error, $catchedException->getPrevious());
        $this->assertSame($image, $catchedException->getImage());
        $this->assertSame($resolver, $catchedException->getResolver());
    }
}
