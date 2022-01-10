<?php

declare(strict_types=1);

namespace Bloatless\TemplateEngine;

require_once __DIR__ . '/TemplateEngineException.php';

class Lexer
{
    public const TOKEN_VARIABLE = 1;
    public const TOKEN_BLOCK = 2;

    public function __invoke(string $viewContent): array
    {
        $tokens = $this->tokenize($viewContent);
        $tokens = $this->combineTokens($tokens);
        $tokens = $this->attachTokenContent($tokens, $viewContent);

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
            if ($token['type'] === self::TOKEN_VARIABLE) {
                if ($token['value'] === '{{') {
                    $buffer = $token;
                    continue;
                }
                if ($token['value'] === '}}') {
                    if ($buffer === []) {
                        throw new TemplateEngineException('Token Error: Found closing variable but opening is missing.');
                    }
                    $combined[] = [
                        'type' => self::TOKEN_VARIABLE,
                        'offset_start' => $buffer['offset'],
                        'offset_end' => $token['offset'],
                    ];
                    $buffer = [];
                    continue;
                }
            }

            if ($token['type'] === self::TOKEN_BLOCK) {
                if ($token['value'] === '{%') {
                    $buffer = $token;
                    continue;
                }
                if ($token['value'] === '%}') {
                    if ($buffer === []) {
                        throw new TemplateEngineException('Token Error: Found closing block but opening is missing.');
                    }
                    $combined[] = [
                        'type' => self::TOKEN_BLOCK,
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
                        'type' => self::TOKEN_VARIABLE,
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
                        'type' => self::TOKEN_BLOCK,
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
                        'type' => self::TOKEN_VARIABLE,
                        'offset' => $pos + 1,
                        'value' => '}}'
                    ];
                    $buffer = '';
                    $pos++;
                    continue;
                }
                if ($buffer === '%') {
                    $tokens[] = [
                        'type' => self::TOKEN_BLOCK,
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
