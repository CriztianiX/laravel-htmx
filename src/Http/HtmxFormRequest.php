<?php

declare(strict_types=1);

namespace Mauricius\LaravelHtmx\Http;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Precognition;
use Mauricius\LaravelHtmx\Http\Concerns\HasHtmxRequest;

class HtmxFormRequest extends FormRequest
{
    use HasHtmxRequest;

    public function validateResolved()
    {
        // Do only validate htmx requests
        if ($this->isMethod('post')) {
            $this->prepareForValidation();

            if (! $this->passesAuthorization()) {
                $this->failedAuthorization();
            }

            $instance = $this->getValidatorInstance();

            if ($this->isPrecognitive()) {
                $instance->after(Precognition::afterValidationHook($this));
            }

            if ($instance->fails()) {
                $this->failedValidation($instance);
            }

            $this->passedValidation();
        }
    }
}
