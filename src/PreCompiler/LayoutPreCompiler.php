<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer\PreCompiler;

use Bloatless\Endocore\Components\PhtmlRenderer\TemplatingException;

class LayoutPreCompiler implements PreCompilerInterface
{
    private string $viewPath;

    public function compile(string $viewContent, array $templateVariables = []): string
    {
        $viewContent = $this->compileExtends($viewContent);
        $viewContent = $this->compileIncludes($viewContent);

        return $viewContent;
    }

    private function compileExtends(string $viewContent): string
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

    private function compileIncludes(string $viewContent): string
    {
        $includePattern = '/\{\{\sinclude\(\'(.+)\'\)\s\}\}/Us';
        $matchCount = preg_match_all($includePattern, $viewContent, $matches, PREG_SET_ORDER);
        if ($matchCount === 0) {
            return $viewContent;
        }

        foreach ($matches as $match) {
            $tag = $match[0];
            $pathToView = sprintf('%s/%s.phtml', $this->viewPath, $match[1]);
            if (!file_exists($pathToView)) {
                throw new TemplatingException(sprintf('Include file not found on disk (%s)', $pathToView));
            }
            $includeContent = file_get_contents($pathToView);
            if (preg_match($includePattern, $includeContent) > 0) {
                $includeContent = $this->compileIncludes($includeContent);
            }
            $viewContent = str_replace($tag, $includeContent, $viewContent);
        }

        return $viewContent;
    }

    public function setViewPath(string $viewPath): void
    {
        if (!file_exists($viewPath)) {
            throw new TemplatingException(sprintf('View path not found on disk (%s).', $viewPath));
        }
        $this->viewPath = rtrim($viewPath, '/');
    }
}
