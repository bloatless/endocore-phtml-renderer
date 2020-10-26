<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer\PreCompiler;

interface PreCompilerInterface
{
    public function compile(string $content, array $templateVariables = []): string;
}
