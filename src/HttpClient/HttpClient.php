<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\HttpClient;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;
use Throwable;

final class HttpClient implements HttpClientInterface
{
    /** @var ClientInterface */
    private $client;

    /** @var RequestFactoryInterface */
    private $requestFactory;

    /** @var StreamFactoryInterface */
    private $streamFactory;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * Create the object with discovered PSR http client, PSR request factory and PSR stream factory
     *
     * @throws UndiscoverableClientException
     */
    public static function discover(): self
    {
        try {
            return new self(
                Psr18ClientDiscovery::find(),
                Psr17FactoryDiscovery::findRequestFactory(),
                Psr17FactoryDiscovery::findStreamFactory()
            );
            // @codeCoverageIgnoreStart
        } catch (Throwable $exception) {
            throw new UndiscoverableClientException($exception);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array<string, string|string[]> $headers
     * @return RequestInterface
     */
    public function createRequest(string $method, string $uri, array $headers): RequestInterface
    {
        $request = $this->requestFactory->createRequest($method, $uri);

        foreach ($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        return $request;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array<string, string|string[]> $headers
     * @return RequestInterface
     */
    public function createJsonRequest(string $method, string $uri, array $headers): RequestInterface
    {
        $jsonHeaders = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
        return $this->createRequest($method, $uri, array_merge($jsonHeaders, $headers));
    }

    public function postJson(string $uri, array $headers = [], $data = null): ResponseInterface
    {
        $request = $this->createJsonRequest('POST', $uri, $headers);

        $request = $request->withBody(
            $this->streamFactory->createStream(json_encode($data) ?: '')
        );

        return $this->send($request);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws HttpException
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw new HttpException($request, null, $exception);
        }

        if ($response->getStatusCode() >= 400) {
            throw new HttpException($request, $response);
        }

        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->client->sendRequest($request);
        } catch (Throwable $exception) {
            if (! $exception instanceof ClientExceptionInterface) {
                $exception = $this->convertThrowableToClientExceptionInterface($exception);
            }
            /** @var ClientExceptionInterface $exception */
            throw $exception;
        }
    }

    private function convertThrowableToClientExceptionInterface(Throwable $exception): ClientExceptionInterface
    {
        return new class ($exception) extends RuntimeException implements ClientExceptionInterface {
            public function __construct(Throwable $previous)
            {
                /**
                 * @see https://github.com/phpstan/phpstan-src/pull/767
                 * @var int|string $code
                 */
                $code = $previous->getCode();
                parent::__construct($previous->getMessage(), (int) $code, $previous);
            }
        };
    }
}
