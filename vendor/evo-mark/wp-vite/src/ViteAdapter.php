<?php

namespace EvoMark\WpVite;

use DOMDocument;
use Exception;

class ViteAdapter
{
    /**
     * Initial config for the instance
     */
    public ViteConfig $config;

    /**
     * The Content Security Policy nonce to apply to all generated tags.
     */
    protected string | null $nonce;

    /**
     * The key to check for integrity hashes within the manifest.
     */
    protected string | false $integrityKey = 'integrity';

    /**
     * The configured entry points.
     */
    protected array $entryPoints = [];

    /**
     * The path to the "hot" file.
     */
    protected string|null $hotFile;

    /**
     * The path to the build directory.
     */
    protected string $buildDirectory = 'build';

    /**
     * The name of the manifest file.
     */
    protected string $manifestFilename = 'manifest.json';

    /**
     * The script tag attributes resolvers.
     */
    protected array $scriptTagAttributesResolvers = [];

    /**
     * The style tag attributes resolvers.
     */
    protected array $styleTagAttributesResolvers = [];

    /**
     * The preload tag attributes resolvers.
     */
    protected array $preloadTagAttributesResolvers = [];

    /**
     * The preloaded assets.
     */
    protected array $preloadedAssets = [];

    /**
     * The cached manifest files.
     */
    protected static array $manifests = [];

    public function __construct(array $configArray)
    {
        $this->config = new ViteConfig($configArray);
    }

    /**
     * Get the preloaded assets.
     */
    public function preloadedAssets(): array
    {
        return $this->preloadedAssets;
    }

    /**
     * Get the Content Security Policy nonce applied to all generated tags.
     */
    public function cspNonce(): string|null
    {
        return $this->nonce;
    }

    /**
     * Generate or set a Content Security Policy nonce to apply to all generated tags.
     */
    public function useCspNonce(?string $nonce = null): string
    {
        return $this->nonce = $nonce ?? wp_create_nonce('vite-hmr');
    }

    /**
     * Use the given key to detect integrity hashes in the manifest.
     */
    public function useIntegrityKey(string|false $key): ViteAdapter
    {
        $this->integrityKey = $key;

        return $this;
    }

    /**
     * Set the Vite entry points.
     */
    public function withEntryPoints(array $entryPoints): ViteAdapter
    {
        $this->entryPoints = $entryPoints;

        return $this;
    }

    /**
     * Set the filename for the manifest file.
     */
    public function useManifestFilename(string $filename): ViteAdapter
    {
        $this->manifestFilename = $filename;

        return $this;
    }

    /**
     * Get the Vite "hot" file path.
     */
    public function hotFile(): string
    {
        return $this->config->uploadsPath . '/' . $this->config->hotFile;
    }

    /**
     * Set the Vite "hot" file path.
     */
    public function useHotFile(string $path): ViteAdapter
    {
        $this->hotFile = $path;

        return $this;
    }

    /**
     * Set the Vite build directory.
     */
    public function useBuildDirectory(string $path): ViteAdapter
    {
        $this->buildDirectory = $path;

        return $this;
    }

    /**
     * Use the given callback to resolve attributes for script tags.
     */
    public function useScriptTagAttributes(callable|array $attributes): ViteAdapter
    {
        if (!is_callable($attributes)) {
            $attributes = fn () => $attributes;
        }

        $this->scriptTagAttributesResolvers[] = $attributes;

        return $this;
    }

    /**
     * Use the given callback to resolve attributes for style tags.
     */
    public function useStyleTagAttributes(callable|array $attributes): ViteAdapter
    {
        if (!is_callable($attributes)) {
            $attributes = fn () => $attributes;
        }

        $this->styleTagAttributesResolvers[] = $attributes;

        return $this;
    }

    /**
     * Use the given callback to resolve attributes for preload tags.
     */
    public function usePreloadTagAttributes(callable|array|false $attributes): ViteAdapter
    {
        if (!is_callable($attributes)) {
            $attributes = fn () => $attributes;
        }

        $this->preloadTagAttributesResolvers[] = $attributes;

        return $this;
    }

    /**
     * Inject required code for React to run in dev/HMR mode
     */
    private function reactRefresh(): array
    {
        if ($this->config->useReact !== true) return [];

        return [sprintf(
            <<<'HTML'
            <script type="module" %s>
                import RefreshRuntime from '%s';
                RefreshRuntime.injectIntoGlobalHook(window);
                window.$RefreshReg$ = () => {};
                window.$RefreshSig$ = () => (type) => type;
                window.__vite_plugin_react_preamble_installed__ = true;
            </script>
        HTML,
            "",
            $this->hotAsset('@react-refresh')
        )];
    }

    /**
     * Generate Vite tags for an entrypoint.
     */
    public function generateTags(array|string $entrypoints, ?string $buildDirectory = null)
    {
        $entrypoints = is_array($entrypoints) ? $entrypoints : array($entrypoints);
        $buildDirectory ??= $this->buildDirectory;

        if ($this->isRunningHot()) {
            $reactRefresh = $this->reactRefresh();

            $entrypoints = ['@vite/client', ...$entrypoints];
            $entryTags = array_map(fn ($entrypoint) => $this->makeTagForChunk($entrypoint, $this->hotAsset($entrypoint), null, null), $entrypoints);
            $entryTags = array_merge($reactRefresh, $entryTags);
            return implode("", $entryTags);
        }

        $manifest = $this->manifest($buildDirectory);
        $manifestHash = hash('md5', json_encode($manifest));

        $tags = [];
        $preloads = [];

        foreach ($entrypoints as $entrypoint) {
            $chunk = $this->chunk($manifest, $entrypoint);

            $preloads[] = array(
                $chunk['src'],
                $this->assetPath("{$buildDirectory}/{$chunk['file']}"),
                $chunk,
                $manifest
            );


            foreach ($chunk['imports'] ?? [] as $import) {
                $preloads[] = array(
                    $import,
                    $this->assetPath("{$buildDirectory}/{$manifest[$import]['file']}"),
                    $manifest[$import],
                    $manifest
                );

                foreach ($manifest[$import]['css'] ?? [] as $css) {
                    $partialManifest = array_filter($manifest, fn ($item) => $item['file'] == $css);

                    $partialManifestKeys = array_keys($partialManifest);
                    $partialManifestValues = array_values($partialManifest);

                    $preloads[] = array(
                        $partialManifestKeys[0],
                        $this->assetPath("{$buildDirectory}/{$css}"),
                        $partialManifestValues[0],
                        $manifest
                    );

                    $tags[] = $this->makeTagForChunk(
                        $partialManifestKeys[0],
                        $this->assetPath("{$buildDirectory}/{$css}"),
                        $partialManifestValues[0],
                        $manifest
                    );
                }
            }

            $tags[] = $this->makeTagForChunk(
                $entrypoint,
                $this->assetPath("{$buildDirectory}/{$chunk['file']}"),
                $chunk,
                $manifest
            );

            foreach ($chunk['css'] ?? [] as $css) {
                $partialManifest = array_filter($manifest, fn ($item) => $item['file'] == $css);

                $partialManifestKeys = array_keys($partialManifest);
                $partialManifestValues = array_values($partialManifest);

                $preloads[] = array(
                    $partialManifestKeys[0] ?? null,
                    $this->assetPath("{$buildDirectory}/{$css}"),
                    $partialManifestValues[0] ?? null,
                    $manifest
                );

                $tags[] = $this->makeTagForChunk(
                    $partialManifestKeys[0] ?? null,
                    $this->assetPath("{$buildDirectory}/{$css}"),
                    $partialManifestValues[0] ?? null,
                    $manifest
                );
            }
        }

        $tags = array_unique($tags);
        $stylesheets = array_filter($tags, fn ($tag) => str_starts_with($tag, '<link'));
        $scripts = array_filter($tags, fn ($tag) => str_starts_with($tag, '<link') === false);

        $preloadsJson = array_map(fn ($pre) => json_encode($pre), $preloads);
        $preloadsJson = array_unique($preloadsJson);
        $preloads = array_map(fn ($pre) => json_decode($pre, true), $preloadsJson);

        usort($preloads, fn ($args) => $this->isCssPath($args[1]) ? 1 : -1);
        $preloads = array_map(fn ($args) => $this->makePreloadTagForChunk(...$args), $preloads);

        $this->enqueueScripts($scripts, $manifestHash);
        $this->enqueueStylesheets($stylesheets, $manifestHash);

        return implode("\n", $preloads) . "\n";
    }

    private function createHandle($multiEntry, $index)
    {
        if ($multiEntry === false && !empty($this->config->entryHandle)) {
            return $this->config->entryHandle;
        }

        $suffix = $multiEntry ? "-" . $index + 1 : "";
        return $this->config->namespace . $suffix;
    }

    public function enqueueScripts(array $scripts, string $hash)
    {
        $multiEntry = count($scripts) > 1;

        foreach ($scripts as $index => $script) {
            $attributes = [];
            $dom = new DOMDocument();
            $dom->loadHTML($script);
            $node = $dom->getElementsByTagName('script');
            foreach ($node->item(0)->attributes as $key => $obj) {
                $attributes[$key] = $obj->value;
            }

            $handle = $this->createHandle($multiEntry, $index);
            wp_register_script($handle, $attributes['src'], $this->config->dependencies, $hash, true);
            unset($attributes['src']);
            wp_enqueue_script($handle);
            foreach ($attributes as $attr => $val) {
                wp_scripts()->add_data($handle, $attr, $val);
            }
        }
    }

    public function enqueueStylesheets(array $styles, string $hash)
    {
        foreach ($styles as $style) {
            $attributes = [];
            $dom = new DOMDocument();
            $dom->loadHTML($style);
            $node = $dom->getElementsByTagName('link');
            foreach ($node->item(0)->attributes as $key => $obj) {
                $attributes[$key] = $obj->value;
            }
            $hrefArray = explode("/", $attributes['href']);
            $handle = array_pop($hrefArray);
            $handle = str_replace(".css", "-css", $handle);
            wp_enqueue_style($handle, $attributes['href'], array(), $hash);
        }
    }

    /**
     * Determine if the HMR server is running.
     */
    public function isRunningHot(): bool
    {
        return is_file($this->hotFile());
    }

    /**
     * Get the path to the manifest file for the given build directory.
     */
    protected function manifestPath(string $buildDirectory): string
    {
        return $this->config->uploadsPath . '/' . $buildDirectory . '/' . $this->manifestFilename;
    }

    /**
     * Get the the manifest file for the given build directory.
     */
    protected function manifest(string $buildDirectory): array
    {
        $path = $this->manifestPath($buildDirectory);

        if (!isset(static::$manifests[$path])) {
            if (!is_file($path)) {
                throw new Exception("Vite manifest not found at: {$path}");
            }

            static::$manifests[$path] = json_decode(file_get_contents($path), true);
        }

        return static::$manifests[$path];
    }

    /**
     * Make tag for the given chunk.
     * @param string|null $src The relative (imported) path of the resource
     * @param string|null $url The URI of the resource
     * @param array|null $chunk The chunk array
     * @param array $manifest The Vite manifest
     */
    protected function makeTagForChunk(string|null $src, string|null $url, array|null $chunk, array|null $manifest): string
    {
        if (empty($url)) return "";

        if (
            (!isset($this->nonce) ||
                $this->nonce === null)
            && $this->integrityKey !== false
            && !array_key_exists($this->integrityKey, $chunk ?? [])
            && $this->scriptTagAttributesResolvers === []
            && $this->styleTagAttributesResolvers === []
        ) {
            return $this->makeTag($url);
        }

        if ($this->isCssPath($url)) {
            return $this->makeStylesheetTagWithAttributes(
                $url,
                $this->resolveStylesheetTagAttributes($src, $url, $chunk, $manifest)
            );
        }

        return $this->makeScriptTagWithAttributes(
            $url,
            $this->resolveScriptTagAttributes($src, $url, $chunk, $manifest)
        );
    }

    /**
     * Generate an appropriate tag for the given URL in HMR mode.
     */
    protected function makeTag(string $url): string
    {
        if ($this->isCssPath($url)) {
            return $this->makeStylesheetTag($url);
        }

        return $this->makeScriptTag($url);
    }

    /**
     * Generate a script tag for the given URL.
     */
    protected function makeScriptTag(string $url): string
    {
        return $this->makeScriptTagWithAttributes($url, []);
    }

    /**
     * Generate a stylesheet tag for the given URL in HMR mode.
     */
    protected function makeStylesheetTag(string $url): string
    {
        return $this->makeStylesheetTagWithAttributes($url, []);
    }

    /**
     * Generate a script tag with attributes for the given URL.
     */
    protected function makeScriptTagWithAttributes(string $url, array $attributes): string
    {
        wp_register_script($this->config->namespace . '-js', $url);
        $attributes = $this->parseAttributes(array_merge([
            'type' => 'module',
            'src' => $url,
            'nonce' => $this->nonce ?? false,
        ], $attributes));

        return '<script ' . implode(' ', $attributes) . '></script>';
    }

    /**
     * Generate a link tag with attributes for the given URL.
     */
    protected function makeStylesheetTagWithAttributes(string $url, array $attributes): string
    {
        $attributes = $this->parseAttributes(array_merge([
            'rel' => 'stylesheet',
            'href' => $url,
            'nonce' => $this->nonce ?? false,
        ], $attributes));

        return '<link ' . implode(' ', $attributes) . ' />';
    }

    /**
     * Determine whether the given path is a CSS file.
     *
     * @param  string  $path
     * @return bool
     */
    protected function isCssPath($path)
    {
        return preg_match('/\.(css|less|sass|scss|styl|stylus|pcss|postcss)(\?.*)?$/', $path) === 1;
    }

    /**
     * Parse the attributes into key="value" strings.
     */
    protected function parseAttributes(array $attributes): array
    {
        $attributes = array_filter($attributes, fn ($value) => in_array($value, [false, null], true) === false);
        $attributes = array_merge([], ...array_map(fn (string $key, string $value) => $value === true ? [$key] : [$key => $value], array_keys($attributes), array_values($attributes)));
        $attributes = array_map(fn ($key, $value) => is_int($key) ? $value : $key . '="' . $value . '"', array_keys($attributes), array_values($attributes));

        return $attributes;
    }

    /**
     * Get the chunk for the given entry point / asset.
     */
    protected function chunk(array $manifest, string $file): array
    {
        if (!isset($manifest[$file])) {
            throw new Exception("Unable to locate file in Vite manifest: {$file}.");
        }
        return $manifest[$file];
    }

    /**
     * Get the path to a given asset when running in HMR mode.
     */
    protected function hotAsset($asset): string
    {
        return rtrim(file_get_contents($this->hotFile())) . '/' . $asset;
    }

    /**
     * Get the URL for an asset.
     */
    public function asset(string $asset, ?string $buildDirectory = null): string
    {
        $buildDirectory ??= $this->buildDirectory;

        if ($this->isRunningHot()) {
            return $this->hotAsset($asset);
        }

        $chunk = $this->chunk($this->manifest($buildDirectory), $asset);

        return $this->assetPath($buildDirectory . '/' . $chunk['file']);
    }

    /**
     * Generate an asset path for the application.
     */
    protected function assetPath(string $path, ?bool $secure = null): string
    {
        return $this->config->uploadsUrl . '/' . $path;
    }

    /**
     * Make a preload tag for the given chunk.
     */
    protected function makePreloadTagForChunk(string|null $src, string|null $url, array|null $chunk, array $manifest): string
    {
        if (empty($url)) return "";

        $manifestHash = hash('md5', json_encode($manifest));
        $url .= "?ver=" . $manifestHash;

        $attributes = $this->resolvePreloadTagAttributes($src, $url, $chunk, $manifest);

        if ($attributes === false) {
            return '';
        }

        $preloadAttributes = array_replace([], $attributes);

        unset($preloadAttributes['href']);

        $this->preloadedAssets[$url] = $this->parseAttributes($preloadAttributes);

        return '<link ' . implode(' ', $this->parseAttributes($attributes)) . ' />';
    }

    /**
     * Resolve the attributes for the chunks generated preload tag.
     *
     * @param  string  $src
     * @param  string  $url
     * @param  array  $chunk
     * @param  array  $manifest
     * @return array|false
     */
    protected function resolvePreloadTagAttributes($src, $url, $chunk, $manifest)
    {
        $attributes = $this->isCssPath($url) ? [
            'rel' => 'preload',
            'as' => 'style',
            'href' => $url,
            'nonce' => $this->nonce ?? false,
            'crossorigin' => $this->resolveStylesheetTagAttributes($src, $url, $chunk, $manifest)['crossorigin'] ?? false,
        ] : [
            'rel' => 'modulepreload',
            'href' => $url,
            'nonce' => $this->nonce ?? false,
            'crossorigin' => $this->resolveScriptTagAttributes($src, $url, $chunk, $manifest)['crossorigin'] ?? false,
        ];

        $attributes = $this->integrityKey !== false
            ? array_merge($attributes, ['integrity' => $chunk[$this->integrityKey] ?? false])
            : $attributes;

        foreach ($this->preloadTagAttributesResolvers as $resolver) {
            if (false === ($resolvedAttributes = $resolver($src, $url, $chunk, $manifest))) {
                return false;
            }

            $attributes = array_merge($attributes, $resolvedAttributes);
        }

        return $attributes;
    }

    /**
     * Resolve the attributes for the chunks generated script tag.
     */
    protected function resolveScriptTagAttributes(string $src, string $url, array $chunk, array $manifest): array
    {
        $attributes = $this->integrityKey !== false
            ? ['integrity' => $chunk[$this->integrityKey] ?? false]
            : [];

        foreach ($this->scriptTagAttributesResolvers as $resolver) {
            $attributes = array_merge($attributes, $resolver($src, $url, $chunk, $manifest));
        }

        return $attributes;
    }

    /**
     * Resolve the attributes for the chunks generated stylesheet tag.
     */
    protected function resolveStylesheetTagAttributes(string|null $src, string|null $url, array|null $chunk, array $manifest): array
    {
        if (empty($chunk)) {
            return [];
        }

        $attributes = $this->integrityKey !== false
            ? ['integrity' => $chunk[$this->integrityKey] ?? false]
            : [];

        foreach ($this->styleTagAttributesResolvers as $resolver) {
            $attributes = array_merge($attributes, $resolver($src, $url, $chunk, $manifest));
        }

        return $attributes;
    }
}
