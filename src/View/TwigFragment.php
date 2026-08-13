<?php

declare(strict_types=1);

namespace Mauricius\LaravelHtmx\View;

use TwigBridge\Facade\Twig;

class TwigFragment
{
    public static function render(string $view, string $fragment, array $data = []): string
    {
        return Twig::load($view)->renderBlock($fragment, $data);
    }
}
