<?php

declare(strict_types=1);

namespace PhpCfdi\ImageCaptchaResolver\Tests\Unit;

use LogicException;
use PhpCfdi\ImageCaptchaResolver\Resolvers\ConsoleResolver;
use RuntimeException;

final class ConsoleResolverWithInput extends ConsoleResolver
{
    /** @var resource|null */
    private $stdin;

    /**
     * @param string $input
     * @return $this
     * @throws RuntimeException
     */
    public function setInput(string $input): self
    {
        $stdin = fopen('php://temp', 'w+');
        if (false === $stdin) {
            throw new RuntimeException("Unable to open $input");
        }
        fwrite($stdin, $input);
        rewind($stdin);

        $this->stdin = $stdin;

        return $this;
    }

    /**
     * @return resource
     */
    protected function openStdInStream()
    {
        if (null === $this->stdin) {
            throw new LogicException('STDIN stream as been not set, use setInput first');
        }
        return $this->stdin;
    }
}
