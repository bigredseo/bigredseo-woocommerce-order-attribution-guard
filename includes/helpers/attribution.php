<?php
namespace BigRedSEO\WAG\Helpers;

defined('ABSPATH') || exit;

class Attribution {
    /**
     * Return best-guess attribution payload (cookie/request/etc.)
     * You may adapt this to read Woo Attribution headers or server-side data as needed.
     */
    public static function current(): array {
        $cookie = isset($_COOKIE['woocommerce_attribution']) ? wp_unslash($_COOKIE['woocommerce_attribution']) : '';
        $data = is_string($cookie) ? json_decode($cookie, true) : [];

        return [
            'origin' => $data['origin'] ?? '',
            'device' => $data['device'] ?? '',
        ];
    }
}
