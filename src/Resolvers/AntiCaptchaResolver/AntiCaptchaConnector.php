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

    private readonly HttpClientInterface $httpClient;

    /**
     * AntiCaptchaConnector constructor.
     *
     * @throws UndiscoverableClientException
     */
    public function __construct(private readonly string $clientKey, ?HttpClientInterface $httpClient = null)
    {
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

        /**
         * @see https://anti-captcha.com/es/apidoc/methods/createTask
         * @phpstan-var stdClass&object{
         *     errorId: int,
         *     errorCode?: string,
         *     errorDescription?: string,
         *     taskId: int,
         * } $result
         */
        $result = $this->request('createTask', $postData);

        return (string) $result->taskId;
    }

    /**
     * @throws RuntimeException
     */
    public function getTaskResult(string $taskId): string
    {
        /**
         * @see https://anti-captcha.com/es/apidoc/methods/getTaskResult
         * @phpstan-var stdClass&object{
         *     errorId: int,
         *     errorCode?: string,
         *     errorDescription?: string,
         *     status?: string,
         *     solution?: stdClass&object{text: string, url: string},
         *     cost?: string,
         *     ip?: string,
         *     createTime?: int,
         *     endTime?: int,
         *     solveCount?: int,
         * } $result
         */
        $result = $this->request('getTaskResult', [
            'taskId' => $taskId,
        ]);

        $antiCaptchaStatus = strtolower($result->status ?? '');
        if ('processing' === $antiCaptchaStatus) {
            return '';
        }
        if ('ready' === $antiCaptchaStatus) {
            if (! isset($result->solution)) {
                throw new LogicException('Expected solution object was not received.');
            }
            return strval($result->solution->text ?? '');
        }
        throw new LogicException(sprintf("Unknown status '%s' for task", $result->status ?? ''));
    }

    /**
     * @param array<string, mixed> $postData
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
        /** @phpstan-var stdClass&object{errorId?: scalar, errorDescription?: scalar} $result */
        $errorId = intval($result->errorId ?? 0);
        if ($errorId > 0) {
            throw new RuntimeException(
                sprintf('Anti-Captcha Error (%d): %s', $errorId, $result->errorDescription ?? ''),
            );
        }

        return $result;
    }
}
