<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Integration\AntiCaptchaResolver;

use PhpCfdi\ImageCaptchaResolver\CaptchaImage;
use PhpCfdi\ImageCaptchaResolver\Resolvers\AntiCaptchaResolver;
use PhpCfdi\ImageCaptchaResolver\Tests\TestCase;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptchaException;

final class AntiCaptchaResolverUsageTest extends TestCase
{
    public function checkTestIsEnabled(): void
    {
        if ('yes' !== $this->getenv('ANTI_CAPTCHA_ENABLED') ?? '') {
            $this->markTestSkipped('Anti-captcha resolver tests are not enabled');
        }
    }

    public function obtainClientKeyFromEnvironment(): string
    {
        $clientKey = $this->getenv('ANTI_CAPTCHA_CLIENT_KEY');
        if ('' === $clientKey) {
            $this->fail('Environment ANTI_CAPTCHA_CLIENT_KEY is not set');
        }
        return $clientKey;
    }

    public function testResolver(): void
    {
        $expectedAnswerText = 'qwerty';
        $image = CaptchaImage::newFromFile($this->filePath('captcha-qwerty.png'));
        $this->checkTestIsEnabled();
        $clientKey = $this->obtainClientKeyFromEnvironment();
        $resolver = AntiCaptchaResolver::create($clientKey, 2, 30, 1000);

        $answer = $resolver->resolve($image);
        $this->assertSame($expectedAnswerText, $answer->getValue());
    }

    public function testResolverWithInvalidClientKey(): void
    {
        $clientKey = '00000000000000000000000000000000';
        $resolver = AntiCaptchaResolver::create($clientKey, 2, 30, 1000);
        $image = CaptchaImage::newFromFile($this->filePath('captcha-qwerty.png'));

        $this->expectException(UnableToResolveCaptchaException::class);
        $resolver->resolve($image);
    }
}
