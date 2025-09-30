<?php
/**
 * Plugin Name: Big Red SEO – WooCommerce Order Attribution Guard
 * Plugin URI:  https://github.com/bigredseo/bigredseo-woocommerce-order-attribution-guard
 * Description: Blocks checkout unless Woo Order Attribution contains both Origin & Device. Protects WooCommerce from spam/invalid orders across Store API, PayPal Payments AJAX, and classic checkout.
 * Version:     1.0.3
 * Author:      Big Red SEO
 * Author URI:  https://www.bigredseo.com
 * License:     GPL-3.0-or-later
 * Text Domain: bigredseo-woocommerce-order-attribution-guard
 */

defined('ABSPATH') || exit;

define('BRSEO_WAG_VERSION', '1.0.3');
define('BRSEO_WAG_PATH', plugin_dir_path(__FILE__));
define('BRSEO_WAG_URL',  plugin_dir_url(__FILE__));

// Bootstrap after plugins load so WooCommerce classes exist.
add_action('plugins_loaded', function () {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p><strong>Big Red SEO – WooCommerce Order Attribution Guard</strong> requires WooCommerce to be active.</p></div>';
        });
        return;
    }

    require_once BRSEO_WAG_PATH . 'includes/helpers/attribution.php';
    require_once BRSEO_WAG_PATH . 'includes/guard/checkoutgate.php';

    \BigRedSEO\WAG\Guard\CheckoutGate::init();
});
