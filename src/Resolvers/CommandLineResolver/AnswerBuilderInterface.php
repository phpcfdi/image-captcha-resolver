<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Resolvers\CommandLineResolver;

use PhpCfdi\ImageCaptchaResolver\CaptchaAnswerInterface;

interface AnswerBuilderInterface
{
    public function createAnswerFromProcessResult(ProcessResult $processResult): CaptchaAnswerInterface;
}
