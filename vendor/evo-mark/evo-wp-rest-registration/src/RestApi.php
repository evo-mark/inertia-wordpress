<?php

namespace EvoWpRestRegistration;

class RestApi
{
    public $base_url;
    public $version;
    public $namespace;
    public $directory;

    public function __construct(array $args = [])
    {
        $required = ["base_url", "namespace", "directory"];
        foreach ($required as $item) {
            if (!isset($args[$item]) || empty($args[$item])) {
                throw new \Exception("Evo WP Rest Registration: no " . $item . " provided");
            }
        }

        $this->base_url = $args['base_url'];
        $this->version = $args['version'] ?? 1;
        $this->namespace = self::formatNamespace($args['namespace']);
        $this->directory = self::formatDir($args['directory']);

        add_action('rest_api_init', [$this, 'registerRoutes']);
        add_filter('rest_pre_dispatch', [$this, '_processRequest'], 10, 3);
    }

    /**
     * Check that the incoming request is for a route matching this prefix
     */
    private function requestMatchesPrefix($request): bool
    {
        $requestRoute = strtolower(trim($request->get_route(), "/ "));
        $prefix = strtolower($this->prefix());
        return strpos($requestRoute, $prefix) === 0;
    }

    /**
     * Move files to the main parameter array to allow their validation
     */
    public function _processRequest($result, $server, $request)
    {
        if ($this->requestMatchesPrefix($request)) {
            if (has_action(Hooks::PROCESS_REQUEST_FILES)) {
                do_action(Hooks::PROCESS_REQUEST_FILES, $request, $server);
            } else {
                $files = $request->get_file_params();
                if (!empty($files)) {
                    foreach ($files as $key => $file) {
                        $request->set_param($key, $file);
                    }
                }
            }
        }
        return null;
    }

    /**
     * Get the REST route prefix
     */
    public function prefix(): string
    {
        return $this->base_url . "/v" . $this->version;
    }

    public static function formatDir($dir): string
    {
        return rtrim($dir, '/') . '/';
    }

    public static function formatNamespace($ns): string
    {
        return rtrim($ns, "\\") . "\\";
    }

    public static function rsearch($folder, $pattern): array
    {
        $dir = new \RecursiveDirectoryIterator($folder);
        $ite = new \RecursiveIteratorIterator($dir);
        $files = new \RegexIterator($ite, $pattern, \RegexIterator::GET_MATCH);

        $fileList = [];
        foreach ($files as $file) {
            $file = str_replace($folder, "", $file);
            $fileList = array_merge($fileList, $file);
        }
        return $fileList;
    }

    public static function convertPathToClass($path, $ext = ".php"): string
    {
        $path = str_replace($ext, "", $path);
        return str_replace(DIRECTORY_SEPARATOR, "\\", $path);
    }

    public function registerRoutes(): void
    {
        $routeFiles = self::rsearch($this->directory, "/.*\.php$/");
        $routeFiles = array_reverse($routeFiles);
        $count = 0;
        foreach ($routeFiles as $file) {
            $class = $this->namespace . self::convertPathToClass($file);

            if (class_exists($class)) {
                $endpoint = new $class;

                register_rest_route($this->prefix(), $this->strStart($endpoint->getPath(), "/"), [
                    'args'  => $endpoint->getArguments(),
                    'callback'  => $endpoint->getCallback(),
                    'methods'   => $endpoint->getMethods(),
                    'permission_callback' => $endpoint->getPermissionCallback(),
                    'show_in_index' => $endpoint->showInIndex()
                ]);
            } else {
                wp_die("Couldn't find the class $class to register the REST route");
            }
            $count++;
        }
    }

    private function strStart($subject, $cap = "/"): string
    {
        return $cap . ltrim($subject, $cap);
    }
}
