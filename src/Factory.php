<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer;

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

        return $renderer;
    }
}
