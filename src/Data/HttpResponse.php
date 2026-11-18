<?php

namespace EvoMark\InertiaWordpress\Data;

/**
 * A response the plugin wants to send, decoupled from actually sending it.
 * Sent by a ResponseEmitter (WordpressEmitter in production).
 */
class HttpResponse
{
    /** @var array<string, string> Header name => value, keyed by the name as given */
    protected array $headers = [];

    protected bool $redirect = false;

    protected bool $safeRedirect = true;

    /**
     * @param array<string, string> $headers
     * @param array<mixed>|string|null $body An array is sent as JSON; a string is sent as-is.
     */
    public function __construct(
        protected int $status = 200,
        array $headers = [],
        protected array|string|null $body = null,
    ) {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
    }

    /**
     * A JSON response.
     *
     * @param array<mixed> $data
     * @param array<string, string> $headers
     */
    public static function json(array $data, int $status = 200, array $headers = []): static
    {
        return new static($status, ['Content-Type' => 'application/json; charset=UTF-8', ...$headers], $data);
    }

    /**
     * A redirect response. Safe redirects go through wp_safe_redirect(), which only allows
     * the site's own host (and filtered allowed hosts); unsafe ones allow any external URL.
     */
    public static function redirect(string $url, int $status = 302, bool $safe = true): static
    {
        $response = new static($status, ['Location' => $url]);
        $response->redirect = true;
        $response->safeRedirect = $safe;

        return $response;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get a header value by name, case-insensitively.
     */
    public function getHeader(string $name): ?string
    {
        foreach ($this->headers as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return $value;
            }
        }

        return null;
    }

    public function setHeader(string $name, string $value): static
    {
        foreach (array_keys($this->headers) as $key) {
            if (strcasecmp($key, $name) === 0) {
                unset($this->headers[$key]);
            }
        }

        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * @return array<mixed>|string|null
     */
    public function getBody(): array|string|null
    {
        return $this->body;
    }

    public function isRedirect(): bool
    {
        return $this->redirect;
    }

    public function isSafeRedirect(): bool
    {
        return $this->safeRedirect;
    }
}
