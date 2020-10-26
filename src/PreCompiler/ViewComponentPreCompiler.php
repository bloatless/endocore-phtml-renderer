<?php

declare(strict_types=1);

namespace Bloatless\Endocore\Components\PhtmlRenderer\PreCompiler;

class ViewComponentPreCompiler implements PreCompilerInterface
{
    public function compile(string $viewContent, array $templateVariables = []): string
    {
        $viewContent = $this->parseSelfClosingTags($viewContent);
        $viewContent = $this->parseOpenCloseTags($viewContent);

        return $viewContent;
    }

    private function parseSelfClosingTags($content): string
    {
        $cnt = preg_match_all('/<vc-(?<component>[\w-]+)(?<attributes>\s[^>]*)?\/>/Us', $content, $matches, PREG_SET_ORDER);
        if ($cnt === 0) {
            return $content;
        }

        $tagCounts = [];
        foreach ($matches as $match) {
            $componentName = $match['component'];
            if (!isset($tagCounts[$componentName])) {
                $tagCounts[$componentName] = 0;
            }
            $attributes = $match['attributes'] ?? '';
            $attributes = trim($attributes);
            $attributes = (!empty($attributes)) ? ' ' . $attributes : $attributes;
            $componentHash = $this->getComponentHash($componentName, 0, $tagCounts[$componentName]);
            $uniqueTag = sprintf(
                '<?php $this->call(\'viewComponent\', [\'hash\' => \'%s\', \'type\' => \'%s\', \'action\' => \'start\']); ?>',
                $componentHash,
                $componentName,
            );
            $uniqueTag .= sprintf(
                '<?php $this->call(\'viewComponent\', [\'hash\' => \'%s\', \'type\' => \'%s\', \'action\' => \'end\']); ?>',
                $componentHash,
                $componentName,
            );
            $tagPattern = '#' . preg_quote($match[0]) . '#';
            $content = preg_replace($tagPattern, $uniqueTag, $content, 1);
            $tagCounts[$componentName]++;
        }

        return $content;
    }

    private function parseOpenCloseTags($content): string
    {
        // collect opening tags
        $openingTagsCount = preg_match_all(
            '/<vc-(?<component>[\w-]+)(?<attributes>\s[^>]*)?>/Us',
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

        // @todo handle opening/closing tag-count mismatch

        $tags = [];
        foreach ($openingTags as $item) {
            $tags[] = (object) [
                'tag' => $item[0][0],
                'offset' => $item[0][1],
                'component' => $item['component'][0],
                'attributes' => $item['attributes'][0] ?? '',
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
            $componentHash = $this->getComponentHash($tag->component, $tag->level, $tag->levelItem);
            if ($tag->type === 'opening') {
                // @todo handle attributes
                $uniqueTag = sprintf(
                    '<?php $this->call(\'viewComponent\', [\'hash\' => \'%s\', \'type\' => \'%s\', \'action\' => \'start\']); ?>',
                    $componentHash,
                    $tag->component,
                );
            }
            if ($tag->type === 'closing') {
                $uniqueTag = sprintf(
                    '<?php $this->call(\'viewComponent\', [\'hash\' => \'%s\', \'type\' => \'%s\', \'action\' => \'end\']); ?>',
                    $componentHash,
                    $tag->component,
                );
            }
            $tagPattern = '#' . preg_quote($tag->tag) . '#';
            $content = preg_replace($tagPattern, $uniqueTag, $content, 1);
        }

        return $content;
    }

    private function getComponentHash(string $componentType, int $level, int $item): string
    {
        return md5(implode('|', [$componentType, $level, $item]));
    }
}
