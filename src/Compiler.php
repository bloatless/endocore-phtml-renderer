<?php

declare(strict_types=1);

namespace Bloatless\TemplateEngine;

require_once __DIR__ . '/Node/AbstractNode.php';

use Bloatless\TemplateEngine\Node\AbstractNode;

class Compiler
{
    public function __invoke(array $nodes, string $viewContent): string
    {
        /** @var AbstractNode $node */
        foreach ($nodes as $node) {
           $viewContent = $node->compile($viewContent);
        }

        return $viewContent;
    }
}
