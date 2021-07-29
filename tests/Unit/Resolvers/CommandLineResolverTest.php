<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit\Resolvers;

use LogicException;
use PhpCfdi\ImageCaptchaResolver\CaptchaImage;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use PhpCfdi\ImageCaptchaResolver\Resolvers\CommandLineResolver;
use PhpCfdi\ImageCaptchaResolver\Tests\TestCase;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptchaException;

final class CommandLineResolverTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = ['command'];
        $answerBuilder = $this->createMock(CommandLineResolver\AnswerBuilderInterface::class);
        $processRunner = $this->createMock(CommandLineResolver\ProcessRunnerInterface::class);

        $resolver = new CommandLineResolver($command, $answerBuilder, $processRunner);

        $this->assertInstanceOf(CaptchaResolverInterface::class, $resolver);
        $this->assertSame($command, $resolver->getCommand());
        $this->assertSame($answerBuilder, $resolver->getAnswerBuilder());
        $this->assertSame($processRunner, $resolver->getProcessRunner());
    }

    public function testCreate(): void
    {
        $command = ['command'];
        $resolver = CommandLineResolver::create($command);

        $this->assertInstanceOf(CaptchaResolverInterface::class, $resolver);
        $this->assertSame($command, $resolver->getCommand());
        $this->assertInstanceOf(CommandLineResolver\AnswerBuilderInterface::class, $resolver->getAnswerBuilder());
        $this->assertInstanceOf(CommandLineResolver\ProcessRunnerInterface::class, $resolver->getProcessRunner());
    }

    public function testConstructFailOnEmptyArray(): void
    {
        $this->expectException(LogicException::class);
        CommandLineResolver::create([]);
    }

    public function testConstructFailOnFileKeyword(): void
    {
        $this->expectException(LogicException::class);
        CommandLineResolver::create(['{file}']);
    }

    public function testBuildCommandLastArgument(): void
    {
        $command = ['command'];
        $resolver = CommandLineResolver::create($command);

        $this->assertSame(array_merge($command, ['temporary-file']), $resolver->buildCommand('temporary-file'));
    }

    public function testBuildCommandPlacedArgument(): void
    {
        $command = ['command', '{file}', 'foo'];
        $expected = ['command', 'temporary-file', 'foo'];
        $resolver = CommandLineResolver::create($command);

        $this->assertSame($expected, $resolver->buildCommand('temporary-file'));
    }

    public function testDummyResolverOk(): void
    {
        $image = CaptchaImage::newFromFile($this->filePath('captcha-qwerty.png'));
        $command = ['echo', '.start.', '{file}', '.end.'];
        $resolver = CommandLineResolver::create($command);
        /** @noinspection PhpUnhandledExceptionInspection */
        $answer = $resolver->resolve($image);
        $this->assertStringStartsWith('.start.', $answer->getValue());
        $this->assertStringEndsWith('.end.', $answer->getValue());
    }

    public function testDummyResolverFailure(): void
    {
        $image = CaptchaImage::newFromFile($this->filePath('captcha-qwerty.png'));
        $resolver = CommandLineResolver::create(['false']);
        $this->expectException(UnableToResolveCaptchaException::class);
        $resolver->resolve($image);
    }
}
