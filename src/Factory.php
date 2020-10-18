<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer;

use Bloatless\Endocore\Components\PhtmlRenderer\Compiler\ViewCompiler;
use Bloatless\Endocore\Components\PhtmlRenderer\Compiler\ViewComponentCompiler;

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

        $viewComponentCompiler = new ViewComponentCompiler($this->config);
        $viewComponentCompiler->setViewComponents($viewComponents);

        $viewCompiler = new ViewCompiler($viewComponentCompiler);
        $viewCompiler->setViewPath($pathViews);
        $viewCompiler->setCompilePath($compilePath);

        $viewRenderer = new ViewRenderer();

        $renderer = new PhtmlRenderer($viewCompiler, $viewRenderer);

        return $renderer;
    }

    public function makeViewComponent(string $componentName)
    {
        $viewComponents = $this->config['view_components'] ?? [];
        if (!isset($viewComponents[$componentName])) {
            throw new TemplatingException('Unknown view component');
        }

        $phtmlRenderer = $this->makeRenderer();
        $componentClass = $viewComponents[$componentName];
        $viewComponent = new $componentClass($phtmlRenderer);

        return $viewComponent;
    }
}
