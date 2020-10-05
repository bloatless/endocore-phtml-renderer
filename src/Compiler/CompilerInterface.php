<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer\Compiler;

interface CompilerInterface
{
    public function compile(string $content, array $templateVariables = []): string;
}
