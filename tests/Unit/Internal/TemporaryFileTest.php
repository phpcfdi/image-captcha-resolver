<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit\Internal;

use PhpCfdi\ImageCaptchaResolver\Internal\TemporaryFile;
use PhpCfdi\ImageCaptchaResolver\Tests\TestCase;

final class TemporaryFileTest extends TestCase
{
    public function testCreateTemporaryFile(): void
    {
        $contents = 'foo';

        $temporaryFile = new TemporaryFile();
        $temporaryFilename = $temporaryFile->getPath();

        $this->assertFileExists($temporaryFilename);

        $temporaryFile->putContents($contents);

        $this->assertSame($contents, $temporaryFile->getContents());
        $this->assertStringEqualsFile($temporaryFilename, $contents);

        unset($temporaryFile);
        $this->assertFileDoesNotExist($temporaryFilename);
    }
}
