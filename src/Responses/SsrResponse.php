<?php

namespace EvoMark\InertiaWordpress\Responses;

use EvoMark\InertiaWordpress\Data\SsrResponse as DataSsrResponse;
use GuzzleHttp\Client;
use EvoMark\InertiaWordpress\Helpers\Settings;

class SsrResponse
{
    public static function handleHead()
    {
        $request = inertia_request();
        $url = Settings::get('ssr_url');

        $client = new Client;
        try {
            $response = $client->post(
                $url . "/render",
                [
                    'json' => $request->getPageData(false),
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ]
                ]
            );

            if ($response->getStatusCode() !== 200) {
                throw new \Exception("Couldn't render Inertia page");
            }

            $ssrRaw = json_decode($response->getBody(), true);
            $ssr = new DataSsrResponse($ssrRaw['head'], $ssrRaw['body']);
            $request->setSsr($ssr);

            foreach ($ssr->head ?? [] as $element) :
                echo $element . PHP_EOL;
            endforeach;
            \wp_head();
        } catch (\Exception $err) {
            $request->ssrEnabled = false;
            $request->setSsr(null);
            return StandardResponse::handleHead();
        }
    }

    public static function handleBody($args)
    {
        $request = inertia_request();
        $ssr = $request->getSsr();

        echo $ssr->body;

        wp_footer();
        return;
    }
}
