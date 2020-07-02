<?php

namespace Terminal42\OEmbedBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class Terminal42OEmbedBundle extends Bundle
{
    public function getPath()
    {
        return \dirname(__DIR__);
    }
}
