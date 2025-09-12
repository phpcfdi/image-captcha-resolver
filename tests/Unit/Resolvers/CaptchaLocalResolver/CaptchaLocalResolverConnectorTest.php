<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit\Resolvers\CaptchaLocalResolver;

use Exception;
use PhpCfdi\ImageCaptchaResolver\CaptchaImage;
use PhpCfdi\ImageCaptchaResolver\HttpClient\HttpClientInterface;
use PhpCfdi\ImageCaptchaResolver\Resolvers\CaptchaLocalResolver\CaptchaLocalResolverConnector;
use PhpCfdi\ImageCaptchaResolver\Tests\Extending\AssertHasPreviousExceptionTrait;
use PhpCfdi\ImageCaptchaResolver\Tests\HttpTestCase;
use PhpCfdi\ImageCaptchaResolver\Tests\Unit\FakeExpiredTimer;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

final class CaptchaLocalResolverConnectorTest extends HttpTestCase
{
    use AssertHasPreviousExceptionTrait;

    /** @var string */
    private $baseUrl = 'http://localhost:9095';

    public function createConnectorWithMockClient(HttpClientInterface $client): CaptchaLocalResolverConnector
    {
        return new CaptchaLocalResolverConnector($this->baseUrl, $client);
    }

    public function testConstructor(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $connector = $this->createConnectorWithMockClient($httpClient);

        $this->assertSame($this->baseUrl, $connector->getBaseUrl());
        $this->assertSame($httpClient, $connector->getHttpClient());
    }

    public function testConstructorWithoutHttpClient(): void
    {
        $connector = new CaptchaLocalResolverConnector($this->baseUrl);

        $this->assertSame($this->baseUrl, $connector->getBaseUrl());
        $this->assertInstanceOf(HttpClientInterface::class, $connector->getHttpClient());
    }

    public function testSendImage(): void
    {
        $serviceCode = 'd41d8cd98f00b204e9800998ecf8427e';
        $image = CaptchaImage::newFromFile($this->filePath('captcha-qwerty.png'));
        $phpHttpMockClient = $this->createPhpHttpMockClient();
        $httpClient = $this->createHttpClient($phpHttpMockClient);
        $connector = $this->createConnectorWithMockClient($httpClient);

        $phpHttpMockClient->addResponse($this->createJsonRespose(['code' => $serviceCode]));

        $code = $connector->sendImage($image);

        /** @var RequestInterface $lastRequest */
        $lastRequest = $phpHttpMockClient->getLastRequest();
        $this->assertSame($serviceCode, $code);
        $this->assertJsonStringEqualsJsonString(
            json_encode(['image' => $image->asBase64()]) ?: '',
            (string) $lastRequest->getBody(),
        );
    }

    public function testSendImageWithEmptyCode(): void
    {
        $image = CaptchaImage::newFromFile($this->filePath('captcha-qwerty.png'));
        $phpHttpMockClient = $this->createPhpHttpMockClient();
        $httpClient = $this->createHttpClient($phpHttpMockClient);
        $connector = $this->createConnectorWithMockClient($httpClient);

        $phpHttpMockClient->addResponse($this->createJsonRespose(['@' => '']));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Image was sent but service returns empty code');
        $connector->sendImage($image);
    }

    public function testSendImageWithError(): void
    {
        $image = CaptchaImage::newFromFile($this->filePath('captcha-qwerty.png'));
        $phpHttpMockClient = $this->createPhpHttpMockClient();
        $httpClient = $this->createHttpClient($phpHttpMockClient);
        $connector = $this->createConnectorWithMockClient($httpClient);

        $phpHttpMockClient->addResponse($this->createJsonRespose([])->withStatus(400));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to send image to $this->baseUrl/send-image");
        $connector->sendImage($image);
    }

    public function testCheckCodeWithAnswer(): void
    {
        $serviceCode = 'd41d8cd98f00b204e9800998ecf8427e';
        $serviceAnswer = 'qwerty';
        $phpHttpMockClient = $this->createPhpHttpMockClient();
        $httpClient = $this->createHttpClient($phpHttpMockClient);
        $connector = $this->createConnectorWithMockClient($httpClient);

        $phpHttpMockClient->addResponse($this->createJsonRespose(['answer' => $serviceAnswer]));

        $answer = $connector->checkCode($serviceCode);

        /** @var RequestInterface $lastRequest */
        $lastRequest = $phpHttpMockClient->getLastRequest();
        $this->assertSame($serviceAnswer, $answer);
        $this->assertJsonStringEqualsJsonString(
            json_encode(['code' => $serviceCode]) ?: '',
            (string) $lastRequest->getBody(),
        );
    }

    public function testCheckCodeWithoutAnswer(): void
    {
        $serviceCode = 'd41d8cd98f00b204e9800998ecf8427e';
        $serviceAnswer = '';
        $phpHttpMockClient = $this->createPhpHttpMockClient();
        $httpClient = $this->createHttpClient($phpHttpMockClient);
        $connector = $this->createConnectorWithMockClient($httpClient);

        $phpHttpMockClient->addResponse($this->createJsonRespose([]));

        $answer = $connector->checkCode($serviceCode);

        /** @var RequestInterface $lastRequest */
        $lastRequest = $phpHttpMockClient->getLastRequest();
        $this->assertSame($serviceAnswer, $answer);
        $this->assertJsonStringEqualsJsonString(
            json_encode(['code' => $serviceCode]) ?: '',
            (string) $lastRequest->getBody(),
        );
    }

    public function testCheckCodeWithCodeNotFound(): void
    {
        $serviceCode = 'd41d8cd98f00b204e9800998ecf8427e';
        $phpHttpMockClient = $this->createPhpHttpMockClient();
        $httpClient = $this->createHttpClient($phpHttpMockClient);
        $connector = $this->createConnectorWithMockClient($httpClient);

        $phpHttpMockClient->addResponse($this->createJsonRespose([])->withStatus(404));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to retrieve answer for non-existent code $serviceCode");
        $connector->checkCode($serviceCode);
    }

    public function testCheckCodeWithHttpError(): void
    {
        $serviceCode = 'd41d8cd98f00b204e9800998ecf8427e';
        $phpHttpMockClient = $this->createPhpHttpMockClient();
        $httpClient = $this->createHttpClient($phpHttpMockClient);
        $connector = $this->createConnectorWithMockClient($httpClient);

        $phpHttpMockClient->addResponse($this->createJsonRespose([])->withStatus(400));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to check code for $this->baseUrl/obtain-decoded");
        $connector->checkCode($serviceCode);
    }

    public function testCheckCodeWithException(): void
    {
        $serviceCode = 'd41d8cd98f00b204e9800998ecf8427e';
        $phpHttpMockClient = $this->createPhpHttpMockClient();
        $httpClient = $this->createHttpClient($phpHttpMockClient);
        $connector = $this->createConnectorWithMockClient($httpClient);

        $previousException = new Exception('Communication exception');
        $phpHttpMockClient->addException($previousException);

        $catchedException = null;
        try {
            $connector->checkCode($serviceCode);
        } catch (RuntimeException $exception) {
            $catchedException = $exception;
        }

        if (null === $catchedException) {
            $this->fail("Expected exception wasn't thrown");
        }
        $this->assertSame("Unable to check code for $this->baseUrl/obtain-decoded", $catchedException->getMessage());
        $this->assertHasPreviousException($previousException, $catchedException);
    }

    public function testResolveImage(): void
    {
        $serviceCode = 'd41d8cd98f00b204e9800998ecf8427e';
        $serviceAnswer = 'qwerty';
        $image = CaptchaImage::newFromFile($this->filePath('captcha-qwerty.png'));
        $phpHttpMockClient = $this->createPhpHttpMockClient();
        $httpClient = $this->createHttpClient($phpHttpMockClient);
        $connector = $this->createConnectorWithMockClient($httpClient);

        $phpHttpMockClient->addResponse($this->createJsonRespose(['code' => $serviceCode]));
        $phpHttpMockClient->addResponse($this->createJsonRespose(['answer' => $serviceAnswer]));

        $answer = $connector->resolveImage($image, new FakeExpiredTimer());

        $this->assertTrue($answer->equalsTo($serviceAnswer), 'Service did not return expected answer');
    }

    public function testResolveImageWithoutAnswer(): void
    {
        $serviceCode = 'd41d8cd98f00b204e9800998ecf8427e';
        $image = CaptchaImage::newFromFile($this->filePath('captcha-qwerty.png'));
        $phpHttpMockClient = $this->createPhpHttpMockClient();
        $httpClient = $this->createHttpClient($phpHttpMockClient);
        $connector = $this->createConnectorWithMockClient($httpClient);

        $phpHttpMockClient->addResponse($this->createJsonRespose(['code' => $serviceCode]));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to get an answer after 0 seconds');
        $connector->resolveImage($image, new FakeExpiredTimer(1));
    }
}
