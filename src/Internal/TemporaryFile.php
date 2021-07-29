<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Internal;

use RuntimeException;

/**
 * Class to create a temporary file and remove it on object destruction
 * @internal
 */
final class TemporaryFile
{
    /** @var string */
    private $path;

    public function __construct(string $prefix = '', string $directory = '')
    {
        $tempnam = tempnam($directory, $prefix);
        if (false === $tempnam) {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new RuntimeException('Unable to create a temporary file'); // @codeCoverageIgnore
        }
        $this->path = $tempnam;
    }

    public function __destruct()
    {
        if (file_exists($this->path)) {
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            @unlink($this->path);
        }
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getContents(): string
    {
        return file_get_contents($this->path) ?: '';
    }

    public function putContents(string $data): void
    {
        file_put_contents($this->path, $data);
    }
}
