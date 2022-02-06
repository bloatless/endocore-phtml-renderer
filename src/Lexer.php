<?php

declare(strict_types=1);

namespace Bloatless\TemplateEngine;

require_once __DIR__ . '/TemplateEngineException.php';

class Lexer
{
    // token groups
    public const TG_COMMAND = 1;
    public const TG_BLOCK = 2;

    // token types
    public const TT_ECHO = 1;
    public const TT_IF = 2;
    public const TT_ELSE = 3;
    public const TT_ENDIF = 4;
    public const TT_ELSEIF = 5;
    public const TT_INCLUDE = 6;

    public function __invoke(string $viewContent): array
    {
        $tokens = $this->tokenize($viewContent);
        $tokens = $this->combineTokens($tokens);
        $tokens = $this->attachTokenContent($tokens, $viewContent);
        $tokens = $this->attachTokenType($tokens);

        return $tokens;
    }

    private function attachTokenType(array $tokens): array
    {
        foreach ($tokens as $i => $token) {
            if (str_starts_with($token['content'], '{{ $')) {
                $tokens[$i]['type'] = self::TT_ECHO;
                continue;
            }
            if (str_starts_with($token['content'], '{{ include(')) {
                $tokens[$i]['type'] = self::TT_INCLUDE;
                continue;
            }
            if (str_starts_with($token['content'], '{% if')) {
                $tokens[$i]['type'] = self::TT_IF;
                continue;
            }
            if (str_starts_with($token['content'], '{% elseif')) {
                $tokens[$i]['type'] = self::TT_ELSEIF;
                continue;
            }
            if ($token['content'] === '{% else %}') {
                $tokens[$i]['type'] = self::TT_ELSE;
                continue;
            }
            if ($token['content'] === '{% endif %}') {
                $tokens[$i]['type'] = self::TT_ENDIF;
                continue;
            }

            throw new TemplateEngineException(sprintf('Unknown token type. (%s)', $token['content']));
        }

        return $tokens;
    }

    private function attachTokenContent(array $tokens, string $viewContent): array
    {
        foreach ($tokens as $i => $token) {
            $contentLength = $token['offset_end'] - $token['offset_start'];
            $tokens[$i]['content'] = substr($viewContent, $token['offset_start'], $contentLength);
        }

        return $tokens;
    }

    private function combineTokens(array $tokens): array
    {
        $combined = [];
        $buffer = [];
        foreach ($tokens as $i => $token) {
            if ($token['group'] === self::TG_COMMAND) {
                if ($token['value'] === '{{') {
                    $buffer = $token;
                    continue;
                }
                if ($token['value'] === '}}') {
                    if ($buffer === []) {
                        throw new TemplateEngineException('Token Error: Found closing variable but opening is missing.');
                    }
                    $combined[] = [
                        'group' => self::TG_COMMAND,
                        'offset_start' => $buffer['offset'],
                        'offset_end' => $token['offset'],
                    ];
                    $buffer = [];
                    continue;
                }
            }

            if ($token['group'] === self::TG_BLOCK) {
                if ($token['value'] === '{%') {
                    $buffer = $token;
                    continue;
                }
                if ($token['value'] === '%}') {
                    if ($buffer === []) {
                        throw new TemplateEngineException('Token Error: Found closing block but opening is missing.');
                    }
                    $combined[] = [
                        'group' => self::TG_BLOCK,
                        'offset_start' => $buffer['offset'],
                        'offset_end' => $token['offset'],
                    ];
                    $buffer = [];
                    continue;
                }
            }

            $buffer = [];
        }

        return $combined;
    }

    private function tokenize(string $input): array
    {
        $tokens = [];
        $inputLength = mb_strlen($input);
        $pos = 0;
        $buffer = '';
        while ($pos < $inputLength) {
            $char = $input[$pos];
            if ($char === '{') {
                if ($buffer === '') {
                    $buffer = $char;
                    $pos++;
                    continue;
                }
                if ($buffer === '{') {
                    $tokens[] = [
                        'group' => self::TG_COMMAND,
                        'offset' => $pos - 1,
                        'value' => '{{'
                    ];
                    $buffer = '';
                    $pos++;
                    continue;
                }
            }

            if ($char === '%') {
                if ($buffer === '') {
                    $buffer = $char;
                    $pos++;
                    continue;
                }
                if ($buffer === '{') {
                    $tokens[] = [
                        'group' => self::TG_BLOCK,
                        'offset' => $pos - 1,
                        'value' => '{%'
                    ];
                    $buffer = '';
                    $pos++;
                    continue;
                }
            }

            if ($char === '}') {
                if ($buffer === '') {
                    $buffer = '}';
                    $pos++;
                    continue;
                }
                if ($buffer === '}') {
                    $tokens[] = [
                        'group' => self::TG_COMMAND,
                        'offset' => $pos + 1,
                        'value' => '}}'
                    ];
                    $buffer = '';
                    $pos++;
                    continue;
                }
                if ($buffer === '%') {
                    $tokens[] = [
                        'group' => self::TG_BLOCK,
                        'offset' => $pos + 1,
                        'value' => '%}'
                    ];
                    $buffer = '';
                    $pos++;
                    continue;
                }
            }

            $buffer = '';
            $pos++;
        }

        return $tokens;
    }
}
