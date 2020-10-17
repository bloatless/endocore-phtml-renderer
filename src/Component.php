<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer;

abstract class Component
{
    protected $content = '';

    protected $attributes = [];

    public function __construct(string $content = '', array $attributes = [])
    {
        $this->content = $content;
        $this->attributes = $attributes;
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

    }

    abstract public function __invoke(): string;
}
