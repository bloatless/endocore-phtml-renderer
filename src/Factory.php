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

        $viewComponentCompiler = new ViewComponentCompiler();
        $viewComponentCompiler->setViewComponents($viewComponents);

        $viewCompiler = new ViewCompiler($viewComponentCompiler);
        $viewCompiler->setViewPath($pathViews);
        $viewCompiler->setCompilePath($compilePath);

        $viewRenderer = new ViewRenderer();

        $renderer = new PhtmlRenderer($viewCompiler, $viewRenderer);

        return $renderer;
    }
}
