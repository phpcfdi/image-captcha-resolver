<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Resolvers\AntiCaptchaResolver;

use LogicException;
use PhpCfdi\ImageCaptchaResolver\HttpClient\HttpClient;
use PhpCfdi\ImageCaptchaResolver\HttpClient\HttpClientInterface;
use PhpCfdi\ImageCaptchaResolver\HttpClient\HttpException;
use PhpCfdi\ImageCaptchaResolver\HttpClient\UndiscoverableClientException;
use RuntimeException;
use stdClass;
use Stringable;

/**
 * This is a Guzzle based Anti-Captcha Tiny client, it allows to create a task,
 * query for a task solution and get curent balance.
 * Throws RuntimeException then HTTP error or Anti-Captcha report an error.
 */
class AntiCaptchaConnector
{
    public const BASE_URL = 'https://api.anti-captcha.com/';

    /** @var string */
    private $clientKey;

    /** @var HttpClientInterface */
    private $httpClient;

    /**
     * AntiCaptchaConnector constructor.
     *
     * @param string $clientKey
     * @param HttpClientInterface|null $httpClient
     * @throws UndiscoverableClientException
     */
    public function __construct(string $clientKey, HttpClientInterface $httpClient = null)
    {
        $this->clientKey = $clientKey;
        $this->httpClient = $httpClient ?? HttpClient::discover();
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * @param string|Stringable $base64Image
     * @return string
     * @throws RuntimeException
     */
    public function createTask($base64Image): string
    {
        /** @see https://anti-captcha.com/es/apidoc/task-types/ImageToTextTask */
        $postData = [
            'task' => [
                'type' => 'ImageToTextTask',
                'body' => $base64Image,
                'case' => true,
            ],
        ];

        $result = $this->request('createTask', $postData);

        return (string) $result->taskId;
    }

    /**
     * @param string $taskId
     * @return string
     * @throws RuntimeException
     */
    public function getTaskResult(string $taskId): string
    {
        /** @see https://anti-captcha.com/es/apidoc/methods/getTaskResult */
        $result = $this->request('getTaskResult', [
            'taskId' => $taskId,
        ]);

        $antiCaptchaStatus = strtolower($result->status);
        if ('processing' === $antiCaptchaStatus) {
            return '';
        }
        if ('ready' === $antiCaptchaStatus) {
            return strval($result->solution->text ?? '');
        }
        throw new LogicException("Unknown status '$result->status' for task");
    }

    /**
     * @param string $methodName
     * @param array<string, mixed> $postData
     * @return stdClass
     * @throws RuntimeException When anti-captcha service return an error status
     */
    public function request(string $methodName, array $postData): stdClass
    {
        $url = self::BASE_URL . $methodName;
        $data = ['clientKey' => $this->getClientKey()] + $postData;
        try {
            $response = $this->getHttpClient()->postJson($url, [], $data);
        } catch (HttpException $exception) {
            $message = sprintf('HTTP error connecting to Anti-Captcha %s', $url);
            throw new RuntimeException($message, 0, $exception);
        }

        $result = json_decode((string) $response->getBody());
        if (! $result instanceof stdClass) {
            $result = (object) ['errorId' => 1, 'errorDescription' => 'Response is not a JSON object'];
        }
        $errorId = intval($result->errorId ?? 0);
        if ($errorId > 0) {
            throw new RuntimeException(
                sprintf('Anti-Captcha Error (%d): %s', $errorId, strval($result->errorDescription ?? ''))
            );
        }

        return $result;
    }
}
