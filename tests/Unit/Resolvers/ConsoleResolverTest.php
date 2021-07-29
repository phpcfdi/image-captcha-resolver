<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit\Resolvers;

use PhpCfdi\ImageCaptchaResolver\CaptchaImage;
use PhpCfdi\ImageCaptchaResolver\Internal\TemporaryFile;
use PhpCfdi\ImageCaptchaResolver\Resolvers\ConsoleResolver;
use PhpCfdi\ImageCaptchaResolver\Tests\TestCase;
use PhpCfdi\ImageCaptchaResolver\Tests\Unit\ConsoleResolverWithInput;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptchaException;

final class ConsoleResolverTest extends TestCase
{
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

        $this->expectException(UnableToResolveCaptchaException::class);
        $this->expectOutputRegex('/Resolve the captcha stored on file .+:/');
        $resolver->resolve($image);
    }

    public function testProcessWithAnswerNotGivenAfterWait(): void
    {
        $tempfile = new TemporaryFile('console-');
        $image = CaptchaImage::newFromFile($this->filePath('captcha-qwerty.png'));
        $resolver = new ConsoleResolver($tempfile->getPath(), 0.1);

        $this->expectException(UnableToResolveCaptchaException::class);
        $this->expectOutputRegex('/Resolve the captcha stored on file .+:/');
        $resolver->resolve($image);
    }
}
