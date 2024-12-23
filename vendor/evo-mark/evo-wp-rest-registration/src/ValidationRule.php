<?php

namespace EvoWpRestRegistration;

use DateTimeInterface;
use \WP_Error;
use WP_REST_Request;

class ValidationRule
{
    private string $definition;
    private array $arguments;
    private $messageCentre;
    private $param;
    private $value;
    private WP_REST_Request $request;
    public $error;
    public bool $skip = false;

    public function __construct($ruleItem, $param, $value, $messageCentre, $request)
    {
        $this->definition = $this->resolveDefinition($ruleItem);
        $this->arguments = $this->resolveArguments($ruleItem);
        $this->messageCentre = $messageCentre;
        $this->param = $param;
        $this->value = $value;
        $this->request = $request;

        if (method_exists($this, $this->definition)) {
            $this->{$this->definition}();
        }
    }

    private function convertSnakeToCamel(string $str): string
    {
        $words = explode('_', $str);
        $camelCaseString = $words[0];
        for ($i = 1; $i < count($words); $i++) {
            $camelCaseString .= ucfirst($words[$i]);
        }
        return $camelCaseString;
    }

    public function resolveDefinition(string $ruleItem): string
    {
        $exploded = explode(":", $ruleItem);
        $first = array_shift($exploded);
        return $this->convertSnakeToCamel($first);
    }

    public function resolveArguments(string $ruleItem): array
    {
        $exploded = explode(":", $ruleItem, 2);
        $argString = array_pop($exploded);
        return explode(",", $argString);
    }

    private function required()
    {
        if ($this->request->has_param($this->param) === false || empty($this->value)) $this->createError('required');
    }

    private function nullable()
    {
        if (empty($this->value)) $this->skip = true;
    }

    private function sometimes()
    {
        if ($this->request->has_param($this->param) === false) $this->skip = true;
    }

    private function accepted()
    {
        $acceptedValues = ['yes', 'on', '1', 'true'];

        if (!in_array(strtolower($this->value), $acceptedValues, true)) $this->createError('accepted');
    }

    private function file()
    {
        if (
            !$this->_isFile($this->param) ||
            !$this->_valueHasKeys($this->value, ['name', 'full_path', 'type', 'size', 'error']) ||
            $this->value['error']
        ) {
            $this->createError('file');
        }
    }

    private function _isFile($param)
    {
        $files = $this->request->get_file_params();
        $prefix = apply_filters(Hooks::FILE_PREFIX, "", $param, $files, $this->request);
        $param = $prefix . $param;
        return isset($files[$param]);
    }

    private function extensions()
    {
        $error = false;
        if (!isset($this->value['full_path'])) $error = true;

        if (!$error) {
            $fileInfo = pathinfo($this->value['full_path']);
            $extension = isset($fileInfo['extension']) ? $fileInfo['extension'] : '';
        }

        if ($error === true || !in_array($extension, $this->arguments)) $this->createError('extensions');
    }

    private function mimetypes()
    {
        if (!isset($this->value['type']) || in_array($this->value['type'], $this->arguments) === false) {
            $this->createError('mimetypes');
        }
    }

    private function string()
    {
        if (!is_string($this->value)) $this->createError('string');
    }

    private function lowercase()
    {
        if (strtolower($this->value) !== $this->value) $this->createError('lowercase');
    }

    private function uppercase()
    {
        if (strtoupper($this->value) !== $this->value) $this->createError('lowercase');
    }

    private function startsWith()
    {
        $found = false;
        $needles = is_iterable($this->arguments) ? $this->arguments : [$this->arguments];

        foreach ($needles as $needle) {
            if ((string) $needle !== '' && str_starts_with($this->value, $needle)) {
                $found = true;
                break;
            }
        }

        if (!$found) $this->createError('starts_with');
    }

    private function endsWith()
    {
        $found = false;
        $needles = is_iterable($this->arguments) ? $this->arguments : [$this->arguments];

        foreach ($needles as $needle) {
            if ((string) $needle !== '' && str_ends_with($this->value, $needle)) {
                $found = true;
                break;
            }
        }

        if (!$found) $this->createError('ends_with');
    }

    private function alphaUnderscore()
    {
        if ((!is_string($this->value) && !is_numeric($this->value)) || preg_match('/\A[a-zA-Z_]+\z/u', $this->value) === 0) {
            $this->createError('alpha_underscore');
        };
    }

    private function alphaDash()
    {
        if ((!is_string($this->value) && !is_numeric($this->value)) || preg_match('/\A[a-zA-Z-]+\z/u', $this->value) === 0) {
            $this->createError('alpha_dash');
        };
    }

    private function alphaNum()
    {
        if ((!is_string($this->value) && !is_numeric($this->value)) || preg_match('/\A[a-zA-Z0-9]+\z/u', $this->value) === 0) {
            $this->createError('alpha_num');
        };
    }

    private function hexColour()
    {
        if (preg_match('/^#(?:(?:[0-9a-f]{3}){1,2}|(?:[0-9a-f]{4}){1,2})$/i', $this->value) === 0) {
            $this->createError('hex_colour');
        }
    }

    private function numeric()
    {
        if (!is_numeric($this->value)) $this->createError('numeric');
    }

    private function min()
    {
        $bound = floatval($this->arguments[0] ?? $this->param);

        if ($this->_isFile($this->param)) {
            $value = floatval($this->value['size']) / 1024;
            if ($value < $bound) $this->createError('min_filesize');
        } else {
            $value = floatval($this->value);
            if ($value < $bound) $this->createError('min');
        }
    }

    private function max()
    {
        $bound = floatval($this->arguments[0] ?? $this->param);

        if ($this->_isFile($this->param)) {
            $value = floatval($this->value['size']) / 1024;
            if ($value > $bound) $this->createError('max_filesize');
        } else {
            $value = floatval($this->value);
            if ($value > $bound) $this->createError('max');
        }
    }

    private function array()
    {
        if (!is_array($this->value)) $this->createError('array');
    }

    private function in()
    {
        if (!in_array($this->value, $this->arguments)) $this->createError('in');
    }

    private function notIn()
    {
        if (in_array($this->value, $this->arguments)) $this->createError('not_in');
    }

    private function boolean()
    {
        if (!is_bool($this->value)) $this->createError('boolean');
    }

    private function email()
    {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) $this->createError('email');
    }

    private function json()
    {
        if (is_null(json_decode($this->value))) $this->createError('json');
    }

    private function url()
    {
        $filters = [FILTER_VALIDATE_URL];
        if (in_array('path', $this->arguments)) $filters[] = FILTER_FLAG_PATH_REQUIRED;
        if (in_array('query', $this->arguments)) $filters[] = FILTER_FLAG_QUERY_REQUIRED;
        if (!filter_var($this->value, ...$filters)) $this->createError('url');
    }

    private function exists()
    {
        global $wpdb;

        $column = $this->arguments[1] ?? $this->param;
        $table = $wpdb->prefix . $this->arguments[0];

        $prepared = $wpdb->prepare("SELECT COUNT(*) FROM `$table` WHERE `$column` = %s", [$this->value]);

        $count = intval($wpdb->get_var($prepared));
        if ($count === 0) {
            $this->createError('exists');
        }
    }

    private function unique()
    {
        global $wpdb;

        $table = $wpdb->prefix . $this->arguments[0];
        $column = $this->arguments[1] ?? $this->param;
        $ignore = $this->arguments[2] ?? null;

        $prepared = $wpdb->prepare("SELECT COUNT(*) FROM `$table` WHERE `$column` = %s", [$this->value]);
        if ($ignore) {
            $prepared .= " AND `id` != " . $this->request->get_param('id');
        }

        $count = intval($wpdb->get_var($prepared));
        if ($count > 0) {
            $this->createError('unique');
        }
    }

    private function date()
    {
        if ($this->value instanceof DateTimeInterface) {
            return true;
        }

        try {
            if ((! is_string($this->value) && ! is_numeric($this->value)) || strtotime($this->value) === false) {
                return $this->createError('date');
            }
        } catch (\Exception) {
            return $this->createError('date');
        }

        $date = date_parse($this->value);

        if (!checkdate($date['month'], $date['day'], $date['year'])) {
            $this->createError('date');
        }
    }

    private function _valueHasKeys(array $value, array $keys)
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $value)) {
                return false;
            }
        }
        return true;
    }

    private function createError($ruleName)
    {
        $this->error = new \WP_Error('validation_failed', $this->formatMessage($ruleName));
    }

    private function formatMessage(string $ruleName, array $replacers = []): string
    {
        $message = apply_filters(Hooks::PRE_VALIDATION_MESSAGE, $this->messageCentre->messages[$ruleName], $this->param, $ruleName);
        $replacers = array_merge($replacers, ['param' => ucwords(str_replace('_', ' ', $this->param)), 'value' => $this->value, 'args' => implode(", ", $this->arguments)]);
        foreach ($replacers as $key => $replacer) {
            if (is_string($replacer) === false) {
                if (is_array($replacer) && empty($replacer)) {
                    $replacer = "";
                } else if (is_array($replacer) || is_object($replacer)) {
                    $replacer = json_encode($replacer);
                }
            }
            $message = str_replace("%" . $key . "%", $replacer ?? "", $message);
        }
        return apply_filters(Hooks::VALIDATION_MESSAGE, $message, $this->param, $ruleName);
    }
}
