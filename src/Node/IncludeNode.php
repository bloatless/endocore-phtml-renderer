<?php

declare(strict_types=1);

namespace Bloatless\TemplateEngine\Node;

require_once __DIR__ . '/AbstractNode.php';

class IncludeNode extends AbstractNode
{
    public function compile(string $viewContent): string
    {
        preg_match('/include\(\'([^\']+)\'\)/', $this->token, $match);
        $viewName = $match[1] ?? '';
        $viewName = $viewName . '.tmpl';
        $pathToCompiledView = $this->templateEngine->compile($viewName, []);
        $phpCode = sprintf('<?php include \'%s\'; ?>', $pathToCompiledView);

        return $this->replaceToken($viewContent, $phpCode);
    }
}
