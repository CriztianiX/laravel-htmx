<?php

declare(strict_types=1);

namespace Mauricius\LaravelHtmx\View;

use Illuminate\Support\Facades\View;

class FragmentResolver
{
    public function __construct() {}

    public function render($view, $fragment, array $data): string
    {
        $path = View::getFinder()->find($view);
        $engine = View::getEngineFromPath($path);

        if(get_class($engine) === 'TwigBridge\Engine\Twig') {
            return TwigFragment::render($view, $fragment, $data);
        }

        return BladeFragment::render($view, $fragment, $data);
    }
}
