<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Extending;

use PHPUnit\Framework\Constraint\Constraint;
use Throwable;

class HasPreviousException extends Constraint
{
    private Throwable $exception;

    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
    }

    public function toString(): string
    {
        return sprintf(
            ' is part of previous exception chain of %s &%s',
            get_class($this->exception),
            spl_object_hash($this->exception),
        );
    }

    protected function failureDescription($other): string
    {
        return sprintf(
            '%s &%s has previous exception %s &%s',
            get_class($this->exception),
            spl_object_hash($this->exception),
            (is_object($other)) ? get_class($other) : gettype($other),
            (is_object($other)) ? spl_object_hash($other) : '',
        );
    }

    /**
     * @param Throwable $other
     * @return bool
     */
    protected function matches($other): bool
    {
        $current = $this->exception;
        while (null !== $current = $current->getPrevious()) {
            if ($other === $current) {
                return true;
            }
        }
        return false;
    }
}
