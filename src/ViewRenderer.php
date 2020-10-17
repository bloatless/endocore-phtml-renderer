<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer;

class ViewRenderer
{
    public function __invoke(string $pathToCompiledView, array $templateVariables = []): string
    {
        extract($templateVariables);
        ob_start();
        include $pathToCompiledView;

        return ob_get_clean();
    }
}
