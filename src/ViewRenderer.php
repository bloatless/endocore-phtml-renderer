<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer;

use Bloatless\Endocore\Components\PhtmlRenderer\Renderer\RendererInterface as ViewRendererInterface;

class ViewRenderer
{
    private array $renderers;

    private array $templateVariables;

    public function render(string $pathToCompiledView, array $templateVariables = []): string
    {
        $this->templateVariables = $templateVariables;
        extract($templateVariables);
        ob_start();
        include $pathToCompiledView;

        return ob_get_clean();
    }

    private function call(string $rendererName, array $arguments = []): void
    {
        if (!isset($this->renderers[$rendererName])) {
            throw new TemplatingException(sprintf('Invalid renderer name (%s)', $rendererName));
        }

        /** @var ViewRendererInterface $renderer */
        $renderer = $this->renderers[$rendererName];
        echo $renderer->render($arguments, $this->templateVariables);
    }

    public function setRenderer(string $rendererName, ViewRendererInterface $renderer): void
    {
        $this->renderers[$rendererName] = $renderer;
    }
}
