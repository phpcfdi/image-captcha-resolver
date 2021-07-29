<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\HttpClient;

use RuntimeException;
use Throwable;

class UndiscoverableClientException extends RuntimeException
{
    public function __construct(Throwable $previous)
    {
        parent::__construct('Cannot discover the HttpClient', 0, $previous);
    }
}
