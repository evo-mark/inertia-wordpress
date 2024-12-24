<?php

namespace EvoMark\InertiaWordpress\Modules\TheSeoFramework;

use DOMNodeList;
use EvoMark\InertiaWordpress\Inertia;
use EvoMark\InertiaWordpress\Modules\BaseModule;
use The_SEO_Framework\Front\Meta\Head;
use The_SEO_Framework\Meta\Title;

class Module extends BaseModule
{
    protected string $title = "The SEO Framework";
    protected string $class = "The_SEO_Framework\Load";
    protected string $slug = "seo-framework";
    protected array|string $entry = [
        'autodescription/autodescription.php',
    ];
    protected bool $isInternal = true;

    public function register()
    {
        remove_action('wp_head', [Head::class, 'print_wrap_and_tags'], 1);

        add_action('after_setup_theme', function () {
            remove_theme_support('title-tag');
        }, PHP_INT_MAX);
    }

    /**
     * Called via action hook
     */
    public function boot(): void
    {
        ob_start();
        Head::print_tags();
        $tags = ob_get_clean();

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($tags);
        libxml_clear_errors();

        $seo = [
            'title' => html_entity_decode(Title::get_title()),
            'links' => $this->parseLinks($dom->getElementsByTagName('link')),
            'meta' => $this->parseMeta($dom->getElementsByTagName('meta')),
            'schema' => $this->parseSchema($dom->getElementsByTagName('script')),
        ];

        Inertia::share('seo', $seo);
    }

    private function parseLinks(DOMNodeList $elements): array
    {
        $results = [];

        foreach ($elements as $link) {
            $linkArray = [];
            foreach ($link->attributes as $attr) {
                $linkArray[$attr->nodeName] = $attr->nodeValue;
            }
            $results[] = $linkArray;
        }
        return $results;
    }

    private function parseMeta(DOMNodeList $elements): array
    {
        $results = [];
        foreach ($elements as $meta) {
            $metaArray = [];
            foreach ($meta->attributes as $attr) {
                $metaArray[$attr->nodeName] = $attr->nodeValue;
            }
            $results[] = $metaArray;
        }
        return $results;
    }

    private function parseSchema(DOMNodeList $elements)
    {
        $results = [];
        /** @var \DOMElement $script */
        foreach ($elements as $script) {
            if ($script->getAttribute('type') === 'application/ld+json') {
                $json = json_decode($script->nodeValue, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    if ($json['@context'] === 'https://schema.org') {
                        $results = array_merge($results, $json['@graph']);
                    }
                }
            }
        }
        return $results;
    }
}
