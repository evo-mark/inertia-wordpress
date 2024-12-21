<?php

namespace EvoMark\InertiaWordpress\Responses;

use EvoMark\InertiaWordpress\Helpers\Header;
use EvoMark\InertiaWordpress\Container;
use EvoMark\InertiaWordpress\Helpers\RequestResponse;
use EvoMark\InertiaWordpress\Inertia;

class InertiaResponse
{
    public static function handle()
    {
        $request = inertia_request();
        $requestedUrl = home_url($_SERVER['REQUEST_URI']);

        $page = $request->getPageData();

        if (RequestResponse::getMethod() === "GET" && $request->getHeader(Header::VERSION, "") !== Inertia::getVersion()) {
            // Can't use wp_redirect because WP doesn't allow non-3xx redirection codes
            Inertia::location($requestedUrl);
        }

        if (http_response_code() === 302 && in_array(RequestResponse::getMethod(), ['PUT', 'PATCH', 'DELETE'])) {
            status_header(303);
        }

        header('Vary: Accept');
        header(ucwords(Header::INERTIA, "-") . ": true");


        wp_send_json($page);
    }
}
