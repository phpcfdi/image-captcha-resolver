<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\HttpClient;

use LogicException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

class HttpException extends RuntimeException
{
    /** @var RequestInterface */
    private $request;

    /** @var ResponseInterface|null */
    private $response;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response = null,
        Throwable $previous = null
    ) {
        parent::__construct("Error on {$request->getMethod()} {$request->getUri()}", 0, $previous);
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function hasResponse(): bool
    {
        return (null !== $this->response);
    }

    public function getResponse(): ResponseInterface
    {
        if (null === $this->response) {
            throw new LogicException('The exception does not have a response');
        }
        return $this->response;
    }
}
