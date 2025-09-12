<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit;

use LogicException;
use PhpCfdi\ImageCaptchaResolver\CaptchaAnswer;
use PhpCfdi\ImageCaptchaResolver\CaptchaAnswerInterface;
use PhpCfdi\ImageCaptchaResolver\Tests\TestCase;

final class CaptchaAnswerTest extends TestCase
{
    public function testObjectProperties(): void
    {
        $value = 'qwerty';
        $answer = new CaptchaAnswer($value);

        $this->assertInstanceOf(CaptchaAnswerInterface::class, $answer);
        $this->assertSame($value, $answer->getValue());
        $this->assertSame($value, (string) $answer);
        $this->assertSame($value, $answer->__toString());
        $this->assertSame($value, $answer->jsonSerialize());
        $this->assertJsonStringEqualsJsonString(
            json_encode($value) ?: '',
            json_encode($answer, JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @testWith [""]
     *           ["\n"]
     */
    public function testCreateWithEmptyStringThrowsException(string $value): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Captcha answer is empty');
        new CaptchaAnswer($value);
    }

    public function testEqualsTo(): void
    {
        $value = 'qwerty';
        $other = "x{$value}x";
        $answer = new CaptchaAnswer($value);

        $this->assertTrue($answer->equalsTo($value));
        $this->assertTrue($answer->equalsTo($answer));
        $this->assertTrue($answer->equalsTo(new CaptchaAnswer($value)));

        $this->assertFalse($answer->equalsTo($other));
        $this->assertFalse($answer->equalsTo(new CaptchaAnswer($other)));

        /** @phpstan-ignore-next-line send object type other than annotation */
        $this->assertFalse($answer->equalsTo((object) [])); // complete different type
    }
}
