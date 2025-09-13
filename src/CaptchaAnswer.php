<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver;

use JsonSerializable;
use LogicException;
use Throwable;

final readonly class CaptchaAnswer implements JsonSerializable, CaptchaAnswerInterface
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if ('' === $value) {
            throw new LogicException('Captcha answer is empty');
        }
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equalsTo($value): bool
    {
        try {
            return $this->value === strval($value);
        } catch (Throwable) {
            return false;
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
