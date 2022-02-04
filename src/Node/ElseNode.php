<?php

declare(strict_types=1);

namespace Bloatless\TemplateEngine\Node;

require_once __DIR__ . '/AbstractNode.php';

class ElseNode extends AbstractNode
{
    public function compile(string $viewContent): string
    {
        return $this->replaceToken($viewContent, '<?php else: ?>');
    }
}