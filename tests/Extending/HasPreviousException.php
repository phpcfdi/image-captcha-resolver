<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Extending;

use PHPUnit\Framework\Constraint\Constraint;
use Throwable;

class HasPreviousException extends Constraint
{
    public function __construct(private readonly Throwable $exception)
    {
    }

    public function toString(): string
    {
        return sprintf(
            ' is part of previous exception chain of %s &%s',
            $this->exception::class,
            spl_object_hash($this->exception),
        );
    }

    protected function failureDescription($other): string
    {
        return sprintf(
            '%s &%s has previous exception %s &%s',
            $this->exception::class,
            spl_object_hash($this->exception),
            get_debug_type($other),
            (is_object($other)) ? spl_object_hash($other) : '',
        );
    }

    /**
     * @param Throwable $other
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
