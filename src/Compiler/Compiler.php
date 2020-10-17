<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer\Compiler;

use Bloatless\Endocore\Components\PhtmlRenderer\RendererInterface;

abstract class Compiler
{
    protected $config;

    protected $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }
}
