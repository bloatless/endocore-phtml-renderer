<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer;

use Bloatless\Endocore\Components\PhtmlRenderer\Compiler\ViewCompiler;

class PhtmlRenderer implements RendererInterface
{
    protected ViewCompiler $viewCompiler;

    protected ViewRenderer $viewRenderer;

    protected array $templateVariables = [];

    public function __construct(ViewCompiler $viewCompiler, ViewRenderer $viewRenderer)
    {
        $this->viewCompiler = $viewCompiler;
        $this->viewRenderer = $viewRenderer;
    }

    /**
     * Assigns template variables.
     *
     * @deprecated we should remove this...
     *
     * @param array $pairs
     * @return void
     */
    public function assign(array $pairs): void
    {
        $this->templateVariables = array_merge($this->templateVariables, $pairs);
    }

    /**
     * Renders given view and returns html code.

     * @param string $view
     * @param array $variables
     * @throws TemplatingException
     * @return string
     */
    public function render(string $view = '', array $variables = []): string
    {
        $pathToCompiledView = $this->viewCompiler->__invoke($view);
        $html = $this->viewRenderer->__invoke($pathToCompiledView, $variables);

        return $html;
    }
}
