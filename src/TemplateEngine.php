<?php

declare(strict_types=1);

namespace Bloatless\TemplateEngine;

require_once __DIR__ . '/TemplateEngineException.php';

class TemplateEngine
{
    protected Lexer $lexer;

    protected Parser $parser;

    protected Compiler $compiler;

    protected string $pathViews = '';

    public function __construct(array $config, Lexer $lexer, Parser $parser, Compiler $compiler)
    {
        $this->setPathViews($config['path_views']);

        $this->lexer = $lexer;
        $this->parser = $parser;
        $this->compiler = $compiler;
    }

    public function render(string $view, array $variables): string
    {
        $viewContent = $this->getViewContent($view);
        $tokens = $this->lexer->__invoke($viewContent);
        $nodes = $this->parser->__invoke($tokens);

        return '';
    }

    public function setPathViews(string $path): void
    {
        $path = rtrim($path, '/');
        $this->pathViews = $path . '/';
        if (!is_dir($this->pathViews)) {
            throw new TemplateEngineException('Invalid "path_views". Folder not found. Check config!');
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
}
