<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer\Renderer;

interface RendererInterface
{
    public function render(array $arguments, array $templateVariables): string;
}
