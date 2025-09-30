<?php
// includes/logging.php
defined('ABSPATH') || exit;

class BRSEO_WAG_Logger {
    private static $instance = null;
    private $file_path;
    private $wc_logger;

    private function __construct() {
        $upload = wp_upload_dir();
        $dir = trailingslashit( $upload['basedir'] ) . 'bigredseo-wag';
        if ( ! file_exists( $dir ) ) {
            wp_mkdir_p( $dir );
        }
        $this->file_path = trailingslashit( $dir ) . 'events.jsonl';
        // WooCommerce logger (falls back gracefully if WC not present)
        if ( function_exists( 'wc_get_logger' ) ) {
            $this->wc_logger = wc_get_logger();
        } else {
            $this->wc_logger = null;
        }
    }

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * $data is an associative array; we'll add timestamp automatically.
     * Example keys: action ('attempt'|'blocked'|'success'), ip, user_login, email,
     * attribution_origin, attribution_device, cart_total, order_id, note
     */
    public function log( array $data ) {
        $data['timestamp'] = gmdate( 'Y-m-d H:i:s' );
        $data['site'] = get_site_url();
        $line = wp_json_encode( $data );

        // append to file (JSON Lines)
        @file_put_contents( $this->file_path, $line . PHP_EOL, FILE_APPEND | LOCK_EX );

        // also log to Woo logger for easy admin access
        if ( $this->wc_logger ) {
            $message = sprintf(
                '[WAG] %s | IP:%s | user:%s | total:%s | note:%s',
                $data['action'] ?? 'unknown',
                $data['ip'] ?? 'n/a',
                $data['user_login'] ?? ($data['email'] ?? 'guest'),
                isset($data['cart_total']) ? $data['cart_total'] : 'n/a',
                $data['note'] ?? ''
            );
            $context = array( 'source' => 'bigredseo-wag' );
            $this->wc_logger->info( $message, $context );
        }
    }

    /** Return last $n lines as array decoded from JSON (most recent first) */
    public function tail( $n = 50 ) {
        if ( ! file_exists( $this->file_path ) ) {
            return array();
        }
        $lines = array();
        $f = fopen( $this->file_path, 'r' );
        if ( ! $f ) {
            return array();
        }
        // read file into array but limited to last $n for simplicity (ok for moderate log sizes)
        $all = [];
        while ( ($line = fgets( $f )) !== false ) {
            $line = trim( $line );
            if ( $line !== '' ) {
                $all[] = json_decode( $line, true );
            }
        }
        fclose( $f );
        if ( empty( $all ) ) {
            return array();
        }
        return array_reverse( array_slice( $all, -1 * $n ) );
    }

    /** Basic stats: counts by action */
    public function stats() {
        $stats = array( 'attempt' => 0, 'blocked' => 0, 'success' => 0, 'other' => 0 );
        if ( ! file_exists( $this->file_path ) ) {
            return $stats;
        }
        $f = fopen( $this->file_path, 'r' );
        if ( ! $f ) {
            return $stats;
        }
        while ( ($line = fgets( $f )) !== false ) {
            $d = json_decode( trim( $line ), true );
            if ( ! is_array( $d ) ) continue;
            $a = $d['action'] ?? 'other';
            if ( isset( $stats[ $a ] ) ) $stats[ $a ]++;
            else $stats['other']++;
        }
        fclose( $f );
        return $stats;
    }

    public function get_log_path() {
        return $this->file_path;
    }
}

/** Helper function */
function brseo_wag_logger() {
    return BRSEO_WAG_Logger::instance();
}
