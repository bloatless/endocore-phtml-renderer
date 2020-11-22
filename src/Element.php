<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer;

class Element
{
    private string $name = '';

    private string $content = '';

    private array $attributes = [];

    public function __construct(string $name, string $content, array $attributes = [])
    {
        $this->setName($name);
        $this->setContent($content);
        $this->setAttributes($attributes);
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
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

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function hasAttribute(string $attributeName): bool
    {
        return isset($this->attributes[$attributeName]);
    }

    public function getAttribute(string $attributeName, $default = null)
    {
        if ($this->hasAttribute($attributeName)) {
            return $attributeName;
        }

        return $default;
    }

    public function setAttribute(string $attributeName, $attributeValue): void
    {
        $this->attributes[$attributeName] = $attributeValue;
    }
}