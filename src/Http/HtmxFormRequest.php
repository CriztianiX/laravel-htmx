<?php

declare(strict_types=1);

namespace Mauricius\LaravelHtmx\Http;

use Illuminate\Foundation\Http\FormRequest;
use Mauricius\LaravelHtmx\Http\Concerns\HasHtmxRequest;

class HtmxFormRequest extends FormRequest
{
    use HasHtmxRequest;
}
