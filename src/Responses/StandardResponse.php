<?php

namespace EvoMark\InertiaWordpress\Responses;

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

        $page = json_encode($request->getPageData());
        $page = htmlspecialchars($page, ENT_QUOTES, 'UTF-8');

        wp_body_open();

        echo "<div id=\"$id\"$classAttribute data-page=\"$page\"></div>";

        wp_footer();
    }
}
