<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Resolvers\CommandLineResolver;

use LogicException;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\RuntimeException as ProcessRuntimeException;
use Symfony\Component\Process\Process;

class SymfonyProcessRunner implements ProcessRunnerInterface
{
    public const DEFAULT_TIMEOUT = 60;

    /** @var float */
    private $timeoutSeconds;

    public function __construct(float $timeoutSeconds = self::DEFAULT_TIMEOUT)
    {
        if (! class_exists(Process::class)) {
            // @codeCoverageIgnoreStart
            throw new LogicException('Install symfony/process in order to use this process runner');
            // @codeCoverageIgnoreEnd
        }
        $this->timeoutSeconds = max(0, $timeoutSeconds);
    }

    /**
     * @throws RuntimeException
     */
    public function run(string ...$command): ProcessResult
    {
        $process = $this->createProcess($command);
        $process->setTimeout($this->timeoutSeconds);
        $output = [];
        try {
            $process->run(
                function (string $type, string $text) use (&$output): void {
                    $output[] = rtrim($text, PHP_EOL);
                }
            );
        } catch (ProcessTimedOutException $exception) {
            throw new RuntimeException("Process timeout after {$this->getTimeoutSeconds()} seconds", 0, $exception);
            // @codeCoverageIgnoreStart
        } catch (ProcessSignaledException $exception) {
            throw new RuntimeException("Process killed by signal {$exception->getSignal()}", 0, $exception);
        } catch (ProcessRuntimeException $exception) {
            throw new RuntimeException('Unable to start process', 0, $exception);
        }
        // @codeCoverageIgnoreEnd
        return new ProcessResult($process->getExitCode() ?? 1, $output);
    }

    /**
     * @param string[] $command
     * @return Process<string, string>
     */
    public function createProcess(array $command): Process
    {
        return new Process($command);
    }

    public function getTimeoutSeconds(): float
    {
        return $this->timeoutSeconds;
    }
}
