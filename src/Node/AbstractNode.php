<?php

declare(strict_types=1);

namespace Bloatless\TemplateEngine\Node;

abstract class AbstractNode
{
    protected string $token = '';

    public function __construct(array $token)
    {
        $this->token = $token['content'];
    }

    abstract public function compile(string $viewContent): string;

    protected function replaceToken(string $viewContent, string $replacement): string
    {
        return preg_replace('/' . preg_quote($this->token) . '/', $replacement, $viewContent, 1);
    }
}
