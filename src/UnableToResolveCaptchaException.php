<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver;

use RuntimeException;
use Throwable;

class UnableToResolveCaptchaException extends RuntimeException
{
    public function __construct(
        private readonly CaptchaResolverInterface $resolver,
        private readonly CaptchaImageInterface $image,
        ?Throwable $previous = null,
    ) {
        parent::__construct('Unable to resolve captcha image', 0, $previous);
    }

    public function getResolver(): CaptchaResolverInterface
    {
        return $this->resolver;
    }

    public function getImage(): CaptchaImageInterface
    {
        return $this->image;
    }
}
