<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer;

use Bloatless\Endocore\Components\PhtmlRenderer\Compiler\CompilerInterface;

class PhtmlRenderer implements RendererInterface
{
    /**
     * @var string $pathViews
     */
    protected $pathViews = '';

    /**
     * @var string $layout
     */
    protected $layout = '';

    /**
     * @var string $view
     */
    protected $view = '';

    /**
     * @var array $templateVariables
     */
    protected $templateVariables = [];

    /**
     * @var array $compilers
     */
    private $compilers = [];

    /**
     * Returns path containing view files.
     *
     * @return string
     */
    public function getPathViews(): string
    {
        return $this->pathViews;
    }

    /**
     * Sets path containing view files.
     *
     * @param string $path
     * @throws TemplatingException
     */
    public function setPathViews(string $path): void
    {
        if (!file_exists($path)) {
            throw new TemplatingException(sprintf('Path can not be found on disk (%s).', $path));
        }
        $this->pathViews = rtrim($path, '/');
    }

    /**
     * Returns path to given view.
     *
     * @param string $viewName
     * @return string
     */
    public function getPathView(string $viewName): string
    {
        $pathViews = $this->getPathViews();

        return $pathViews . '/' . $viewName . '.phtml';
    }

    /**
     * Returns layout name/file.
     *
     * @return string
     */
    public function getLayout(): string
    {
        return $this->layout;
    }

    /**
     * Sets layout name/file.
     *
     * @param string $layout
     * @return void
     */
    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * Returns view name.
     *
     * @return string
     */
    public function getView(): string
    {
        return $this->view;
    }

    /**
     * Sets view name.
     *
     * @param string $view
     * @return void
     */
    public function setView(string $view): void
    {
        $this->view = $view;
    }

    /**
     * Assigns template variables.
     *
     * @param array $pairs
     * @return void
     */
    public function assign(array $pairs): void
    {
        $this->templateVariables = array_merge($this->templateVariables, $pairs);
    }

    /**
     * Returns array containing template variables.
     *
     * @return array
     */
    public function getTemplateVariables(): array
    {
        return $this->templateVariables;
    }

    /**
     * Registers a compiler to be executed during the render process.
     *
     * @param string $compilerName
     * @param CompilerInterface $compiler
     */
    public function addCompiler(string $compilerName, CompilerInterface $compiler): void
    {
        $this->compilers[$compilerName] = $compiler;
    }

    /**
     * Renders given view and returns html code.

     * @param string $view
     * @param array $variables
     * @throws TemplatingException
     * @return string
     */
    public function render(string $view = '', array $variables = []): string
    {
        if (!empty($view)) {
            $this->setView($view);
        }
        $this->assign($variables);
        $content = $this->renderView();
        if (!empty($this->layout)) {
            $content = $this->renderLayout($content);
        }

        return $this->compileView($content);
    }

    /**
     * Renders a view file.
     *
     * @throws TemplatingException
     * @return string
     */
    protected function renderView(): string
    {
        $viewFile = $this->getPathView($this->view);
        if (!file_exists($viewFile)) {
            throw new TemplatingException(sprintf('View file not found. (%s)', $viewFile));
        }
        $viewContent = file_get_contents($viewFile);
        if (preg_match('/<!-- extends "(.+)" -->/Usi', $viewContent, $matches) === 1) {
            $this->setLayout($matches[1]);
        }

        return $this->renderFile($viewFile);
    }

    private function compileView(string $content): string
    {
        if (empty($this->compilers)) {
            return $content;
        }
        /** @var \Bloatless\Endocore\Components\PhtmlRenderer\Compiler\CompilerInterface $compiler */
        foreach ($this->compilers as $compiler) {
            $content = $compiler->compile($content, $this->templateVariables);
        }

        return $content;
    }

    /**
     * Renders a layout file.
     *
     * @param string $content
     * @throws TemplatingException
     * @return string
     */
    protected function renderLayout(string $content): string
    {
        $content = str_replace('<!-- extends "' . $this->layout . '" -->', '', $content);
        $content = trim($content);
        $this->assign(['content' => $content]);
        $layoutFile = $this->getPathView($this->layout);

        return $this->renderFile($layoutFile);
    }

    /**
     * @param string $templateFile
     * @return string
     * @throws TemplatingException
     */
    protected function renderFile(string $templateFile): string
    {
        if (!file_exists($templateFile)) {
            throw new TemplatingException(sprintf('Template file not found. (%s)', $templateFile));
        }
        extract($this->templateVariables);
        ob_start();
        include $templateFile;
        return ob_get_clean();
    }

    /**
     * (Safely) outputs a variable.
     *
     * @deprecated Use mustache syntax in views.
     *
     * @param mixed $value
     * @param bool $secure
     * @return void
     */
    protected function out($value, $secure = true): void
    {
        if ($secure === true) {
            $value = (string) $value;
            echo htmlentities($value);
        } else {
            echo $value;
        }
    }

    /**
     * Includes a partial.
     *
     * @param string $partial
     * @param array $data
     */
    protected function include(string $partial, array $data = []): void
    {
        $pathToPartial = $this->getPathView($partial);
        extract($data);
        ob_start();
        include $pathToPartial;
        echo ob_get_clean();
    }
}
