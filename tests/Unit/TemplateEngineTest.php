<?php

namespace Bloatless\TemplateEngine\Tests;

require_once SRC_ROOT . '/TemplateEngineFactory.php';

use Bloatless\TemplateEngine\TemplateEngineFactory;
use PHPUnit\Framework\TestCase;

class TemplateEngineTest extends TestCase
{
    public function testRender()
    {
        $config = include __DIR__ . '/../Fixtures/config.php';
        $factory = new TemplateEngineFactory($config);
        $engine = $factory->make();
        $foo = $engine->render('example.tmpl', [
            'foo' => 'Hello World!',
            'bar' => 'Hello Universe!',
        ]);

        print_r($foo);
    }
}