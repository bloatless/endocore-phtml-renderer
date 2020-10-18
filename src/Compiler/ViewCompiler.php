<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer\Compiler;

use Bloatless\Endocore\Components\PhtmlRenderer\TemplatingException;

class ViewCompiler
{
    /**
     * @var string $viewPath Folder containing (phtml) view files.
     */
    private string $viewPath = '';

    /**
     * @var string $compilePath Folder containing compiled views.
     */
    private string $compilePath = '';

    private ViewComponentCompiler $viewComponentCompiler;

    private MustacheTagCompiler $mustacheTagCompiler;

    public function __construct(ViewComponentCompiler $viewComponentCompiler, MustacheTagCompiler $mustacheTagCompiler)
    {
        $this->viewComponentCompiler = $viewComponentCompiler;
        $this->mustacheTagCompiler = $mustacheTagCompiler;
    }

    public function __invoke(string $viewName): string
    {
        // load view
        $viewContent = $this->getViewContent($viewName);

        // attach layout
        $viewContent = $this->attachLayout($viewContent);

        // - compile view components
        $viewContent = $this->compileViewComponents($viewContent);
        $viewContent = $this->compileMustacheTags($viewContent);

        // - save php
        $pathToCompiledView = $this->storeCompiledView($viewName, $viewContent);

        return $pathToCompiledView;
    }

    protected function getViewContent(string $viewName): string
    {
        $pathToView = sprintf('%s/%s.phtml', $this->viewPath, $viewName);
        if (!file_exists($pathToView)) {
            throw new TemplatingException(sprintf('View file not found on disk (%s)', $pathToView));
        }

        return file_get_contents($pathToView);
    }

    protected function attachLayout(string $viewContent): string
    {
        $matchCount = preg_match('/<!-- extends "(.+)" -->/Usi', $viewContent, $matches);
        if ($matchCount !== 1) {
            return $viewContent;
        }
        $layoutName = $matches[1];
        $pathToLayout = sprintf('%s/%s.phtml', $this->viewPath, $layoutName);
        if (!file_exists($pathToLayout)) {
            throw new TemplatingException(sprintf('Layout file not found on disk (%s)', $pathToLayout));
        }
        $layoutContent = file_get_contents($pathToLayout);
        if (strpos($layoutContent, '<!-- $view -->') === false) {
            throw new TemplatingException(
                sprintf('Invalid Layout file. View placeholder missing. (%s)', $pathToLayout)
            );
        }
        $viewContent = str_replace('<!-- $view -->', $viewContent, $layoutContent);

        return $viewContent;
    }

    protected function compileViewComponents(string $viewContent): string
    {
        $viewContent = $this->viewComponentCompiler->compile($viewContent);

        return $viewContent;
    }

    protected function compileMustacheTags(string $viewContent): string
    {
        $viewContent = $this->mustacheTagCompiler->compile($viewContent);

        return $viewContent;
    }

    protected function storeCompiledView(string $viewName, string $viewContent): string
    {
        $viewHash = $this->getViewHash($viewName);
        $pathToCompiledView = sprintf('%s/%s.php', $this->compilePath, $viewHash);
        $written = file_put_contents($pathToCompiledView, $viewContent);
        if ($written === false) {
            throw new TemplatingException('Could not store compiled view.');
        }

        return $pathToCompiledView;
    }

    protected function getViewHash(string $viewName): string
    {
        $pathToView = sprintf('%s/%s.phtml', $this->viewPath, $viewName);

        return md5($pathToView);
    }

    public function setViewPath(string $viewPath): void
    {
        if (!file_exists($viewPath)) {
            throw new TemplatingException(sprintf('View path not found on disk (%s).', $viewPath));
        }
        $this->viewPath = rtrim($viewPath, '/');
    }

    public function setCompilePath(string $compilePath): void
    {
        if (!file_exists($compilePath) || !is_writeable($compilePath)) {
            throw new TemplatingException(
                sprintf('Compile path not found on disk or not writeable (%s).', $compilePath)
            );
        }
        $this->compilePath = rtrim($compilePath, '/');
    }
}
