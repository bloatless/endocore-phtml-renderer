<?php

declare(strict_types=1);

namespace Bloatless\TemplateEngine;

require_once __DIR__ . '/Node/AbstractNode.php';
require_once __DIR__ . '/Node/EchoNode.php';
require_once __DIR__ . '/Node/ElseifNode.php';
require_once __DIR__ . '/Node/ElseNode.php';
require_once __DIR__ . '/Node/EndforeachNode.php';
require_once __DIR__ . '/Node/EndifNode.php';
require_once __DIR__ . '/Node/ForeachNode.php';
require_once __DIR__ . '/Node/IfNode.php';
require_once __DIR__ . '/Node/IncludeNode.php';

use Bloatless\TemplateEngine\Node\EchoNode;
use Bloatless\TemplateEngine\Node\ElseifNode;
use Bloatless\TemplateEngine\Node\ElseNode;
use Bloatless\TemplateEngine\Node\EndforeachNode;
use Bloatless\TemplateEngine\Node\EndifNode;
use Bloatless\TemplateEngine\Node\ForeachNode;
use Bloatless\TemplateEngine\Node\IfNode;
use Bloatless\TemplateEngine\Node\IncludeNode;

require_once __DIR__ . '/Lexer.php';

class Parser
{
    protected TemplateEngine $templateEngine;

    public function __invoke(array $tokens): array
    {
        $nodes = $this->createNodes($tokens);

        return $nodes;
    }

    public function setTemplateEngine(TemplateEngine $templateEngine): void
    {
        $this->templateEngine = $templateEngine;
    }

    private function createNodes(array $tokens): array
    {
        $nodes = [];
        $openNodes = [
            Lexer::TT_IF => 0,
            Lexer::TT_FOREACH => 0,
        ];

        foreach ($tokens as $token) {
            switch ($token['type']) {
                case Lexer::TT_ECHO:
                    $nodes[] = new EchoNode($this->templateEngine, $token);
                    break;
                case Lexer::TT_INCLUDE:
                    $nodes[] = new IncludeNode($this->templateEngine, $token);
                    break;
                case Lexer::TT_IF:
                    $nodes[] = new IfNode($this->templateEngine, $token);
                    $openNodes[Lexer::TT_IF]++;
                    break;
                case Lexer::TT_FOREACH:
                    $nodes[] = new ForeachNode($this->templateEngine, $token);
                    $openNodes[Lexer::TT_FOREACH]++;
                    break;
                case Lexer::TT_ELSE:
                    if ($openNodes[Lexer::TT_IF] === 0) {
                        throw new TemplateEngineException('Found else-node without matching if-node.');
                    }
                    $nodes[] = new ElseNode($this->templateEngine, $token);
                    break;
                case Lexer::TT_ELSEIF:
                    if ($openNodes[Lexer::TT_IF] === 0) {
                        throw new TemplateEngineException('Found elseif-node without matching if-node.');
                    }
                    $nodes[] = new ElseifNode($this->templateEngine, $token);
                    break;
                case Lexer::TT_ENDIF:
                    if ($openNodes[Lexer::TT_IF] === 0) {
                        throw new TemplateEngineException('Found endif-node without matching if-node.');
                    }
                    $nodes[] = new EndifNode($this->templateEngine, $token);
                    $openNodes[Lexer::TT_IF]--;
                    break;
                case Lexer::TT_ENDFOREACH:
                    if ($openNodes[Lexer::TT_FOREACH] === 0) {
                        throw new TemplateEngineException('Found endforeach-node without matching foreach-node.');
                    }
                    $nodes[] = new EndforeachNode($this->templateEngine, $token);
                    $openNodes[Lexer::TT_FOREACH]--;
                    break;
            }
        }

        if (array_sum($openNodes) !== 0) {
            throw new TemplateEngineException('Count of opening and closing nodes does not match.');
        }

        return $nodes;
    }
}
