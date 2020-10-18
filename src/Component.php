<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer;

abstract class Component
{
    protected PhtmlRenderer $phtmlRenderer;

    protected string $content = '';

    protected array $attributes = [];

    public function __construct(PhtmlRenderer $phtmlRenderer)
    {
        $this->phtmlRenderer = $phtmlRenderer;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    protected function render(string $viewName, array $templateVariables = []): string
    {
        return $this->phtmlRenderer->render($viewName, $templateVariables);
    }

    abstract public function __invoke(): string;
}
