<?php

declare(strict_types=1);

namespace Bloatless\TemplateEngine;

require_once __DIR__ . '/Node/AbstractNode.php';

use Bloatless\TemplateEngine\Node\AbstractNode;

class Compiler
{
    protected string $compilePath = '';

    public function __construct(string $compilePath)
    {
        $this->setCompilePath($compilePath);
    }

    public function setCompilePath(string $compilePath): void
    {
        $compilePath = rtrim($compilePath, '/');
        $this->compilePath = $compilePath . '/';
        if (!is_dir($this->compilePath)) {
            throw new TemplateEngineException('Invalid "path_compile". Folder not found. Check config!');
        }
    }

    public function __invoke(string $viewName, array $nodes, string $viewContent): string
    {
        /** @var AbstractNode $node */
        foreach ($nodes as $node) {
            $viewContent = $node->makeUnique($viewContent);
        }

        /** @var AbstractNode $node */
        foreach ($nodes as $node) {
            $viewContent = $node->compile($viewContent);
        }

        return $this->storeCompiledViewContent($viewName, $viewContent);
    }

    protected function storeCompiledViewContent(string $viewName, string $content): string
    {
        $viewNameHash = md5($viewName);
        $pathToCompiledView = sprintf('%s%s.php', $this->compilePath, $viewNameHash);
        file_put_contents($pathToCompiledView, $content);

        return $pathToCompiledView;
    }
}
