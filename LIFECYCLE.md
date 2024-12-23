1. Entry Point

Request entry is always through the controller render function

2. Render Function

Detects X-Inertia request and returns a JSON response (InertiaResponse)

    OR

Includes the `app.php` root view

3. Root View

Checks if SSR is enabled, if so contacts the endpoint for head/body data (SsrResponse)

Otherwise do a standard response (StandardResponse)
