<?php

declare(strict_types=1);

namespace Bloatless\TemplateEngine;

require_once __DIR__ . '/TemplateEngineException.php';

class TemplateEngine
{
    protected Lexer $lexer;

    protected Parser $parser;

    protected Compiler $compiler;

    protected ViewRenderer $viewRenderer;

    protected string $pathViews = '';

    protected string $compilePath = '';

    public function __construct(
        array $config,
        Lexer $lexer,
        Parser $parser,
        Compiler $compiler,
        ViewRenderer $viewRenderer
    ) {
        $this->setPathViews($config['path_views']);
        $this->setCompilePath($config['path_compile']);

        $this->lexer = $lexer;
        $this->parser = $parser;
        $this->compiler = $compiler;
        $this->viewRenderer = $viewRenderer;
    }

    public function render(string $view, array $variables): string
    {
        $viewContent = $this->getViewContent($view);
        $tokens = $this->lexer->__invoke($viewContent);
        $nodes = $this->parser->__invoke($tokens);
        $compiledViewContent = $this->compiler->__invoke($nodes, $viewContent);
        $pathToCompiledView = $this->storeCompiledViewContent($view, $compiledViewContent);
        $html = $this->viewRenderer->__invoke($pathToCompiledView, $variables);

        return $html;
    }

    public function setPathViews(string $path): void
    {
        $path = rtrim($path, '/');
        $this->pathViews = $path . '/';
        if (!is_dir($this->pathViews)) {
            throw new TemplateEngineException('Invalid "path_views". Folder not found. Check config!');
        }
    }

    public function setCompilePath(string $path): void
    {
        $path = rtrim($path, '/');
        $this->compilePath = $path . '/';
        if (!is_dir($this->compilePath)) {
            throw new TemplateEngineException('Invalid "path_compile". Folder not found. Check config!');
        }
    }

    protected function getViewContent(string $view): string
    {
        $pathToView = $this->pathViews . $view;
        if (!file_exists($pathToView)) {
            throw new TemplateEngineException(sprintf('View not found on disk. (%s)', $pathToView));
        }

        return file_get_contents($pathToView);
    }

    protected function storeCompiledViewContent(string $view, string $content): string
    {
        $vieNameHash = md5($view);
        $pathToCompiledView = sprintf('%s%s.php', $this->compilePath, $vieNameHash);
        file_put_contents($pathToCompiledView, $content);

        return $pathToCompiledView;
    }
}
