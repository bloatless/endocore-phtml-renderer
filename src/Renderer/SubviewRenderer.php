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

    public function render(array $arguments): string
    {
        $phtmlRenderer = $this->phtmlRendererFactory->makeRenderer();
        $viewName = $arguments['viewName'];
        $templateVariables = $arguments['subviewArguments'] ?? [];

        return $phtmlRenderer->render($viewName, $templateVariables);
    }
}
