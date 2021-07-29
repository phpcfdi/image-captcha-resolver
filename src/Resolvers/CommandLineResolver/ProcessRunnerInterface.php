<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Resolvers\CommandLineResolver;

use RuntimeException;

interface ProcessRunnerInterface
{
    /**
     * @param string ...$command
     * @return ProcessResult
     * @throws RuntimeException on any kind of execution error
     */
    public function run(string ...$command): ProcessResult;
}
