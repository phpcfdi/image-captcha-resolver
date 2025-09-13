<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver;

interface CaptchaResolverInterface
{
    /**
     * Perform the required operations to resolve the captcha
     * It must never return an empty string
     *
     * @throws UnableToResolveCaptchaException
     */
    public function resolve(CaptchaImageInterface $image): CaptchaAnswerInterface;
}
