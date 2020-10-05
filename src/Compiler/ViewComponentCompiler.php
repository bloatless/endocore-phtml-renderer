<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer\Compiler;

class ViewComponentCompiler implements CompilerInterface
{
    private $viewComponents = [];

    private $vcReplacements = [];

    public function setViewComponents(array $viewComponents): void
    {
        $this->viewComponents = $viewComponents;
    }

    public function compile(string $content, array $templateVariables = []): string
    {
        $content = $this->parseOpenCloseTags($content);

        // parse single-tag components
        $this->compileSelfClosingTags($content);

        // parse multi-tag components
        $this->compileOpenCloseTags($content);
        $content = strtr($content, $this->vcReplacements);

        return $content;
    }

    private function parseOpenCloseTags($content): string
    {
        // collect opening tags
        $openingTagsCount = preg_match_all(
            '/<vc-(?<component>[\w-]+)>/Us',
            $content,
            $openingTags,
            PREG_OFFSET_CAPTURE|PREG_SET_ORDER
        );

        // collect closing tags
        $closingTagsCount = preg_match_all(
            '/<\/vc-(?<component>[\w-]+)>/Us',
            $content,
            $closingTags,
            PREG_OFFSET_CAPTURE|PREG_SET_ORDER
        );

        $tags = [];
        foreach ($openingTags as $item) {
            $tags[] = (object) [
                'tag' => $item[0][0],
                'offset' => $item[0][1],
                'component' => $item['component'][0],
                'type' => 'opening',
            ];
        }
        foreach ($closingTags as $item) {
            $tags[] = (object) [
                'tag' => $item[0][0],
                'offset' => $item[0][1],
                'component' => $item['component'][0],
                'type' => 'closing',
            ];
        }

        // order by offset
        usort($tags, function ($a, $b) {
            return $a->offset <=> $b->offset;
        });

        // add level and count information
        $levels = [];
        $itemCounts = [];
        foreach ($tags as $i => $tag) {
            $component = $tag->component;
            if (!isset($levels[$component])) {
                $levels[$component] = 0;
            }
            if (!isset($itemCounts[$component])) {
                $itemCounts[$component] = [];
            }

            if ($tag->type === 'opening') {
                // add level
                $level = $levels[$component];
                $tags[$i]->level = $level;
                $levels[$component]++;

                // add count on level
                if (!isset($itemCounts[$component][$level])) {
                    $itemCounts[$component][$level] = 0;
                }
                $tags[$i]->levelItem = $itemCounts[$component][$level];
            }
            if ($tag->type === 'closing') {
                $levels[$component]--;
                $level = $levels[$component];
                $tags[$i]->level = $level;

                $tags[$i]->levelItem = $itemCounts[$component][$level];
                $itemCounts[$component][$level]++;

            }
        }

        // replace component tag with unique tags
        foreach ($tags as $tag) {
            if ($tag->type === 'opening') {
                $uniqueTag = sprintf('<vc-%s-#l%d#i%d>', $tag->component, $tag->level, $tag->levelItem);
            }
            if ($tag->type === 'closing') {
                $uniqueTag = sprintf('</vc-%s-#l%d#i%d>', $tag->component, $tag->level, $tag->levelItem);
            }
            $tagPattern = '#' . preg_quote($tag->tag) . '#';
            $content = preg_replace($tagPattern, $uniqueTag, $content, 1);
        }

        return $content;
    }

    private function compileSelfClosingTags($content): void
    {
        $cnt = preg_match_all('/<vc-([\w-]+)\s?\/>/Us', $content, $matches, PREG_SET_ORDER);
        if ($cnt === 0) {
            return;
        }
    }

    private function compileOpenCloseTags($content): void
    {
        $cnt = preg_match_all(
            '/<vc-((?<component>[\w-]+)-#l[0-9]+#i[0-9]+)>(?<content>.*)<\/vc-\1>/Us',
            $content,
            $matches,
            PREG_SET_ORDER
        );
        if ($cnt === 0) {
            return;
        }

        foreach ($matches as $match) {
            $componentName = $match['component'];
            if (!isset($this->viewComponents[$componentName])) {
                // @todo Error: Unknown view component
                continue;
            }
            $componentClass = $this->viewComponents[$componentName];
            $viewComponent = new $componentClass;
            $componentHtml = $viewComponent->render($match['content']);
            $this->vcReplacements[$match[0]] = $componentHtml;
        }
    }
}
