<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer\Renderer;

use Bloatless\Endocore\Components\PhtmlRenderer\Factory as PhtmlRendererFactory;
use Bloatless\Endocore\Components\PhtmlRenderer\TemplatingException;

class ViewComponentRenderer implements RendererInterface
{
    private PhtmlRendererFactory $phtmlRendererFactory;

    private array $viewComponentClasses;

    private array $viewComponents;

    public function __construct(PhtmlRendererFactory $phtmlRendererFactory, array $viewComponentClasses)
    {
        $this->phtmlRendererFactory = $phtmlRendererFactory;
        $this->viewComponentClasses = $viewComponentClasses;
        $this->viewComponents = [];
    }

    public function render(array $arguments): string
    {
        $componentHash = $arguments['hash'] ?? '';
        $componentType = $arguments['type'] ?? '';
        $componentAction = $arguments['action'] ?? '';
        if (empty($componentHash)) {
            throw new TemplatingException('Component hash can not be empty.');
        }
        if (!isset($this->viewComponentClasses[$componentType])) {
            throw new TemplatingException(sprintf('Unknown view component type (%s)', $componentType));
        }

        $this->initComponent($componentType, $componentHash);
        switch ($componentAction) {
            case 'start':
                return $this->viewComponents[$componentHash]->start();
            case 'end':
                return $this->viewComponents[$componentHash]->end();
            default:
                throw new TemplatingException(sprintf('Invalid component action (%s)'));
        }
    }

    private function initComponent(string $componentType, string $componentHash): void
    {
        if (isset($this->viewComponents[$componentHash])) {
            return;
        }

        $componentClass = $this->viewComponentClasses[$componentType];
        $phtmlRenderer = $this->phtmlRendererFactory->makeRenderer();
        $this->viewComponents[$componentHash] = new $componentClass($phtmlRenderer);
    }
}
