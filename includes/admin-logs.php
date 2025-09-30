<?php
defined('ABSPATH') || exit;

// Admin: WAG Logs screen under WooCommerce (at the bottom).
add_action('admin_menu', function () {
    // parent 'woocommerce' puts it under the WooCommerce menu
    add_submenu_page(
        'woocommerce',
        __('WAG Logs', 'bigredseo-woocommerce-order-attribution-guard'), // page title
        __('WAG Logs', 'bigredseo-woocommerce-order-attribution-guard'), // menu title
        'manage_woocommerce',                                            // capability
        'brseo-wag-logs',                                                // slug
        function () {                                                    // render callback
            if ( ! current_user_can('manage_woocommerce') ) {
                wp_die(__('Insufficient permissions', 'bigredseo-woocommerce-order-attribution-guard'));
            }
            if ( ! function_exists('brseo_wag_logger') ) {
                echo '<div class="wrap"><h1>WAG Logs</h1><p>'.esc_html__('Logger not loaded.', 'bigredseo-woocommerce-order-attribution-guard').'</p></div>';
                return;
            }

            $logger = brseo_wag_logger();
            $stats  = $logger->stats();
            $recent = $logger->tail(50);
            ?>
            <div class="wrap">
                <h1><?php esc_html_e('WAG Logs', 'bigredseo-woocommerce-order-attribution-guard'); ?></h1>
                <p><?php esc_html_e('Log file:', 'bigredseo-woocommerce-order-attribution-guard'); ?>
                    <code><?php echo esc_html( $logger->get_log_path() ); ?></code>
                </p>
                <h2><?php esc_html_e('Counts', 'bigredseo-woocommerce-order-attribution-guard'); ?></h2>
                <ul>
                    <li><?php echo esc_html__('Attempts:', 'bigredseo-woocommerce-order-attribution-guard').' '.intval($stats['attempt'] ?? 0); ?></li>
                    <li><?php echo esc_html__('Blocked:', 'bigredseo-woocommerce-order-attribution-guard').' '.intval($stats['blocked'] ?? 0); ?></li>
                    <li><?php echo esc_html__('Success:', 'bigredseo-woocommerce-order-attribution-guard').' '.intval($stats['success'] ?? 0); ?></li>
                    <li><?php echo esc_html__('Other:', 'bigredseo-woocommerce-order-attribution-guard').' '.intval($stats['other'] ?? 0); ?></li>
                </ul>

                <h2><?php printf( esc_html__('Recent (last %d)', 'bigredseo-woocommerce-order-attribution-guard'), count($recent) ); ?></h2>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Time (UTC)', 'bigredseo-woocommerce-order-attribution-guard'); ?></th>
                            <th><?php esc_html_e('Action', 'bigredseo-woocommerce-order-attribution-guard'); ?></th>
                            <th><?php esc_html_e('IP', 'bigredseo-woocommerce-order-attribution-guard'); ?></th>
                            <th><?php esc_html_e('User', 'bigredseo-woocommerce-order-attribution-guard'); ?></th>
                            <th><?php esc_html_e('Total', 'bigredseo-woocommerce-order-attribution-guard'); ?></th>
                            <th><?php esc_html_e('Origin', 'bigredseo-woocommerce-order-attribution-guard'); ?></th>
                            <th><?php esc_html_e('Device', 'bigredseo-woocommerce-order-attribution-guard'); ?></th>
                            <th><?php esc_html_e('Order', 'bigredseo-woocommerce-order-attribution-guard'); ?></th>
                            <th><?php esc_html_e('Note', 'bigredseo-woocommerce-order-attribution-guard'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent)) : ?>
                            <tr><td colspan="9"><?php esc_html_e('No events logged yet.', 'bigredseo-woocommerce-order-attribution-guard'); ?></td></tr>
                        <?php else : foreach ($recent as $r) : ?>
                            <tr>
                                <td><?php echo esc_html($r['timestamp'] ?? ''); ?></td>
                                <td><?php echo esc_html($r['action'] ?? ''); ?></td>
                                <td><?php echo esc_html($r['ip'] ?? ''); ?></td>
                                <td><?php echo esc_html($r['user_login'] ?? ($r['email'] ?? '')); ?></td>
                                <td><?php echo esc_html($r['cart_total'] ?? ''); ?></td>
                                <td><?php echo esc_html($r['attribution_origin'] ?? ''); ?></td>
                                <td><?php echo esc_html($r['attribution_device'] ?? ''); ?></td>
                                <td><?php echo esc_html($r['order_id'] ?? ''); ?></td>
                                <td><?php echo esc_html($r['note'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <?php
        },
        99 // position: high number pushes it to the bottom of the WooCommerce submenu
    );
}, 100); // run late so it stays near the bottom
