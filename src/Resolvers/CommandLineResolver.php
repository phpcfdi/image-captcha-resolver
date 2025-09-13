<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Resolvers;

use LogicException;
use PhpCfdi\ImageCaptchaResolver\CaptchaAnswerInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaImageInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use PhpCfdi\ImageCaptchaResolver\Internal\TemporaryFile;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptchaException;
use RuntimeException;
use Throwable;

final readonly class CommandLineResolver implements CaptchaResolverInterface
{
    /** @var string[] */
    private array $command;

    /**
     * CommandLineResolver constructor.
     *
     * @param string[] $command
     */
    public function __construct(
        array $command,
        private CommandLineResolver\AnswerBuilderInterface $answerBuilder,
        private CommandLineResolver\ProcessRunnerInterface $processRunner,
    ) {
        if ([] === $command) {
            throw new LogicException('Invalid command argument');
        }
        if ('{file}' === $command[0]) {
            throw new LogicException('Command cannot be "{file}"');
        }
        $this->command = $command;
    }

    /**
     * @param string[] $command
     */
    public static function create(
        array $command,
        ?CommandLineResolver\AnswerBuilderInterface $answerBuilder = null,
        ?CommandLineResolver\ProcessRunnerInterface $processRunner = null,
    ): self {
        return new self(
            $command,
            $answerBuilder ?? new CommandLineResolver\LastLineAnswerBuilder(),
            $processRunner ?? new CommandLineResolver\SymfonyProcessRunner(),
        );
    }

    /** @return string[] */
    public function getCommand(): array
    {
        return $this->command;
    }

    public function getAnswerBuilder(): CommandLineResolver\AnswerBuilderInterface
    {
        return $this->answerBuilder;
    }

    public function getProcessRunner(): CommandLineResolver\ProcessRunnerInterface
    {
        return $this->processRunner;
    }

    public function resolve(CaptchaImageInterface $image): CaptchaAnswerInterface
    {
        try {
            return $this->realResolve($image);
        } catch (Throwable $exception) {
            throw new UnableToResolveCaptchaException($this, $image, $exception);
        }
    }

    /** @return string[] */
    public function buildCommand(string $fileNameArgument): array
    {
        $command = $this->command;
        $indexToReplace = array_search('{file}', $command, true);
        if (false === $indexToReplace) {
            $command[] = $fileNameArgument;
        } else {
            $command[$indexToReplace] = $fileNameArgument;
        }
        return $command;
    }

    /**
     * @throws RuntimeException
     */
    private function realResolve(CaptchaImageInterface $image): CaptchaAnswerInterface
    {
        $temporaryFile = new TemporaryFile();
        $temporaryFile->putContents($image->asBinary());
        $temporaryFileName = $temporaryFile->getPath();

        $command = $this->buildCommand($temporaryFileName);
        $result = $this->processRunner->run(...$command);
        if (! $result->isSuccessful()) {
            throw new RuntimeException("Command execution return exit code {$result->getExitCode()} or no output");
        }

        return $this->answerBuilder->createAnswerFromProcessResult($result);
    }
}
