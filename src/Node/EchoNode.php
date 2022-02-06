<?php

declare(strict_types=1);

namespace Bloatless\TemplateEngine\Node;

require_once __DIR__ . '/AbstractNode.php';

class EchoNode extends AbstractNode
{
    public function compile(string $viewContent): string
    {
        $varName = preg_replace('/[\{\}\s]/', '', $this->token);
        if (str_ends_with($varName, '|raw')) {
            $varName = str_replace('|raw', '', $varName);
            $phpCode = sprintf('<?php echo %s; ?>', $varName);
        } else {
            $phpCode = sprintf('<?php echo htmlentities(%s); ?>', $varName);
        }

        return $this->replaceToken($viewContent, $phpCode);
    }
}
