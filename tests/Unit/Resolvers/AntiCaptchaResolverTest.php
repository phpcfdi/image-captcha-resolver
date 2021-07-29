<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit\Resolvers;

use Exception;
use PhpCfdi\ImageCaptchaResolver\CaptchaAnswer;
use PhpCfdi\ImageCaptchaResolver\CaptchaImageInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use PhpCfdi\ImageCaptchaResolver\Resolvers\AntiCaptchaResolver;
use PhpCfdi\ImageCaptchaResolver\Resolvers\AntiCaptchaResolver\AntiCaptchaConnector;
use PhpCfdi\ImageCaptchaResolver\Tests\HttpTestCase;
use PhpCfdi\ImageCaptchaResolver\Tests\Unit\FakeExpiredTimer;
use PhpCfdi\ImageCaptchaResolver\Timer\Timer;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptchaException;
use PHPUnit\Framework\MockObject\MockObject;

final class AntiCaptchaResolverTest extends HttpTestCase
{
    public function testCreate(): void
    {
        $clientKey = 'client-key';

        $resolver = AntiCaptchaResolver::create($clientKey);
        $this->assertInstanceOf(CaptchaResolverInterface::class, $resolver);

        $this->assertSame($clientKey, $resolver->getConnector()->getClientKey());

        /** @var Timer $timer */
        $timer = $resolver->getTimer();
        $this->assertSame(AntiCaptchaResolver::DEFAULT_INITIAL_WAIT, $timer->getInitialSeconds());
        $this->assertSame(AntiCaptchaResolver::DEFAULT_TIMEOUT, $timer->getTimeoutSeconds());
        $this->assertSame(AntiCaptchaResolver::DEFAULT_WAIT, $timer->getWaitMilliseconds());
    }

    public function testResolveWithValue(): void
    {
        $taskId = 'task-id';
        $taskResult = 'task-result';
        $captchaAnswer = new CaptchaAnswer($taskResult);
        $image = $this->createMock(CaptchaImageInterface::class);
        /** @var AntiCaptchaConnector&MockObject $connector */
        $connector = $this->createMock(AntiCaptchaConnector::class);
        $connector->expects($this->once())->method('createTask')->willReturn($taskId);
        $connector->expects($this->once())->method('getTaskResult')->willReturn($taskResult);

        $resolver = new AntiCaptchaResolver($connector, new FakeExpiredTimer());
        $answer = $resolver->resolve($image);

        $this->assertEquals($captchaAnswer, $answer);
    }

    public function testResolveWithException(): void
    {
        $error = new Exception('Dummy Exception');
        $image = $this->createMock(CaptchaImageInterface::class);
        /** @var AntiCaptchaConnector&MockObject $connector */
        $connector = $this->createMock(AntiCaptchaConnector::class);
        $connector->expects($this->once())->method('createTask')->willThrowException($error);

        $resolver = new AntiCaptchaResolver($connector, new FakeExpiredTimer());

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

    public function testResolveWithRetry(): void
    {
        $image = $this->createMock(CaptchaImageInterface::class);
        /** @var AntiCaptchaConnector&MockObject $connector */
        $connector = $this->createMock(AntiCaptchaConnector::class);
        $connector->expects($this->once())->method('createTask')->willReturn('task-id');

        $resolver = new AntiCaptchaResolver($connector, new FakeExpiredTimer(1));

        $catchedException = null;
        try {
            $resolver->resolve($image);
        } catch (UnableToResolveCaptchaException $exception) {
            $catchedException = $exception;
        }

        if (null === $catchedException) {
            $this->fail('Resolver::resolve does not thow expected exception');
        }
        $this->assertSame($image, $catchedException->getImage());
        $this->assertSame($resolver, $catchedException->getResolver());

        $previousException = $catchedException->getPrevious();
        if (null === $previousException) {
            $this->fail('Catched exception should contains a previous exception');
        }
        $this->assertSame('Unable to resolve captcha after 0 seconds', $previousException->getMessage());
    }
}
