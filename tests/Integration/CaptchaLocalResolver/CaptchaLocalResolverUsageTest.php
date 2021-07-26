<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Integration\CaptchaLocalResolver;

use PhpCfdi\ImageCaptchaResolver\CaptchaImage;
use PhpCfdi\ImageCaptchaResolver\Resolvers\CaptchaLocalResolver;
use PhpCfdi\ImageCaptchaResolver\Tests\TestCase;
use RuntimeException;

final class CaptchaLocalResolverUsageTest extends TestCase
{
    /** @var string */
    private $localResolverUrl;

    protected function setUp(): void
    {
        parent::setUp();

        if ('yes' !== $this->getenv('CAPTCHA_LOCAL_RESOLVER_ENABLED') ?? '') {
            $this->markTestSkipped('Captcha local resolver tests are not enabled');
        }

        $localResolverUrl = $this->getenv('CAPTCHA_LOCAL_RESOLVER_BASEURL') ?? '';
        if ('' === $localResolverUrl) {
            $this->fail('Environment CAPTCHA_LOCAL_RESOLVER_BASEURL is not set');
        }

        $host = strval(parse_url($localResolverUrl, PHP_URL_HOST));
        $port = intval(parse_url($localResolverUrl, PHP_URL_PORT) ?: 80);
        try {
            $this->checkPortIsOpen($host, $port);
        } catch (RuntimeException $exception) {
            $this->markTestSkipped("Captcha local resolver service is not open at $host:$port");
        }

        $this->localResolverUrl = $localResolverUrl;
    }

    public function testCaptchaLocalResolverUsage(): void
    {
        $expectedAnswer = 'qwerty';
        $image = CaptchaImage::newFromFile($this->filePath('captcha-qwerty.png'));
        $resolver = CaptchaLocalResolver::create($this->localResolverUrl);

        $answer = $resolver->resolve($image);

        $this->assertSame($expectedAnswer, $answer, 'Did you answer "qwerty" on the captcha local resolver?');
    }
}
