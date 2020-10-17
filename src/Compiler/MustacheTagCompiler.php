<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer\Compiler;

class MustacheTagCompiler extends Compiler implements CompilerInterface
{
    private $templateVariables = [];

    private $replacements = [];

    public function compile(string $source, array $templateVariables = []): string
    {
        $this->replacements = [];
        $this->templateVariables = $templateVariables;
        $this->parseOutTags($source);
        $this->parseUnescapedOutTags($source);
        $source = strtr($source, $this->replacements);

        return $source;
    }

    private function parseOutTags(string $source): void
    {
        $outTagCount = preg_match_all('/\{\{\s\$([\w-]+)\s\}\}/Us', $source, $matches, PREG_SET_ORDER);
        if ($outTagCount === 0) {
            return;
        }

        $this->addOutReplacements($matches);
    }

    private function parseUnescapedOutTags(string $source): void
    {
        $outTagCount = preg_match_all('/\{\!\!\s\$([\w-]+)\s\!\!\}/Us', $source, $matches, PREG_SET_ORDER);
        if ($outTagCount === 0) {
            return;
        }

        $this->addOutReplacements($matches, false);
    }

    private function addOutReplacements(array $matches, bool $escaped = true): void
    {
        foreach ($matches as $match) {
            $tag = $match[0];
            $varName = $match[1];
            if (!isset($this->templateVariables[$varName])) {
                continue;
            }
            $varContent = (string) $this->templateVariables[$varName];
            if ($escaped === true) {
                $this->replacements[$tag] = htmlentities($varContent);
            } else {
                $this->replacements[$tag] = $varContent;
            }
        }
    }
}
