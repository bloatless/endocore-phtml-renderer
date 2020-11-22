<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer;

abstract class Component
{
    protected PhtmlRenderer $phtmlRenderer;

    protected string $content = '';

    protected array $attributes = [];

    protected array $elements = [];

    public function __construct(PhtmlRenderer $phtmlRenderer)
    {
        $this->phtmlRenderer = $phtmlRenderer;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function addElement(string $name, Element $element)
    {
        $this->elements[$name] = $element;
    }

    public function getElement(string $name): ?Element
    {
        return $this->elements[$name] ?? null;
    }

    public function hasElement(string $elementName): bool
    {
        return isset($this->elements[$elementName]);
    }

    public function start(): string
    {
        ob_start();

        return '';
    }

    public function end(): string
    {
        $rawContent = ob_get_clean();
        $content = $this->stripElementsFromContent($rawContent);
        $this->setContent($content);

        return $this->__invoke();
    }

    protected function render(string $viewName, array $templateVariables = []): string
    {
        return $this->phtmlRenderer->render($viewName, $templateVariables);
    }

    private function stripElementsFromContent(string $content): string
    {
        $elCount = preg_match_all(
            '/<el-(?<name>[\w-]+)(?<attributes>\s[^>]*)?>(?<content>.*)<\/el-\1>/Us',
            $content,
            $matches,
            PREG_SET_ORDER
        );
        if ($elCount === 0) {
            return $content;
        }

        foreach ($matches as $match) {
            $elAttributes = $this->getAttributes($match['attributes']);
            $this->addElement(
                $match['name'],
                (new Element($match['name'], $match['content'], $elAttributes))
            );
            $content = str_replace($match[0], '', $content);
        }

        return $content;
    }

    private function getAttributes(string $attributeString): array
    {
        if (empty($attributeString)) {
            return [];
        }
        $attrCount = preg_match_all('/([\w:-]+)="([^"]+)"/Us', $attributeString, $attributeMatches, PREG_SET_ORDER);
        if ($attrCount === 0) {
            return [];
        }

        $attributes = [];
        foreach ($attributeMatches as $attr) {
            $attributes[$attr[1]] = $attr[2];
        }

        return $attributes;
    }

    abstract public function __invoke(): string;
}
