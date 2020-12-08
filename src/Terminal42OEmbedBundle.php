<?php

declare(strict_types=1);

namespace Terminal42\OEmbedBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class Terminal42OEmbedBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = $this->createContainerExtension();
        }

        return $this->extension;
    }
}
