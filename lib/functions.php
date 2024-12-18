<?php

use EvoMark\InertiaWordpress\Container;
use EvoMark\InertiaWordpress\Request\RequestHandler;

if (!function_exists('inertia_head')) {
    function inertia_head()
    {
        $container = Container::getInstance();
        $handler = $container->get('requestHandler');
        $handler->head();
    }
}

if (!function_exists('inertia_body')) {
    function inertia_body($args = [])
    {
        $container = Container::getInstance();
        $handler = $container->get('requestHandler');
        $handler->body($args);
    }
}

if (!function_exists('getallheaders')) {
    /**
     * This is a reproduction of the apache_response_headers() PHP function in case it's not available natively.
     */
    function getallheaders()
    {
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }
}

if (!function_exists('inertia_request')) {
    function inertia_request(): RequestHandler
    {
        $container = Container::getInstance();
        return $container->get('requestHandler');
    }
}
