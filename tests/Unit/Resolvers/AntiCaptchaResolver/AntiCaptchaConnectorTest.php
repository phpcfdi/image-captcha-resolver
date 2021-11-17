<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit\Resolvers\AntiCaptchaResolver;

use Exception;
use LogicException;
use PhpCfdi\ImageCaptchaResolver\CaptchaImage;
use PhpCfdi\ImageCaptchaResolver\HttpClient\HttpClientInterface;
use PhpCfdi\ImageCaptchaResolver\Resolvers\AntiCaptchaResolver\AntiCaptchaConnector;
use PhpCfdi\ImageCaptchaResolver\Tests\Extending\AssertHasPreviousExceptionTrait;
use PhpCfdi\ImageCaptchaResolver\Tests\HttpTestCase;
use RuntimeException;
use stdClass;

final class AntiCaptchaConnectorTest extends HttpTestCase
{
    use AssertHasPreviousExceptionTrait;

    /** @var string */
    private $clientKey = 'client-key';

    public function createConnector(HttpClientInterface $client = null): AntiCaptchaConnector
    {
        return new AntiCaptchaConnector($this->clientKey, $client);
    }

    public function testConstructor(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $connector = new AntiCaptchaConnector($this->clientKey, $httpClient);

        $this->assertSame($this->clientKey, $connector->getClientKey());
        $this->assertSame($httpClient, $connector->getHttpClient());
    }

    public function testConstructorWithoutHttpClient(): void
    {
        $connector = new AntiCaptchaConnector($this->clientKey);

        $this->assertInstanceOf(HttpClientInterface::class, $connector->getHttpClient());
    }

    public function testCreateTask(): void
    {
        $expectedTaskId = 'task-id';
        $image = CaptchaImage::newFromFile($this->filePath('captcha-qwerty.png'));

        $phpHttpMockClient = $this->createPhpHttpMockClient();
        $phpHttpMockClient->addResponse($this->createJsonRespose(['taskId' => $expectedTaskId]));
        $connector = $this->createConnector($this->createHttpClient($phpHttpMockClient));

        $taskId = $connector->createTask($image);
        $this->assertSame($expectedTaskId, $taskId);

        $lastRequest = $phpHttpMockClient->getLastRequest();
        /** @var stdClass $sentValues */
        $sentValues = json_decode((string) $lastRequest->getBody());
        $this->assertSame($this->clientKey, $sentValues->clientKey ?? '');
        $this->assertSame('ImageToTextTask', $sentValues->task->type ?? '');
        $this->assertSame($image->asBase64(), $sentValues->task->body ?? '');
    }

    public function testGetTaskResultProcessing(): void
    {
        $taskId = 'task-id';

        $phpHttpMockClient = $this->createPhpHttpMockClient();
        $phpHttpMockClient->addResponse($this->createJsonRespose(['status' => 'Processing']));
        $connector = $this->createConnector($this->createHttpClient($phpHttpMockClient));

        $result = $connector->getTaskResult($taskId);
        $this->assertSame('', $result);
    }

    public function testGetTaskResultReady(): void
    {
        $taskId = 'task-id';
        $expectedResult = 'qwerty';

        $phpHttpMockClient = $this->createPhpHttpMockClient();
        $phpHttpMockClient->addResponse(
            $this->createJsonRespose(['status' => 'Ready', 'solution' => ['text' => $expectedResult]])
        );
        $connector = $this->createConnector($this->createHttpClient($phpHttpMockClient));

        $result = $connector->getTaskResult($taskId);
        $this->assertSame($expectedResult, $result);
    }

    public function testGetTaskResultUnknown(): void
    {
        $taskId = 'task-id';

        $phpHttpMockClient = $this->createPhpHttpMockClient();
        $phpHttpMockClient->addResponse($this->createJsonRespose(['status' => 'Foo']));
        $connector = $this->createConnector($this->createHttpClient($phpHttpMockClient));

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Unknown status 'Foo' for task");
        $connector->getTaskResult($taskId);
    }

    public function testRequest(): void
    {
        $phpHttpMockClient = $this->createPhpHttpMockClient();
        $responseData = ['values' => ['foo' => '1', 'bar' => '2']];
        $phpHttpMockClient->addResponse($this->createJsonRespose($responseData));

        $connector = $this->createConnector($this->createHttpClient($phpHttpMockClient));
        $requestData = ['foo' => 'bar'];
        $result = $connector->request('fake-method', $requestData);
        $request = $phpHttpMockClient->getLastRequest();

        $this->assertJsonStringEqualsJsonString(
            json_encode($requestData + ['clientKey' => $this->clientKey]) ?: '',
            (string) $request->getBody()
        );

        $this->assertEquals(
            json_decode(json_encode($responseData) ?: ''),
            $result,
            'result was expected to be an object with the same values'
        );
    }

    public function testRequestWithErrorInResponse(): void
    {
        $phpHttpMockClient = $this->createPhpHttpMockClient();
        $phpHttpMockClient->addResponse(
            $this->createJsonRespose(['errorId' => '1', 'errorDescription' => 'description'])
        );
        $connector = $this->createConnector($this->createHttpClient($phpHttpMockClient));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Anti-Captcha Error (1): description');
        $connector->request('fake-method', ['foo' => 'bar']);
    }

    public function testRequestWithHttpError(): void
    {
        $phpHttpMockClient = $this->createPhpHttpMockClient();
        $phpHttpMockClient->addResponse($this->createJsonRespose([])->withStatus(404));
        $connector = $this->createConnector($this->createHttpClient($phpHttpMockClient));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HTTP error connecting to Anti-Captcha');
        $connector->request('fake-method', ['foo' => 'bar']);
    }

    public function testRequestWithException(): void
    {
        $expectedException = new Exception('Dummy Exception');
        $phpHttpMockClient = $this->createPhpHttpMockClient();
        $phpHttpMockClient->addException($expectedException);
        $connector = $this->createConnector($this->createHttpClient($phpHttpMockClient));

        $catchedException = null;
        try {
            $connector->request('fake-method', ['foo' => 'bar']);
        } catch (RuntimeException $exception) {
            $catchedException = $exception;
        }

        if (null === $catchedException) {
            $this->fail("Expected exception wasn't thrown");
        }
        $this->assertStringMatchesFormat('HTTP error connecting to Anti-Captcha %s', $catchedException->getMessage());
        $this->assertHasPreviousException($expectedException, $catchedException);
    }
}
