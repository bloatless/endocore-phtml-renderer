<?php

declare(strict_types=1);

namespace Bloatless\TemplateEngine\Node;

use Bloatless\TemplateEngine\TemplateEngine;

abstract class AbstractNode
{
    protected string $token = '';

    protected int $tokenStartPos = 0;

    protected int $tokenEndPos = 0;

    protected int $tokenLength = 0;

    protected string $tokenPlaceholder = '';

    protected TemplateEngine $templateEngine;

    public function __construct(TemplateEngine $templateEngine, array $token)
    {
        $this->token = $token['content'];
        $this->tokenStartPos = $token['offset_start'];
        $this->tokenEndPos = $token['offset_end'];
        $this->tokenLength = $this->tokenEndPos - $this->tokenStartPos;
        $this->setTemplateEngine($templateEngine);
    }

    abstract public function compile(string $viewContent): string;

    public function setTemplateEngine(TemplateEngine $templateEngine): void
    {
        $this->templateEngine = $templateEngine;
    }

    public function makeUnique(string $viewContent): string
    {
        $this->tokenPlaceholder = $this->generateRandomString($this->tokenLength);

        return substr_replace($viewContent, $this->tokenPlaceholder, $this->tokenStartPos, $this->tokenLength);
    }

    protected function replaceToken(string $viewContent, string $replacement): string
    {
        return str_replace($this->tokenPlaceholder, $replacement, $viewContent);
    }

    private function generateRandomString(int $length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
