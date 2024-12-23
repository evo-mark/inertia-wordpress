<?php

namespace EvoWpRestRegistration;

class ValidationMessages
{
    public $messages = [];

    public function __construct(array $userMessages = [])
    {
        $this->messages = apply_filters(Hooks::COMPILE_VALIDATION_MESSAGES, array_merge($this->defaults(), $userMessages));
    }

    private function defaults()
    {
        return [
            'required' => '%param% is required',
            'string' => '%param% must be a string',
            'accepted' => 'You must accept the %param%',
            'numeric' => '%param% must be numeric',
            'array' => '%param% must be an array',
            'boolean' => '%param% must be true or false',
            'email' => 'Valid email address is required',
            'unique' => '%value% already exists as a %param%',
            'exists' => '%param% must exist on %args%',
            'in' => '%param% must be one of %args%',
            'not_in' => '%param% must be not be any of %args%',
            'json' => '%param% must be a valid JSON string',
            'url' => '%param% must be a valid URL',
            'min' => '%param% must be at least %args%',
            'min_filesize' => '%param% must be at least %args% KB',
            'max' => '%param% must be no more than %args%',
            'max_filesize' => '%param% must be less than %args% KB',
            'lowercase' => '%param% must be lowercase',
            'uppercase' => '%param% must be uppercase',
            'starts_with' => '%param% must start with one of the following: %args%',
            'ends_with' => '%param% must end with one of the following: %args%',
            'alpha_underscore' => '%param% can only contain letters and underscores',
            'alpha_dash' => '%param% can only contain letters and hyphens',
            'alpha_num' => '%param% can only contain letters and numbers',
            'hex_colour' => '%param% must be a hex colour',
            'file' => '%param% must be a valid file',
            'extensions' => '%param% must be a file with extension of %args%',
            'mimetypes' => '%param% must be a file with a mime type of %args%',
            'date' => '%param% must be a valid date'
        ];
    }
}
