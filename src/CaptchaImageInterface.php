<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver;

use JsonSerializable;
use Stringable;

interface CaptchaImageInterface extends Stringable, JsonSerializable
{
    public function asBinary(): string;

    public function asBase64(): string;

    public function asInlineHtml(): string;

    public function getMimeType(): string;
}
