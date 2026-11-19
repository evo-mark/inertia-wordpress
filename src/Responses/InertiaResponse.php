<?php

namespace EvoMark\InertiaWordpress\Responses;

use EvoMark\InertiaWordpress\Data\HttpResponse;
use EvoMark\InertiaWordpress\Helpers\Header;
use EvoMark\InertiaWordpress\Helpers\RequestResponse;
use EvoMark\InertiaWordpress\Inertia;

class InertiaResponse
{
    /**
     * Send the page object to an Inertia visit as JSON. This ends the request.
     */
    public static function handle()
    {
        $request = inertia_request();

        // Normally caught earlier by InertiaController::render() so that flash data and
        // errors are not consumed; kept here for anything calling handle() directly.
        if ($request->hasVersionMismatch()) {
            return self::handleVersionChange();
        }

        RequestResponse::emit(HttpResponse::json($request->getPageData(), 200, [
            ucwords(Header::INERTIA, "-") => 'true',
            'Vary' => ucwords(Header::INERTIA, "-"),
        ]));
    }

    /**
     * Tell the client its assets are stale: it will reload the requested URL with a full visit.
     */
    public static function handleVersionChange()
    {
        // Can't use wp_redirect because WP doesn't allow non-3xx redirection codes
        return Inertia::location(RequestResponse::currentUrl());
    }
}
