<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Resolvers;

use Countable;
use PhpCfdi\ImageCaptchaResolver\CaptchaAnswerInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaImageInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptchaException;
use Throwable;

final class MultiResolver implements CaptchaResolverInterface, Countable
{
    /** @var list<CaptchaResolverInterface> */
    private readonly array $resolvers;

    private readonly int $resolversCount;

    /** @var list<Throwable|CaptchaAnswerInterface> */
    private array $lastResults = [];

    public function __construct(CaptchaResolverInterface ...$resolvers)
    {
        $this->resolvers = array_values($resolvers);
        $this->resolversCount = count($this->resolvers);
    }

    /** @return list<CaptchaResolverInterface> */
    public function getResolvers(): array
    {
        return $this->resolvers;
    }

    public function resolve(CaptchaImageInterface $image): CaptchaAnswerInterface
    {
        $this->clearResults();

        foreach ($this->resolvers as $resolver) {
            try {
                $answer = $resolver->resolve($image);
                $this->lastResults[] = $answer;
                return $answer;
            } catch (Throwable $exception) {
                $this->lastResults[] = $exception;
                continue;
            }
        }

        throw new UnableToResolveCaptchaException($this, $image);
    }

    public function count(): int
    {
        return $this->resolversCount;
    }

    public function clearResults(): void
    {
        $this->lastResults = [];
    }

    /** @return list<Throwable|CaptchaAnswerInterface> */
    public function getLastResults(): array
    {
        return $this->lastResults;
    }
}
