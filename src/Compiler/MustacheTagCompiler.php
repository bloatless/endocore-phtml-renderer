<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer\Compiler;

class MustacheTagCompiler
{
    private $replacements = [];

    public function compile(string $source): string
    {
        $this->replacements = [];
        $this->parseOutTags($source);
        $this->parseUnescapedOutTags($source);
        $source = strtr($source, $this->replacements);

        return $source;
    }

    private function parseOutTags(string $source): void
    {
        $outTagCount = preg_match_all('/\{\{\s(\$[^\s]+)\s\}\}/Us', $source, $matches, PREG_SET_ORDER);
        if ($outTagCount === 0) {
            return;
        }

        $this->addReplacements($matches);
    }

    private function parseUnescapedOutTags(string $source): void
    {
        $outTagCount = preg_match_all('/\{\!\!\s(\$[^\s]+)\s\!\!\}/Us', $source, $matches, PREG_SET_ORDER);
        if ($outTagCount === 0) {
            return;
        }

        $this->addReplacements($matches, false);
    }

    private function addReplacements(array $matches, bool $escaped = true): void
    {
        foreach ($matches as $match) {
            $tag = $match[0];
            $varName = $match[1];
            if ($escaped === true) {
                $this->replacements[$tag] = sprintf('<?php echo htmlentities(%s); ?>', $varName);
            } else {
                $this->replacements[$tag] = sprintf('<?php echo %s; ?>', $varName);
            }
        }
    }
}
