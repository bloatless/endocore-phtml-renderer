<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer\Renderer;

use Bloatless\Endocore\Components\PhtmlRenderer\Factory as PhtmlRendererFactory;

class SubviewRenderer implements RendererInterface
{
    private PhtmlRendererFactory $phtmlRendererFactory;

    public function __construct(PhtmlRendererFactory $phtmlRendererFactory)
    {
        $this->phtmlRendererFactory = $phtmlRendererFactory;
    }

    public function render(array $arguments, array $templateVariables): string
    {
        // includes have their own scope so we override template variables
        $templateVariables = $arguments['subviewArguments'] ?? [];
        $phtmlRenderer = $this->phtmlRendererFactory->makeRenderer();
        $viewName = $arguments['viewName'];

        return $phtmlRenderer->render($viewName, $templateVariables);
    }
}
