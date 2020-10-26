<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer;

use Bloatless\Endocore\Components\PhtmlRenderer\PreCompiler\PreCompilerInterface;

class PhtmlRenderer implements RendererInterface
{
    /** @var string $viewPath Directory containing views. */
    private string $viewPath;

    /** @var string $compilePath Directory to store/cache compiled views */
    private string $compilePath;

    private array $preCompilers;

    private ViewRenderer $viewRenderer;

    protected array $templateVariables = [];

    public function __construct(ViewRenderer $viewRender)
    {
        $this->viewRenderer = $viewRender;
    }

    public function render(string $viewName = '', array $templateVariables = []): string
    {
        $viewContent = $this->getViewContent($viewName);
        $viewContent = $this->preCompile($viewContent, $templateVariables);
        $pathToCompiledView = $this->storeCompiledView($viewName, $viewContent);
        $html = $this->renderCompiledView($pathToCompiledView, $templateVariables);

        return $html;
    }

    private function preCompile(string $viewContent, array $templateVariables): string
    {
        if (empty($this->preCompilers)) {
            return $viewContent;
        }

        /** @var PreCompilerInterface $preCompiler */
        foreach ($this->preCompilers as $preCompiler) {
            $viewContent = $preCompiler->compile($viewContent, $templateVariables);
        }

        return $viewContent;
    }

    private function renderCompiledView(string $pathToCompiledView, array $templateVariables): string
    {
        return $this->viewRenderer->render($pathToCompiledView, $templateVariables);
    }

    private function getViewContent(string $viewName): string
    {
        $pathToView = sprintf('%s/%s.phtml', $this->viewPath, $viewName);
        if (!file_exists($pathToView)) {
            throw new TemplatingException(sprintf('View file not found on disk (%s)', $pathToView));
        }

        return file_get_contents($pathToView);
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

    public function setPreCompiler(PreCompilerInterface $preCompiler): void
    {
        $this->preCompilers[] = $preCompiler;
    }



    /**
     * Assigns template variables.
     *
     * @deprecated we should remove this...
     *
     * @param array $pairs
     * @return void
     */
    public function assign(array $pairs): void
    {
        $this->templateVariables = array_merge($this->templateVariables, $pairs);
    }
}
