<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Resolvers;

use PhpCfdi\ImageCaptchaResolver\CaptchaAnswerInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaImageInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use PhpCfdi\ImageCaptchaResolver\HttpClient\UndiscoverableClientException;
use PhpCfdi\ImageCaptchaResolver\Resolvers\CaptchaLocalResolver\CaptchaLocalResolverConnector;
use PhpCfdi\ImageCaptchaResolver\Timer\Timer;
use PhpCfdi\ImageCaptchaResolver\Timer\TimerInterface;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptchaException;
use Throwable;

class CaptchaLocalResolver implements CaptchaResolverInterface
{
    public const DEFAULT_INITIAL_WAIT = 5;

    public const DEFAULT_TIMEOUT = 90;

    public const DEFAULT_WAIT = 500;

    /** @var CaptchaLocalResolverConnector */
    private $connector;

    /** @var TimerInterface */
    private $timer;

    public function __construct(
        CaptchaLocalResolverConnector $connector,
        TimerInterface $timer
    ) {
        $this->connector = $connector;
        $this->timer = $timer;
    }

    /**
     * @param string $baseUrl
     * @param int $initialWaitSeconds
     * @param int $timeoutSeconds
     * @param int $sleepMilliseconds
     * @return self
     * @throws UndiscoverableClientException
     */
    public static function create(
        string $baseUrl,
        int $initialWaitSeconds = self::DEFAULT_INITIAL_WAIT,
        int $timeoutSeconds = self::DEFAULT_TIMEOUT,
        int $sleepMilliseconds = self::DEFAULT_WAIT
    ): self {
        return new self(
            new CaptchaLocalResolverConnector($baseUrl),
            new Timer($initialWaitSeconds, $timeoutSeconds, $sleepMilliseconds)
        );
    }

    public function getConnector(): CaptchaLocalResolverConnector
    {
        return $this->connector;
    }

    public function getTimer(): TimerInterface
    {
        return $this->timer;
    }

    public function resolve(CaptchaImageInterface $image): CaptchaAnswerInterface
    {
        try {
            return $this->connector->resolveImage($image, $this->timer);
        } catch (Throwable $exception) {
            throw new UnableToResolveCaptchaException($this, $image, $exception);
        }
    }
}
