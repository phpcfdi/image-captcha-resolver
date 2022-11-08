<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Resolvers\CaptchaLocalResolver;

use PhpCfdi\ImageCaptchaResolver\CaptchaAnswer;
use PhpCfdi\ImageCaptchaResolver\CaptchaAnswerInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaImageInterface;
use PhpCfdi\ImageCaptchaResolver\HttpClient\HttpClient;
use PhpCfdi\ImageCaptchaResolver\HttpClient\HttpClientInterface;
use PhpCfdi\ImageCaptchaResolver\HttpClient\HttpException;
use PhpCfdi\ImageCaptchaResolver\HttpClient\UndiscoverableClientException;
use PhpCfdi\ImageCaptchaResolver\Timer\TimerInterface;
use RuntimeException;

/**
 * Connector to captcha local resolver service
 *
 * @see https://github.com/eclipxe13/captcha-local-resolver
 * @internal
 */
class CaptchaLocalResolverConnector
{
    /** @var string Full URL to access service, by example http://localhost:9095 */
    private $baseUrl;

    /** @var HttpClientInterface */
    private $httpClient;

    /**
     * Connector constructor
     *
     * @param string $baseUrl Full URL to access service, by example http://localhost:9095
     * @param HttpClientInterface|null $httpClient
     * @throws UndiscoverableClientException
     */
    public function __construct(string $baseUrl, HttpClientInterface $httpClient = null)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->httpClient = $httpClient ?? HttpClient::discover();
    }

    /**
     * @param CaptchaImageInterface $image
     * @param TimerInterface $timer
     * @return CaptchaAnswerInterface
     * @throws RuntimeException if unable to get an answer after <timeout> seconds
     * @throws RuntimeException if unable to send image
     * @throws RuntimeException if code does not exists
     * @throws RuntimeException if unable to check code
     * @throws RuntimeException if http transaction error occurs
     */
    public function resolveImage(CaptchaImageInterface $image, TimerInterface $timer): CaptchaAnswerInterface
    {
        $code = $this->sendImage($image);
        $timer->start();
        do {
            $result = $this->checkCode($code);
            if ('' !== $result) {
                break;
            }
            if ($timer->isExpired()) {
                throw new RuntimeException("Unable to get an answer after {$timer->getTimeoutSeconds()} seconds");
            }
            $timer->wait();
        } while (true);

        return new CaptchaAnswer($result);
    }

    /**
     * @param CaptchaImageInterface $image
     * @return string
     * @throws RuntimeException if unable to send image
     * @throws RuntimeException if image was sent but service returns empty code
     * @throws RuntimeException if http transaction error occurs
     */
    public function sendImage(CaptchaImageInterface $image): string
    {
        $uri = $this->buildUri('/send-image'); // TODO
        try {
            $response = $this->getHttpClient()->postJson($uri, [], (object)['image' => $image->asBase64()]);
        } catch (HttpException $exception) {
            throw new RuntimeException("Unable to send image to $uri", 0, $exception);
        }
        $contents = strval($response->getBody());
        $data = json_decode($contents, true);
        $code = (is_array($data)) ? strval($data['code'] ?? '') : '';
        if ('' === $code) {
            throw new RuntimeException('Image was sent but service returns empty code');
        }
        return $code;
    }

    /**
     * Check code for answer, if empty string means answer does not exists yet
     *
     * @param string $code
     * @return string
     * @throws RuntimeException if code does not exists
     * @throws RuntimeException if unable to check code
     * @throws RuntimeException if http transaction error occurs
     */
    public function checkCode(string $code): string
    {
        $uri = $this->buildUri('/obtain-decoded');
        try {
            $response = $this->getHttpClient()->postJson($uri, [], (object)['code' => $code]);
        } catch (HttpException $exception) {
            if (
                $exception->hasResponse()
                && 404 === $exception->getResponse()->getStatusCode()
            ) {
                throw new RuntimeException("Unable to retrieve answer for non-existent code $code", 0, $exception);
            }

            throw new RuntimeException("Unable to check code for $uri", 0, $exception);
        }
        $contents = strval($response->getBody());
        $data = json_decode($contents, true);
        return (is_array($data)) ? strval($data['answer'] ?? '') : '';
    }

    public function buildUri(string $action): string
    {
        return $this->getBaseUrl() . $action;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }
}
