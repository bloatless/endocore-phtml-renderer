<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer;

use Bloatless\Endocore\Components\PhtmlRenderer\Compiler\MustacheTagCompiler;
use Bloatless\Endocore\Components\PhtmlRenderer\Compiler\ViewComponentCompiler;

class Factory
{
    /**
     * @var array $config
     */
    protected $config = [];

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
        $renderer = new PhtmlRenderer;
        $renderer->setPathViews($pathViews);

        // add compiler for mustache tags
        $mustacheTagCompiler = new MustacheTagCompiler($this->config);
        $renderer->addCompiler('mustacheTagCompiler', $mustacheTagCompiler);

        // add compiler for view-components
        $viewComponentCompiler = new ViewComponentCompiler($this->config);
        $viewComponentCompiler->setViewComponents($this->config['view_components'] ?? []);
        $renderer->addCompiler('viewComponentCompiler', $viewComponentCompiler);

        return $renderer;
    }
}
