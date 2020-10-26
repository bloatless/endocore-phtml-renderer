<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer\PreCompiler;

class SubviewPreCompiler implements PreCompilerInterface
{
    public function compile(string $viewContent, array $templateVariables = []): string
    {
        $includePattern = '/\{\{\sview\(\'(?<viewName>.+)\'(?:,\s\[(?<arguments>.*)\])?\)\s\}\}/Us';
        $matchCount = preg_match_all($includePattern, $viewContent, $matches, PREG_SET_ORDER);
        if ($matchCount === 0) {
            return $viewContent;
        }

        $phpCodePattern = '<?php $this->call(\'subview\', [\'viewName\' => \'%s\', \'subviewArguments\' => [%s]]); ?>';
        foreach ($matches as $match) {
            $tag = $match[0];
            $tagReplacement = sprintf($phpCodePattern, $match['viewName'], $match['arguments']);
            $viewContent = str_replace($tag, $tagReplacement, $viewContent);
        }

        return $viewContent;
    }
}
