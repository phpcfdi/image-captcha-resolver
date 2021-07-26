<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit\Resolvers;

use PhpCfdi\ImageCaptchaResolver\CaptchaImage;
use PhpCfdi\ImageCaptchaResolver\Resolvers\ConsoleResolver;
use PhpCfdi\ImageCaptchaResolver\Tests\TestCase;
use PhpCfdi\ImageCaptchaResolver\Tests\Unit\ConsoleResolverWithInput;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptcha;
use RuntimeException;

final class ConsoleResolverTest extends TestCase
{
    /** @var string */
    private $captchaTemporaryFile = '';

    protected function setUp(): void
    {
        parent::setUp();

        $temporaryFile = tempnam('', 'console-');
        if (false === $temporaryFile) {
            throw new RuntimeException('Unable to create a temporary file');
        }
        $this->captchaTemporaryFile = $temporaryFile;
    }

    protected function tearDown(): void
    {
        if ('' !== $this->captchaTemporaryFile && file_exists($this->captchaTemporaryFile)) {
            unlink($this->captchaTemporaryFile);
        }

        parent::tearDown();
    }

    public function testProcessWithAnswer(): void
    {
        $expectedAnswer = 'qwerty';
        $image = CaptchaImage::newFromFile($this->filePath('captcha-qwerty.png'));

        $resolver = (new ConsoleResolverWithInput())->setInput($expectedAnswer);

        $answer = $resolver->resolve($image);

        $this->expectOutputRegex('/Resolve the captcha stored on file .+:/');
        $this->assertSame($expectedAnswer, $answer->getValue());
    }

    public function testProcessWithEmptyAnswer(): void
    {
        $image = CaptchaImage::newFromFile($this->filePath('captcha-qwerty.png'));

        $resolver = (new ConsoleResolverWithInput())->setInput('');

        $this->expectException(UnableToResolveCaptcha::class);
        $this->expectOutputRegex('/Resolve the captcha stored on file .+:/');
        $resolver->resolve($image);
    }

    public function testProcessWithAnswerNotGivenAfterWait(): void
    {
        $image = CaptchaImage::newFromFile($this->filePath('captcha-qwerty.png'));
        $resolver = new ConsoleResolver($this->captchaTemporaryFile, 0.1);

        $this->expectException(UnableToResolveCaptcha::class);
        $this->expectOutputRegex('/Resolve the captcha stored on file .+:/');
        $resolver->resolve($image);
    }
}
