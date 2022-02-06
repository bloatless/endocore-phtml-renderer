<?php

declare(strict_types=1);

namespace Bloatless\TemplateEngine;

require_once __DIR__ . '/Compiler.php';
require_once __DIR__ . '/Lexer.php';
require_once __DIR__ . '/Parser.php';
require_once __DIR__ . '/TemplateEngine.php';
require_once __DIR__ . '/TemplateEngineException.php';
require_once __DIR__ . '/ViewRenderer.php';

class TemplateEngineFactory
{
    protected array $config = [];

    public function __construct(array $config)
    {
        if (!isset($config['templates'])) {
            throw new TemplateEngineException('Invalid config. Key "templates" is missing. Check config file!');
        }
        if (empty($config['templates']['path_views'])) {
            throw new TemplateEngineException('Invalid value for "path_views". Check config file!');
        }
        if (empty($config['templates']['path_compile'])) {
            throw new TemplateEngineException('Invalid value for "path_compile". Check config file!');
        }

        $this->config = $config['templates'];
    }

    public function make(): TemplateEngine
    {
        $lexer = new Lexer();
        $parser = new Parser();
        $compiler = new Compiler(
            $this->config['path_compile']
        );
        $viewRenderer = new ViewRenderer();

        $templateEngine = new TemplateEngine(
            $this->config,
            $lexer,
            $parser,
            $compiler,
            $viewRenderer
        );

        return $templateEngine;
    }
}
