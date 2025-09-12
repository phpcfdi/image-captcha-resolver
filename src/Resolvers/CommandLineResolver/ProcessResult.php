<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Resolvers\CommandLineResolver;

final class ProcessResult
{
    private int $exitCode;

    /** @var string[] */
    private array $output;

    /**
     * @param string[] $output
     */
    public function __construct(int $exitCode, array $output)
    {
        $this->exitCode = $exitCode;
        $this->output = $output;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    /** @return string[] */
    public function getOutput(): array
    {
        return $this->output;
    }

    public function getLastLine(): string
    {
        return strval(end($this->output));
    }

    public function isSuccessful(): bool
    {
        return 0 === $this->exitCode && [] !== $this->output;
    }
}
