<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver;

use JsonSerializable;
use Stringable;

interface CaptchaAnswerInterface extends Stringable, JsonSerializable
{
    public function getValue(): string;

    /**
     * Compare the current value to another
     *
     * @param Stringable|scalar $value hould be a string or string compatible
     * @noinspection PhpMissingParamTypeInspection
     */
    public function equalsTo($value): bool;
}
