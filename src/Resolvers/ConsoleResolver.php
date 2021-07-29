<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Resolvers;

use PhpCfdi\ImageCaptchaResolver\CaptchaAnswer;
use PhpCfdi\ImageCaptchaResolver\CaptchaAnswerInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaImageInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptchaException;
use RuntimeException;
use Throwable;

/**
 * Class ConsoleResolver
 *
 * Use this resolver for command line testing, it will store the captcha into a file
 * and ask you to open it and write the answer.
 */
class ConsoleResolver implements CaptchaResolverInterface
{
    public const DEFAULT_WAIT = 120;

    public const MAX_WAIT = 300;

    /** @var string */
    private $captchaOutputFile;

    /** @var float|int */
    private $waitForAnswerSeconds;

    public function __construct(string $captchaOutputFile = '', float $waitForAnswerSeconds = self::DEFAULT_WAIT)
    {
        $this->captchaOutputFile = $captchaOutputFile ?: (getcwd() . '/captcha.png');
        $this->waitForAnswerSeconds = $waitForAnswerSeconds;
    }

    public function resolve(CaptchaImageInterface $image): CaptchaAnswerInterface
    {
        try {
            return $this->realResolveProcess($image);
        } catch (Throwable $exception) {
            throw new UnableToResolveCaptchaException($this, $image, $exception);
        }
    }

    protected function realResolveProcess(CaptchaImageInterface $image): CaptchaAnswerInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->storeCaptchaFile($image->asBinary());
        try {
            echo PHP_EOL, "Resolve the captcha stored on file $this->captchaOutputFile: ";
            /** @noinspection PhpUnhandledExceptionInspection */
            $answer = $this->readLine();
            return new CaptchaAnswer($answer);
        } finally {
            $this->removeCaptchaFile();
        }
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    protected function storeCaptchaFile(string $image): void
    {
        if (false === file_put_contents($this->captchaOutputFile, $image)) {
            throw new RuntimeException("Unable to write captcha on $this->captchaOutputFile"); // @codeCoverageIgnore
        }
    }

    /**
     * @return resource
     * @throws RuntimeException
     */
    protected function openStdInStream()
    {
        $stdin = fopen('php://stdin', 'r');
        if (false === $stdin) {
            throw new RuntimeException('Unable to open STDIN'); // @codeCoverageIgnore
        }
        return $stdin;
    }

    /** @throws RuntimeException */
    protected function readLine(): string
    {
        $this->waitForAnswerSeconds = min($this->waitForAnswerSeconds, self::MAX_WAIT);
        $timeoutSecs = intval(floor($this->waitForAnswerSeconds) ?: 0);
        $timeoutUsecs = intval(1000000 * ($this->waitForAnswerSeconds - $timeoutSecs));

        $read = [$stdin = $this->openStdInStream()];
        $write = [];
        $except = [];

        try {
            if (false === stream_set_blocking($stdin, false)) {
                throw new RuntimeException('Unable to set STDIN as non-blocking'); // @codeCoverageIgnore
            }
            if (false === stream_select($read, $write, $except, $timeoutSecs, $timeoutUsecs)) {
                throw new RuntimeException('Unable to select STDIN with timeout'); // @codeCoverageIgnore
            }
            $line = fgets($stdin);
            if (false === $line) {
                throw new RuntimeException('No answer received');
            }
            return $line;
        } finally {
            fclose($stdin);
        }
    }

    protected function removeCaptchaFile(): void
    {
        if (file_exists($this->captchaOutputFile)) {
            unlink($this->captchaOutputFile);
        }
    }
}
