<?php

namespace EvoWpRestRegistration;


class Validation
{
    protected $rules;
    protected $args;
    public $validated;
    public $request;
    protected $messageCentre;

    public function __construct($rules, $args, $messages)
    {
        $this->validated = [];
        $this->rules = $rules;
        $this->args = $args;
        $this->messageCentre = $messages;
    }

    public function createValidationCallback()
    {
        // Loop through rules and create a `validate_callback` callable
        foreach ($this->rules as $key => $ruleSet) {
            $this->args[$key]['required'] = in_array('required', $ruleSet) && !in_array('sometimes', $ruleSet);
            $this->args[$key]['validate_callback'] = $this->validationCallback($ruleSet);
            $this->args[$key]['sanitize_callback'] = $this->sanitisationCallback($ruleSet);
        }
        return $this->args;
    }

    /**
     * Generates a callback that is used as the `validate_callback` property on the arg
     *
     * Seems to only be called when a value is set on the request
     */
    public function validationCallback(array $ruleSet): callable
    {
        return function ($value, $request, $param) use ($ruleSet) {
            $value = $this->prepareValue($value, $param, $ruleSet);
            foreach ($ruleSet as $ruleItem) {
                $ruleResolver = new ValidationRule($ruleItem, $param, $value, $this->messageCentre, $request);
                if ($ruleResolver->error) {
                    return $ruleResolver->error;
                } else if ($ruleResolver->skip === true) {
                    break;
                }
            }
            // Set the value on the validated array for use in the controller
            $this->validated[$param] = $value;
        };
    }

    /**
     * ValidationCallback isn't run on null values, so we use this callback to register them
     */
    public function sanitisationCallback(array $ruleSet): callable
    {
        return function ($value, $request, $param) use ($ruleSet) {
            if (in_array('nullable', $ruleSet)) {
                if ((in_array('string', $ruleSet) || in_array('integer', $ruleSet) || in_array('date', $ruleSet)) && !$value) {
                    $this->validated[$param] = null;
                }
            }

            return $value;
        };
    }

    private function prepareValue($value, $param, $ruleSet)
    {
        // Handle a string 'null' value such as might be sent by a file field
        if ($value === 'null') {
            $value = null;
        }

        if (in_array('array', $ruleSet) && is_string($value) === true) {
            $value = array_filter(explode(",", $value));
        } else if (in_array('boolean', $ruleSet)) {
            $value = boolval($value);
        } else if (in_array('string', $ruleSet)) {
            $value = strval($value);
        }

        return apply_filters(Hooks::PREPARE_VALIDATION_VALUE, $value, $param, $ruleSet);
    }
}
