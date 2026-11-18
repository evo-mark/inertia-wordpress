<?php

/**
 * Minimal doubles for WordPress core classes that the unit suite needs to
 * instantiate. They only implement the surface the plugin actually touches.
 */

if (! class_exists('WP_Admin_Bar')) {
    /**
     * Shared as `wp.adminBar` on Inertia visits. Like core, it always renders the wrapper.
     */
    class WP_Admin_Bar
    {
        public function render()
        {
            echo '<div id="wpadminbar"><ul></ul></div>';
        }
    }
}

if (! class_exists('WP_Query')) {
    class WP_Query
    {
        /** @var array<string, mixed> */
        public $query_vars = [];

        /** @var array<string, mixed> */
        public $query = [];

        /** @var int */
        public $max_num_pages = 0;

        /** @var int */
        public $found_posts = 0;

        /** @var array<int, mixed> */
        public $posts = [];

        /**
         * @param array<string, mixed> $query
         */
        public function __construct($query = [])
        {
            $this->query = $query;
            $this->query_vars = $query;
        }

        public function get($query_var, $default_value = '')
        {
            return $this->query_vars[$query_var] ?? $default_value;
        }
    }
}
