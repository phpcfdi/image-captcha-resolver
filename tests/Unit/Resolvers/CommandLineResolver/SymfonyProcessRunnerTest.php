<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit\Resolvers\CommandLineResolver;

use PhpCfdi\ImageCaptchaResolver\Resolvers\CommandLineResolver\SymfonyProcessRunner;
use PhpCfdi\ImageCaptchaResolver\Tests\TestCase;
use RuntimeException;

class SymfonyProcessRunnerTest extends TestCase
{
    public function testConstruct(): void
    {
        $runner = new SymfonyProcessRunner();
        $this->assertEqualsWithDelta(SymfonyProcessRunner::DEFAULT_TIMEOUT, $runner->getTimeoutSeconds(), 0.1);
    }

    public function testExpectedRunOk(): void
    {
        $runner = new SymfonyProcessRunner();
        $result = $runner->run('echo', 'ok');
        $this->assertSame(0, $result->getExitCode());
        $this->assertSame(['ok'], $result->getOutput());
    }

    public function testExpectedRunExitError(): void
    {
        $runner = new SymfonyProcessRunner();
        $result = $runner->run('false');
        $this->assertSame(1, $result->getExitCode());
        $this->assertSame([], $result->getOutput());
    }

    public function testExpectedRunCommandNotFound(): void
    {
        $runner = new SymfonyProcessRunner();
        $result = $runner->run(__DIR__ . '/non-existent-command');
        $this->assertNotEquals(0, $result->getExitCode());
    }

    public function testExpectedTimeout(): void
    {
        $timeout = 0.005;
        $runner = new SymfonyProcessRunner($timeout);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Process timeout after $timeout seconds");
        $runner->run('sleep', '1');
    }
}
