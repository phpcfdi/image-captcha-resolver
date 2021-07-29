<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit\Resolvers\CommandLineResolver;

use PhpCfdi\ImageCaptchaResolver\Resolvers\CommandLineResolver\ProcessResult;
use PhpCfdi\ImageCaptchaResolver\Tests\TestCase;

class ProcessResultTest extends TestCase
{
    public function testProperties(): void
    {
        $exitCode = 1;
        $lastLine = 'bar';
        $output = ['foo', $lastLine];

        $result = new ProcessResult($exitCode, $output);

        $this->assertSame($exitCode, $result->getExitCode());
        $this->assertSame($output, $result->getOutput());
        $this->assertSame($lastLine, $result->getLastLine());
    }

    public function testIsSucessTrue(): void
    {
        $result = new ProcessResult(0, ['qwerty']);
        $this->assertTrue($result->isSuccessful());
    }

    public function testIsNotSucessExitCode(): void
    {
        $result = new ProcessResult(1, ['qwerty']);
        $this->assertFalse($result->isSuccessful());
    }

    public function testIsNotSucessOutput(): void
    {
        $result = new ProcessResult(0, []);
        $this->assertFalse($result->isSuccessful());
    }
}
