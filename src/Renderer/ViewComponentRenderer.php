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

    private array $templateVariables;

    public function __construct(PhtmlRendererFactory $phtmlRendererFactory, array $viewComponentClasses)
    {
        $this->phtmlRendererFactory = $phtmlRendererFactory;
        $this->viewComponentClasses = $viewComponentClasses;
        $this->viewComponents = [];
        $this->templateVariables = [];
    }

    public function render(array $arguments, array $templateVariables): string
    {
        $this->templateVariables = $templateVariables;
        $componentHash = $arguments['hash'] ?? '';
        $componentType = $arguments['type'] ?? '';
        $componentAction = $arguments['action'] ?? '';
        $attributesString = $arguments['attributes'] ?? '';
        if (empty($componentHash)) {
            throw new TemplatingException('Component hash can not be empty.');
        }
        if (!isset($this->viewComponentClasses[$componentType])) {
            throw new TemplatingException(sprintf('Unknown view component type (%s)', $componentType));
        }

        $this->initComponent($componentType, $componentHash);
        switch ($componentAction) {
            case 'start':
                $attributes = $this->getAttributes($attributesString);
                $this->viewComponents[$componentHash]->setAttributes($attributes);
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

    private function getAttributes(string $attributeString): array
    {
        if (empty($attributeString)) {
            return [];
        }
        $attributeString = base64_decode($attributeString);
        $attrCount = preg_match_all('/([\w:-]+)="([^"]+)"/Us', $attributeString, $attributeMatches, PREG_SET_ORDER);
        if ($attrCount === 0) {
            return [];
        }

        $attributes = [];
        foreach ($attributeMatches as $attr) {
            if (substr($attr[1], 0, 1) === ':') {
                $tmplVarKey = substr($attr[2], 1);
                $attributes[$tmplVarKey] = $this->templateVariables[$tmplVarKey] ?? null;
            } else {
                $attributes[$attr[1]] = $attr[2];
            }
        }

        return $attributes;
    }
}
