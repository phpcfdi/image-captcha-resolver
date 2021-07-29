<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver;

use RuntimeException;
use Throwable;

class UnableToResolveCaptchaException extends RuntimeException
{
    /** @var CaptchaResolverInterface */
    private $resolver;

    /** @var CaptchaImageInterface */
    private $image;

    public function __construct(
        CaptchaResolverInterface $resolver,
        CaptchaImageInterface $image,
        Throwable $previous = null
    ) {
        parent::__construct('Unable to resolve captcha image', 0, $previous);
        $this->resolver = $resolver;
        $this->image = $image;
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
