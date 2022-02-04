<?php

declare(strict_types=1);

namespace Bloatless\TemplateEngine;

require_once __DIR__ . '/Node/AbstractNode.php';
require_once __DIR__ . '/Node/EchoNode.php';
require_once __DIR__ . '/Node/ElseNode.php';
require_once __DIR__ . '/Node/EndifNode.php';
require_once __DIR__ . '/Node/IfNode.php';

use Bloatless\TemplateEngine\Node\EchoNode;
use Bloatless\TemplateEngine\Node\ElseNode;
use Bloatless\TemplateEngine\Node\EndifNode;
use Bloatless\TemplateEngine\Node\IfNode;

require_once __DIR__ . '/Lexer.php';

class Parser
{
    public function __invoke(array $tokens): array
    {
        $nodes = $this->createNodes($tokens);

        return $nodes;
    }

    private function createNodes(array $tokens): array
    {
        $nodes = [];
        $openNodes = [
            Lexer::TT_IF => 0,
        ];

        foreach ($tokens as $token) {
            switch ($token['type']) {
                case Lexer::TT_ECHO:
                    $nodes[] = new EchoNode($token);
                    break;
                case Lexer::TT_IF:

                    $nodes[] = new IfNode($token);
                    $openNodes[Lexer::TT_IF]++;
                    break;
                case Lexer::TT_ELSE:
                    if ($openNodes[Lexer::TT_IF] === 0) {
                        throw new TemplateEngineException('Found else-node without matching if-node.');
                    }
                    $nodes[] = new ElseNode($token);
                    break;
                case Lexer::TT_ENDIF:
                    if ($openNodes[Lexer::TT_IF] === 0) {
                        throw new TemplateEngineException('Found endif-node without matching if-node.');
                    }
                    $nodes[] = new EndifNode($token);
                    $openNodes[Lexer::TT_IF]--;
            }
        }

        if (array_sum($openNodes) !== 0) {
            throw new TemplateEngineException('Count of opening and closing nodes does not match.');
        }

        return $nodes;
    }
}
