<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer;

use Bloatless\Endocore\Components\PhtmlRenderer\PreCompiler\SubviewPreCompiler;
use Bloatless\Endocore\Components\PhtmlRenderer\PreCompiler\LayoutPreCompiler;
use Bloatless\Endocore\Components\PhtmlRenderer\PreCompiler\MustachePreCompiler;
use Bloatless\Endocore\Components\PhtmlRenderer\PreCompiler\ViewComponentPreCompiler;
use Bloatless\Endocore\Components\PhtmlRenderer\Renderer\SubviewRenderer;
use Bloatless\Endocore\Components\PhtmlRenderer\Renderer\ViewComponentRenderer;

class Factory
{
    /**
     * @var array $config
     */
    protected array $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Creates and returns a new PhtmlRenderer instance.
     *
     * @return PhtmlRenderer
     * @throws TemplatingException
     */
    public function makeRenderer(): PhtmlRenderer
    {
        $pathViews = $this->config['path_views'] ?? '';
        $compilePath = $this->config['compile_path'] ?? '';
        $viewComponents = $this->config['view_components'] ?? [];

        // prepare renderer
        $viewRenderer = new ViewRenderer();
        $subviewRenderer = new SubviewRenderer($this);
        $viewComponentRenderer = new ViewComponentRenderer($this, $viewComponents);
        $viewRenderer->setRenderer('subview', $subviewRenderer);
        $viewRenderer->setRenderer('viewComponent', $viewComponentRenderer);

        // prepare pre-compiler
        $layoutPreCompiler = new LayoutPreCompiler();
        $layoutPreCompiler->setViewPath($pathViews);
        $mustachePreCompiler = new MustachePreCompiler();
        $subviewPreCompiler = new SubviewPreCompiler();
        $viewComponentPreCompiler = new ViewComponentPreCompiler();

        $renderer = new PhtmlRenderer($viewRenderer);
        $renderer->setViewPath($pathViews);
        $renderer->setCompilePath($compilePath);
        $renderer->setPreCompiler($layoutPreCompiler);
        $renderer->setPreCompiler($mustachePreCompiler);
        $renderer->setPreCompiler($subviewPreCompiler);
        $renderer->setPreCompiler($viewComponentPreCompiler);

        return $renderer;
    }
}
