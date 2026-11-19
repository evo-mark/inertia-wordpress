<?php

namespace EvoMark\InertiaWordpress\Responses;

use EvoMark\InertiaWordpress\Helpers\Settings;

class StandardResponse
{
    public static function handleHead()
    {
        wp_head();
    }

    public static function handleBody($args)
    {
        $request = inertia_request();

        $id = esc_attr($args['id'] ?? "app");
        $class = $args['class'] ?? "";
        $classAttribute = $class !== "" ? " class=\"" . esc_attr($class) . "\"" : "";

        wp_body_open();

        if (Settings::get('use_script_element')) {
            // JSON_HEX_TAG escapes < and > so prop content can't close the script element
            // (e.g. "</script>") or open a comment that confuses the HTML parser ("<!--<script").
            $page = json_encode($request->getPageData(), JSON_HEX_TAG);

            echo "<script data-page=\"$id\" type=\"application/json\">$page</script><div id=\"$id\"$classAttribute></div>";
        } else {
            $page = htmlspecialchars(json_encode($request->getPageData()), ENT_QUOTES, 'UTF-8');

            echo "<div id=\"$id\"$classAttribute data-page=\"$page\"></div>";
        }

        wp_footer();
    }
}
