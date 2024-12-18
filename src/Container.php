<?php

namespace EvoMark\InertiaWordpress;

use DI\ContainerBuilder;

final class Container
{
    protected $container;
    protected static $instance;

    protected function __construct()
    {
        $builder = new ContainerBuilder();
        $this->container = $builder->build();
    }

    public static function getInstance()
    {
        if (null == static::$instance) {
            static::$instance = new static();
        }

        return (static::$instance)->container;
    }
}
