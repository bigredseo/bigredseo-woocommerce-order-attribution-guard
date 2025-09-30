<?php
namespace BigRedSEO\WAG\Guard;

use BigRedSEO\WAG\Helpers\Attribution;

defined('ABSPATH') || exit;

class CheckoutGate {

    // Bootstrap all gates.
    public static function init(): void {
        // HARD STOP for Store API (Checkout Block) *before* controller callbacks.
        // This prevents Woo from creating a draft order in the first place.
        add_filter('rest_request_before_callbacks', [__CLASS__, 'gate_store_api_pre'], 5, 3);

        // Safety net if a draft was already created by the Store API.
        add_filter('woocommerce_store_api_checkout_update_order_from_request', [__CLASS__, 'gate_store_api'], 5, 2);

        // Classic checkout (shortcode/template) — block during validation so no order is created.
        add_action('woocommerce_checkout_process', [__CLASS__, 'gate_classic']);

        // PayPal Payments AJAX routes
        add_action('init', [__CLASS__, 'gate_paypal_ajax']);
    }

    // Classic (non-Blocks) checkout gate - prevents order creation with validation error
    public static function gate_classic(): void {
        $route = 'classic_checkout';
        if (self::should_block($route)) {
            wc_add_notice(
                __('Checkout blocked: missing attribution (origin/device).', 'bigredseo-woocommerce-order-attribution-guard'),
                'error'
            );
        }
    }

    /**
     * Prevent draft order creation for Checkout Block by rejecting the request
     * *before* Store API controllers execute.
     *
     * @param mixed             $response  Null or WP_Error from earlier filters.
     * @param array             $handler   Callback info.
     * @param \WP_REST_Request  $request   Current request.
     * @return mixed|\WP_Error
     */
    public static function gate_store_api_pre($response, $handler, $request) {
        if (!($request instanceof \WP_REST_Request)) {
            return $response;
        }

        $route = $request->get_route();

        // Match core checkout/cart endpoints (covers unversioned and v1 namespaces).
        if (!self::is_store_checkout_route($route)) {
            return $response;
        }

        if (self::should_block($route)) {
            return new \WP_Error(
                'brseo_wag_blocked',
                __('Checkout blocked: missing attribution (origin/device).', 'bigredseo-woocommerce-order-attribution-guard'),
                ['status' => 400]
            );
        }

        return $response;
    }

    /**
     * Safety net for when a draft order already exists and the Store API attempts
     * to update it from the request. We annotate and cancel it to avoid silent Drafts.
     *
     * @param \WC_Order|\WP_Error $order
     * @param \WP_REST_Request    $request
     * @return \WC_Order|\WP_Error
     */
    public static function gate_store_api($order, $request) {
        if ($order instanceof \WP_Error) {
            return $order;
        }

        $route = $request instanceof \WP_REST_Request ? $request->get_route() : 'store_api_update';

        if (!self::should_block($route)) {
            return $order;
        }

        if ($order instanceof \WC_Order) {
            // Add an admin-visible note explaining why this was blocked.
            $order->add_order_note(
                __('Blocked by Big Red SEO – WooCommerce Order Attribution Guard: missing attribution (origin/device).', 'bigredseo-woocommerce-order-attribution-guard')
            );

            // Close it out to avoid lingering Drafts (works with HPOS).
            $order->update_status('cancelled');
        }

        return new \WP_Error(
            'brseo_wag_blocked',
            __('Checkout blocked: missing attribution (origin/device).', 'bigredseo-woocommerce-order-attribution-guard'),
            ['status' => 400]
        );
    }

    /**
     * PayPal Payments AJAX gating.
     * Keep/extend your plugin-specific checks here (left as placeholder so existing behavior remains).
     */
    public static function gate_paypal_ajax(): void {
        if (!wp_doing_ajax()) return; 

        $action = $_REQUEST['action'] ?? ''; 

        if (!in_array($action, ['ppc-create-order', 'ppc-approve-order'], true)) return; 

        if (self::should_block('paypal_ajax')) { 
            wp_send_json_error(['message' => __('We couldn’t verify checkout attribution. Please refresh and retry.', 'bigredseo-woocommerce-order-attribution-guard')], 400); 
        } 
    }

    /**
     * Central decision: should we block this checkout route?
     *
     * @param string $route
     * @return bool
     */
    protected static function should_block(string $route): bool {
        $data    = Attribution::current();
        $missing = (empty($data['origin']) || empty($data['device']));

        // --- Logging: record every attempt before evaluating the filter ---
        // Try to grab basic user + ip context safely.
        $ip         = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_login = '';
        $email      = '';
        if ( is_user_logged_in() ) {
            $u = wp_get_current_user();
            if ( $u && $u->exists() ) {
                $user_login = $u->user_login;
                $email      = $u->user_email;
            }
        }
        // Log the attempt (will appear in JSONL + WC logs if logger is loaded).
        if ( function_exists('\\brseo_wag_logger') ) {
            \brseo_wag_logger()->log([
                'action'              => 'attempt',
                'ip'                  => $ip,
                'user_login'          => $user_login,
                'email'               => $email,
                'attribution_origin'  => $data['origin'] ?? '',
                'attribution_device'  => $data['device'] ?? '',
                'note'                => $route,
            ]);
        }

        /**
         * Filter: allow site owners to override blocking conditions or log diagnostics.
         * @param bool  $missing Default decision (true = block).
         * @param array $context Route, attribution payload, and user.
         */
        $block = (bool) apply_filters('brseo_wag_should_block_checkout', $missing, [
            'route' => $route,
            'attr'  => $data,
            'user'  => get_current_user_id(),
        ]);

        // If we are blocking, log the blocked event with context.
        if ( $block && function_exists('\\brseo_wag_logger') ) {
            \brseo_wag_logger()->log([
                'action'              => 'blocked',
                'ip'                  => $ip,
                'user_login'          => $user_login,
                'email'               => $email,
                'attribution_origin'  => $data['origin'] ?? '',
                'attribution_device'  => $data['device'] ?? '',
                'note'                => 'missing origin/device · ' . $route,
            ]);
        }

        return $block;
    }

    /**
     * Identify Store API checkout/cart routes.
     *
     * @param string $route
     * @return bool
     */
    protected static function is_store_checkout_route(string $route): bool {
        // Use polyfilled starts-with to keep PHP 7.x compatibility.
        return self::starts_with($route, '/wc/store/checkout')
            || self::starts_with($route, '/wc/store/v1/checkout')
            || self::starts_with($route, '/wc/store/cart')
            || self::starts_with($route, '/wc/store/v1/cart');
    }

    /**
     * Polyfill for PHP 8's str_starts_with.
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    protected static function starts_with(string $haystack, string $needle): bool {
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
