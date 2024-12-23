<?php

namespace EvoMark\WpVite;

class WpVite
{
    public $type;
    public $vite;
    public $uploadsPath;
    public $uploadsUrl;
    public static $init = false;
    public bool $hasAbsolutes = false;

    public function __construct()
    {
        $this->uploadsPath  = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . 'scw-vite-hmr';
        $this->uploadsUrl  = wp_upload_dir()['baseurl'] . '/' . 'scw-vite-hmr';

        if (!file_exists($this->uploadsPath)) wp_mkdir_p($this->uploadsPath);

        $this->setupFilters();
    }

    public function getVite(): ViteAdapter
    {
        return $this->vite;
    }

    /**
     * Main enqueue function for both styles and scripts
     *
     * The $args array should have the following structure:
     * array(
     *     'input' => (string) Description of option1,
     *     'namespace' => (string) Description of option1,
     *     'buildDirectory' => (int) Description of option2,
     *     'absolutePath' => (string) Manually override the entire path
     *     'absoluteUrl' => (string) Manually override the entire URL
     *     'hotFile' => (string) Filename to use for the hotfile
     *     'dependencies' => (array) List of dependencies
     *     'admin' => (bool) Only enqueue the asset on admin pages
     *     'react' => (bool) Use the react dev server in HMR mode
     * )
     *
     * @param array $args The configuration options
     */
    public function enqueue($args = [])
    {
        $this->validateArgs($args);

        if ($this->hasAbsolutes) {
            $this->uploadsPath = $args['absolutePath'];
            $this->uploadsUrl = $args['absoluteUrl'];
        } else {
            $this->uploadsPath = $this->uploadsPath . DIRECTORY_SEPARATOR  . $args['namespace'];
            $this->uploadsUrl = $this->uploadsUrl . "/" . $args['namespace'];
        }

        $buildDirectory = $args['buildDirectory'] ?? 'build';

        if (!file_exists($this->uploadsPath)) {
            try {
                wp_mkdir_p($this->uploadsPath);
            }
            catch (\Exception $e) {
                throw new \Exception("Directory \"" . $this->uploadsPath . "\" could not be created. Please ensure that your frontend build process is outputting to the same path.");
            }
        }

        $this->vite = new ViteAdapter([
            'uploadsPath' => $this->uploadsPath,
            'uploadsUrl' => $this->uploadsUrl,
            'hotFile' => $args['hotFile'] ?? 'hot',
            'dependencies' => $args['dependencies'] ?? [],
            'namespace' => $args['namespace'],
            'entryHandle' => $args['entryHandle'] ?? "",
            'useReact' => $args['react'] ?? false
        ]);

        $hook = isset($args['admin']) && $args['admin'] === true ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts';

        $callback = function () use ($buildDirectory, $args) {
            $inputs = is_array($args['input']) ? $args['input'] : (array) $args['input'];
            if (count($inputs) === 0) {
                throw new \Exception("No valid input files received");
            }
            foreach ($inputs as $input) {
                echo $this->vite->generateTags($input, $buildDirectory);
            }
        };

        // If we're already in the needed hook, attempt to enqueue anyway
        if (
            did_action($hook) && 
            ! did_action('wp_head') && 
            ! did_action('admin_print_scripts')
        ) {
            $callback();
        } else {
            add_action($hook, $callback, $args['priority'] ?? 10);
        }

    }

    /**
     * Ensure that user has provided sensible config settings that adhere to the needed types
     *
     * @param  array $args
     * @return bool
     */
    public function validateArgs(array $args): bool
    {
        $this->checkAbsolutes($args);
        $this->checkInput($args);
        $this->checkNamespace($args);
        $this->checkPriority($args);
        $this->checkAdmin($args);
        $this->checkDependencies($args);
        return true;
    }

    public function setupFilters(): void
    {
        if (self::$init === true) return;

        add_filter('script_loader_tag', [$this, 'addScriptAttributes'], 10, 2);
        self::$init = true;
    }

    /**
     * Remove deprecated script/link attributes and add attributes from registered sources
     *
     * @filter script_loader_tag
     */
    public function addScriptAttributes(string $tag, string $handle): string
    {
        $attributes = ['type', 'async', 'crossorigin', 'defer', 'fetchpriority', 'integrity', 'nomodule', 'nonce', 'referrerpolicy', 'blocking'];
        $tag = preg_replace("/type=['\"]text\/(javascript|css)['\"]/", '', $tag);
        foreach ($attributes as $attribute) {
            $data = wp_scripts()->get_data($handle, $attribute);
            if (!empty($data)) {
                $tag = str_replace('src', $attribute . '="' . esc_attr($data) . '" src', $tag);
            }
        }

        return $tag;
    }

    private function checkAbsolutes(array $args): void
    {
        if (isset($args['absolutePath']) || isset($args['absoluteUrl'])) {
            $this->hasAbsolutes = true;
        }

        if ($this->hasAbsolutes && (!isset($args['absolutePath']) || !isset($args['absoluteUrl']))) {
            throw new \Exception("You must pass both 'absolutePath' and 'absoluteUrl' to use manual definitions");
        }
    }

    private function checkInput(array $args): void
    {
        if (!isset($args['input'])) {
            throw new \Exception("No 'input' found");
        }
    }

    private function checkNamespace(array $args): void
    {
        if ((!$this->hasAbsolutes && empty($args['namespace'])) ||
            ($this->hasAbsolutes && empty($args['namespace']))
        ) {
            throw new \Exception("A 'namespace' is required");
        }
    }

    private function checkPriority(array $args): void
    {
        if (isset($args['priority']) && !is_int($args['priority'])) {
            throw new \Exception("Priority must be an integer");
        }
    }

    private function checkAdmin(array $args): void
    {
        if (isset($args['admin']) && !is_bool($args['admin'])) {
            throw new \Exception("Admin argument must be a boolean");
        }
    }

    private function checkDependencies(array $args): void
    {
        if (isset($args['dependencies']) && !is_array($args['dependencies'])) {
            throw new \Exception("Dependencies must be an array");
        }
    }

    public static function __callStatic($method, $args)
    {
        $instance = new static;
        return $instance->$method(...$args);
    }
}
