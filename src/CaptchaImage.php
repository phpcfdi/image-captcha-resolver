<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver;

use finfo;
use InvalidArgumentException;

final class CaptchaImage implements CaptchaImageInterface
{
    /** @var string */
    private $contents;

    /** @var string */
    private $mimeType;

    /**
     * CaptchaImage constructor.
     *
     * The contents are evaluated to be a non-empty base64 encoded image.
     *
     * @param string $contents Should be the image in base64
     */
    public function __construct(string $contents)
    {
        if ('' === $contents) {
            throw new InvalidArgumentException('The captcha image is empty');
        }

        $binary = base64_decode($contents, true) ?: '';
        if ($contents !== base64_encode($binary)) {
            throw new InvalidArgumentException('The captcha image is not base64 encoded');
        }

        $mimeType = self::finfo()->buffer($binary, FILEINFO_MIME_TYPE) ?: '';
        if ('image/' !== substr($mimeType, 0, 6)) {
            throw new InvalidArgumentException('The captcha image is not an image');
        }

        $this->contents = $contents;
        $this->mimeType = $mimeType;
    }

    public static function newFromFile(string $filename): self
    {
        return self::newFromBinary(file_get_contents($filename) ?: '');
    }

    public static function newFromBinary(string $contents): self
    {
        return new self(base64_encode($contents));
    }

    public static function newFromBase64(string $contents): self
    {
        return new self($contents);
    }

    public static function newFromInlineHtml(string $contents): self
    {
        if (1 !== preg_match('#^data:image/(?<type>[a-zA-Z]+?);base64,(?<image>[/+=\w\s]+)#', $contents, $parts)) {
            throw new InvalidArgumentException('Image is not an embeded base64 image');
        }

        return self::newFromBase64((string) preg_replace('#\s#', '', $parts['image']));
    }

    private static function finfo(): finfo
    {
        // if finfo is used in other places on the project then move it to a static class
        static $finfo = null;
        if (null === $finfo) {
            $finfo = new finfo(); // @codeCoverageIgnore
        }
        return $finfo;
    }

    public function asBinary(): string
    {
        return base64_decode($this->contents);
    }

    public function asBase64(): string
    {
        return $this->contents;
    }

    public function asInlineHtml(): string
    {
        return implode('', ['data:', $this->getMimeType(), ';base64,', $this->contents]);
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function __toString(): string
    {
        return $this->contents;
    }

    public function jsonSerialize(): string
    {
        return $this->contents;
    }
}
