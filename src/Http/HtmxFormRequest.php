<?php

declare(strict_types=1);

namespace Mauricius\LaravelHtmx\Http;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Precognition;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Mauricius\LaravelHtmx\Http\Concerns\HasHtmxRequest;
use Mauricius\LaravelHtmx\RequestStore;

class HtmxFormRequest extends FormRequest
{
    use HasHtmxRequest;

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return !$this->validator->fails();
    }

    public function errors(): MessageBag
    {
        return $this->validator->errors();
    }

    /**
     * @inheritDoc
     */
    public function validated($key = null, $default = null)
    {
        $results = [];
        $missingValue = new \stdClass;

        foreach ($this->validator->getRules() as $field => $rules) {
            $value = data_get($this->validator->getData(), $field, $missingValue);

            if ($this->validator->excludeUnvalidatedArrayKeys &&
                (in_array('array', $rules) || in_array('list', $rules)) &&
                $value !== null &&
                ! empty(preg_grep('/^'.preg_quote($field, '/').'\.+/', array_keys($this->validator->getRules())))) {
                continue;
            }

            if ($value !== $missingValue) {
                Arr::set($results, $field, $value);
            }
        }

        $reflection = new \ReflectionMethod($this->validator, 'replacePlaceholders');
        $reflection->setAccessible(true);
        $results = $reflection->invoke($this->validator, $results);

        if($key) {
            return data_get($results, $key, $default);
        }

        return $results;
    }

    public function validateResolved()
    {
        // Do only validate htmx requests
        if ($this->isHtmxRequest() && $this->isMethod('post')) {
            $this->prepareForValidation();

            if (! $this->passesAuthorization()) {
                $this->failedAuthorization();
            }

            $instance = $this->getValidatorInstance();

            if ($this->isPrecognitive()) {
                $instance->after(Precognition::afterValidationHook($this));
            }

            if ($instance->fails()) {
                return $this->failedValidation($instance);
            }

            $this->passedValidation();
        }
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        // Save errors only on post requests
        if ($this->isMethod('post')) {
            app(RequestStore::class)->store('errors', $validator->errors());
            app(RequestStore::class)->flashInput($this->all());
        }
    }
}
