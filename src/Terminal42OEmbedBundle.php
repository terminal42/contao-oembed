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
}
