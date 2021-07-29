<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Resolvers\CommandLineResolver;

use PhpCfdi\ImageCaptchaResolver\CaptchaAnswer;
use PhpCfdi\ImageCaptchaResolver\CaptchaAnswerInterface;

final class LastLineAnswerBuilder implements AnswerBuilderInterface
{
    public function createAnswerFromProcessResult(ProcessResult $processResult): CaptchaAnswerInterface
    {
        return new CaptchaAnswer($processResult->getLastLine());
    }
}
