<?php

namespace EvoMark\InertiaWordpress\Responses;

use EvoMark\InertiaWordpress\Contracts\ResponseEmitter;
use EvoMark\InertiaWordpress\Data\HttpResponse;

/**
 * Sends responses using WordPress's own helpers, then ends the request.
 */
class WordpressEmitter implements ResponseEmitter
{
    public function emit(HttpResponse $response): void
    {
        if ($response->isRedirect()) {
            $this->sendHeaders($response, except: ['Location']);
            $url = (string) $response->getHeader('Location');

            // Both apply the wp_redirect / wp_redirect_status filters.
            if ($response->isSafeRedirect()) {
                wp_safe_redirect($url, $response->getStatus(), 'Inertia');
            } else {
                wp_redirect($url, $response->getStatus(), 'Inertia');
            }

            exit;
        }

        $body = $response->getBody();

        if (is_array($body)) {
            $this->sendHeaders($response, except: ['Content-Type']);
            wp_send_json($body, $response->getStatus());
            exit;
        }

        status_header($response->getStatus());
        $this->sendHeaders($response);

        echo $body ?? '';

        exit;
    }

    /**
     * @param array<int, string> $except Header names (case-insensitive) left to WordPress to send.
     */
    protected function sendHeaders(HttpResponse $response, array $except = []): void
    {
        if (headers_sent()) {
            return;
        }

        $except = array_map('strtolower', $except);

        foreach ($response->getHeaders() as $name => $value) {
            if (in_array(strtolower($name), $except, true)) {
                continue;
            }

            header(ucwords(strtolower($name), '-') . ': ' . $value, true);
        }
    }
}
