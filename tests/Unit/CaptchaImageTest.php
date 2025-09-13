<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit;

use InvalidArgumentException;
use PhpCfdi\ImageCaptchaResolver\CaptchaImage;
use PhpCfdi\ImageCaptchaResolver\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class CaptchaImageTest extends TestCase
{
    public function testObjectProperties(): void
    {
        $basename = 'red-pixel.gif';
        $content = $this->fileContents($basename);
        $filename = $this->filePath($basename);
        $contentBase64 = base64_encode($content);
        $inlineImage = 'data:image/gif;base64,' . $contentBase64;

        $image = new CaptchaImage($contentBase64);

        $this->assertStringEqualsFile($filename, $image->asBinary());
        $this->assertSame($contentBase64, $image->asBase64());
        $this->assertSame($inlineImage, $image->asInlineHtml());
        $this->assertSame('image/gif', $image->getMimeType());
        $this->assertSame($contentBase64, (string) $image);
        $this->assertSame($contentBase64, $image->__toString());
        $this->assertSame($contentBase64, $image->jsonSerialize());
        $this->assertJsonStringEqualsJsonString(
            json_encode($contentBase64) ?: '',
            json_encode($image, JSON_THROW_ON_ERROR),
        );
    }

    public function testNewFromInlineImage(): void
    {
        $basename = 'red-pixel.gif';
        $content = $this->fileContents($basename);
        $contentBase64 = base64_encode($content);
        $inlineImage = 'data:image/gif;base64,' . chunk_split($contentBase64, 16);

        $image = CaptchaImage::newFromInlineHtml($inlineImage);
        $this->assertSame($contentBase64, $image->asBase64());
    }

    public function testNewFromFile(): void
    {
        $basename = 'red-pixel.gif';
        $content = $this->fileContents($basename);
        $filename = $this->filePath($basename);

        $image = CaptchaImage::newFromFile($filename);

        $expectedBase64 = base64_encode($content);
        $this->assertSame($expectedBase64, $image->asBase64());
    }

    /** @return array<string, array{string, string}> */
    public static function providerNewFromBase64Malformed(): array
    {
        return [
            'empty' => ['', 'The captcha image is empty'],
            'non base64' => ['$', 'The captcha image is not base64 encoded'],
            'non image' => [base64_encode('$'), 'The captcha image is not an image'],
        ];
    }

    #[DataProvider('providerNewFromBase64Malformed')]
    public function testNewFromBase64Malformed(string $contents, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        CaptchaImage::newFromBase64($contents);
    }

    /** @return array<string, array{string}> */
    public static function providerNewFromInlineImageMalformed(): array
    {
        $image = CaptchaImage::newFromFile(self::filePath('red-pixel.gif'));
        return [
            'empty' => [''],
            'no data part' => ["{$image->getMimeType()};base64,{$image->asBase64()}"],
            'no type part' => ["data:;base64,{$image->asBase64()}"],
            'no base64 part' => ["data:{$image->getMimeType()};{$image->asBase64()}"],
            'no content part' => ["data:{$image->getMimeType()};base64,"],
            'only base64' => ["data:{$image->asBase64()}"],
        ];
    }

    #[DataProvider('providerNewFromInlineImageMalformed')]
    public function testNewFromInlineImageMalformed(string $inlineImage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Image is not an embeded base64 image');
        CaptchaImage::newFromInlineHtml($inlineImage);
    }
}
