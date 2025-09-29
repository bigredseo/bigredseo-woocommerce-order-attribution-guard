<?php
namespace BigRedSEO\WAG\Guard;

use BigRedSEO\WAG\Helpers\Attribution;

defined('ABSPATH') || exit;

class CheckoutGate {
    public static function init(): void {
        // Store API (Checkout Block)
        add_filter('woocommerce_store_api_checkout_update_order_from_request', [__CLASS__, 'gate_store_api'], 5, 2);

        // PayPal Payments AJAX routes
        add_action('init', [__CLASS__, 'gate_paypal_ajax']);

        // Classic checkout fallback
        add_action('woocommerce_checkout_process', [__CLASS__, 'gate_classic']);
    }

    public static function gate_store_api($order, $request) {
        if (self::should_block('store_api')) {
            wc_add_notice(__('We couldn’t verify checkout attribution — please reload the page and try again.', 'bigredseo-woocommerce-order-attribution-guard'), 'error');
            throw new \Exception('Blocked by Attribution Guard');
        }
        return $order;
    }

    public static function gate_paypal_ajax(): void {
        if (!wp_doing_ajax()) return;

        $action = $_REQUEST['action'] ?? '';
        if (!in_array($action, ['ppc-create-order', 'ppc-approve-order'], true)) return;

        if (self::should_block('paypal_ajax')) {
            wp_send_json_error(['message' => __('We couldn’t verify checkout attribution. Please refresh and retry.', 'bigredseo-woocommerce-order-attribution-guard')], 400);
        }
    }

    public static function gate_classic(): void {
        if (self::should_block('classic_checkout')) {
            wc_add_notice(__('We couldn’t verify checkout attribution — please reload and try again.', 'bigredseo-woocommerce-order-attribution-guard'), 'error');
        }
    }

    protected static function should_block(string $route): bool {
        $data = Attribution::current();
        $missing = (empty($data['origin']) || empty($data['device']));

        $block = (bool) apply_filters('brseo_wag_should_block_checkout', $missing, [
            'route' => $route,
            'attr'  => $data,
            'user'  => get_current_user_id(),
        ]);

        return $block;
    }
}
