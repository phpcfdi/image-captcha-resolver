<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Mock\Client as PhpHttpMockClient;
use PhpCfdi\ImageCaptchaResolver\HttpClient\HttpClient;
use PhpCfdi\ImageCaptchaResolver\HttpClient\HttpClientInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

abstract class HttpTestCase extends TestCase
{
    private StreamFactoryInterface $streamFactory;

    private RequestFactoryInterface $requestFactory;

    private ResponseFactoryInterface $responseFactory;

    /** @noinspection PhpUnhandledExceptionInspection */
    protected function setUp(): void
    {
        parent::setUp();

        $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();
        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $this->responseFactory = Psr17FactoryDiscovery::findResponseFactory();
    }

    protected function createPhpHttpMockClient(): PhpHttpMockClient
    {
        return new PhpHttpMockClient($this->getResponseFactory());
    }

    protected function createHttpClient(ClientInterface $client): HttpClientInterface
    {
        return new HttpClient($client, $this->requestFactory, $this->streamFactory);
    }

    /**
     * @param array<string, string|string[]> $data
     */
    protected function createJsonRespose(array $data): ResponseInterface
    {
        $responseFactory = $this->getResponseFactory();
        $streamFactory = $this->getStreamFactory();
        return $responseFactory->createResponse()
            ->withHeader('Content-Type', 'application/json')
            ->withBody(
                $streamFactory->createStream(json_encode($data) ?: ''),
            );
    }

    public function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    public function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }
}
