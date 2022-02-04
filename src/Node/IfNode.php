<?php

declare(strict_types=1);

namespace Bloatless\TemplateEngine\Node;

require_once __DIR__ . '/AbstractNode.php';

class IfNode extends AbstractNode
{
    public function compile(string $viewContent): string
    {
        $phpCode = str_replace('{%', '<?php', $this->token);
        $phpCode = str_replace(' %}', '%}', $phpCode);
        $phpCode = str_replace('%}', ': ?>', $phpCode);

        return $this->replaceToken($viewContent, $phpCode);
    }
}
