<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Resolvers;

use Countable;
use OutOfRangeException;
use PhpCfdi\ImageCaptchaResolver\CaptchaAnswerInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaImageInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptcha;

final class MockResolver implements CaptchaResolverInterface, Countable
{
    /** @var CaptchaAnswerInterface[]|UnableToResolveCaptcha[] */
    private $resolveResponses;

    /**
     * MockResolver constructor.
     *
     * @param CaptchaAnswerInterface|UnableToResolveCaptcha ...$resolveResponses
     */
    public function __construct(...$resolveResponses)
    {
        $this->resolveResponses = $resolveResponses;
    }

    public function resolve(CaptchaImageInterface $image): CaptchaAnswerInterface
    {
        $response = array_shift($this->resolveResponses);
        if (null === $response) {
            throw new OutOfRangeException('MockResolver does not have any response to process');
        }

        if ($response instanceof UnableToResolveCaptcha) {
            throw $response;
        }

        return $response;
    }

    public function count(): int
    {
        return count($this->resolveResponses);
    }

    public function isEmpty(): bool
    {
        return ([] === $this->resolveResponses);
    }
}
