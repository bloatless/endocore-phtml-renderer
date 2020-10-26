<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer;

abstract class Component
{
    protected PhtmlRenderer $phtmlRenderer;

    protected string $content = '';

    protected array $attributes = [];

    protected array $data = [];

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

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function start(): string
    {
        ob_start();

        return '';
    }

    public function end(): string
    {
        $content = ob_get_clean();
        $this->setContent($content);

        return $this->__invoke();
    }

    protected function render(string $viewName, array $templateVariables = []): string
    {
        return $this->phtmlRenderer->render($viewName, $templateVariables);
    }

    abstract public function __invoke(): string;
}
