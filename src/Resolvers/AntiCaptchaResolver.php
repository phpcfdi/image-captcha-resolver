<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Resolvers;

use PhpCfdi\ImageCaptchaResolver\CaptchaAnswer;
use PhpCfdi\ImageCaptchaResolver\CaptchaAnswerInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaImageInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use PhpCfdi\ImageCaptchaResolver\HttpClient\UndiscoverableClientException;
use PhpCfdi\ImageCaptchaResolver\Resolvers\AntiCaptchaResolver\AntiCaptchaConnector;
use PhpCfdi\ImageCaptchaResolver\Timer\Timer;
use PhpCfdi\ImageCaptchaResolver\Timer\TimerInterface;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptchaException;
use RuntimeException;
use Throwable;

final class AntiCaptchaResolver implements CaptchaResolverInterface
{
    public const DEFAULT_INITIAL_WAIT = 4;

    public const DEFAULT_TIMEOUT = 60;

    public const DEFAULT_WAIT = 2000;

    /** @var AntiCaptchaConnector */
    private $connector;

    /** @var TimerInterface */
    private $timer;

    public function __construct(
        AntiCaptchaConnector $connector,
        TimerInterface $timer
    ) {
        $this->connector = $connector;
        $this->timer = $timer;
    }

    public function getConnector(): AntiCaptchaConnector
    {
        return $this->connector;
    }

    public function getTimer(): TimerInterface
    {
        return $this->timer;
    }

    /**
     * Factory method with defaults
     *
     * @param string $clientKey
     * @param int $initialWaitSeconds
     * @param int $timeoutSeconds
     * @param int $waitMilliseconds
     * @return self
     * @throws UndiscoverableClientException
     */
    public static function create(
        string $clientKey,
        int $initialWaitSeconds = self::DEFAULT_INITIAL_WAIT,
        int $timeoutSeconds = self::DEFAULT_TIMEOUT,
        int $waitMilliseconds = self::DEFAULT_WAIT
    ): self {
        return new self(
            new AntiCaptchaConnector($clientKey),
            new Timer($initialWaitSeconds, $timeoutSeconds, $waitMilliseconds)
        );
    }

    public function resolve(CaptchaImageInterface $image): CaptchaAnswerInterface
    {
        try {
            return $this->resolveImage($image);
        } catch (Throwable $exception) {
            throw new UnableToResolveCaptchaException($this, $image, $exception);
        }
    }

    /** @throws RuntimeException */
    private function resolveImage(CaptchaImageInterface $image): CaptchaAnswerInterface
    {
        $taskId = $this->connector->createTask($image);
        $this->timer->start();
        do {
            $result = $this->connector->getTaskResult($taskId);
            if ('' !== $result) {
                return new CaptchaAnswer($result);
            }
            $this->timer->wait();
        } while (! $this->timer->isExpired());

        throw new RuntimeException("Unable to resolve captcha after {$this->timer->getTimeoutSeconds()} seconds");
    }
}
