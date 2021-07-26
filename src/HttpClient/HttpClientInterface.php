<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\HttpClient;

use Psr\Http\Message\ResponseInterface;

interface HttpClientInterface
{
    /**
     * @param string $uri
     * @param array<string, string|string[]> $headers
     * @param array|object|mixed $data
     * @return ResponseInterface
     * @throws HttpException
     */
    public function postJson(string $uri, array $headers = [], $data = null): ResponseInterface;
}
