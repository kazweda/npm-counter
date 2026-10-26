<?php
/*
Plugin Name: Npm Counter
Version: 1.1
Description: Website Visit Counter
Author: Kazuaki UEDA
Author URI: http://www.netplan.co.jp/
Plugin URI: http://www.netplan.co.jp/
License: GPLv3 or later
Text Domain: npm-counter
*/


// Store a reference to the plugin in GLOBALS so that our unit tests can access it
if( ! array_key_exists( 'npm_counter', $GLOBALS ) ) {
    $GLOBALS['npm_counter'] = new NpmCounter();
}

//#インストール時に実行
if(function_exists('register_activation_hook')) {
    register_activation_hook (__FILE__, array('NpmCounter', 'npmCounterInstall'));
}
//#削除時に実行
if(function_exists('register_uninstall_hook')) {
    register_uninstall_hook (__FILE__, array('NpmCounter', 'npmCounterUninstall'));
}

class NpmCounter {

    const NPMCOUNTER = "npm_counter";

    const BOT_USER_AGENT = '/bot|crawl|spider|slurp|archiver|facebookexternalhit|headless/i';

    public function __construct() {
        // フロントの表示時だけカウントする
        add_action( 'template_redirect', array( $this, 'mySetCookie'));
        add_shortcode( 'npm_counter', array( $this, 'getCount' ));
        add_filter( 'widget_text', 'do_shortcode', 11 );
    }

    public function mySetCookie() {
        if (!$this->isCountableRequest()) {
            return;
        }
        if (!isset($_COOKIE[self::NPMCOUNTER]) || $_COOKIE[self::NPMCOUNTER] != "1") {
            // Cookie を設定できないときは、アクセスのたびに増えてしまうのでカウントしない
            if ($this->sendCookie()) {
                $this->countUp();
            }
        }
    }

    public function isCountableRequest() {
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return false;
        }
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return false;
        }
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if ($userAgent === '' || preg_match(self::BOT_USER_AGENT, $userAgent)) {
            return false;
        }
        return true;
    }

    protected function sendCookie() {
        if (headers_sent()) {
            return false;
        }
        return setcookie(self::NPMCOUNTER, "1", time() + 1800, COOKIEPATH, COOKIE_DOMAIN);
    }

    public function getCount() {
        return number_format( intval(get_option( self::NPMCOUNTER )) );
    }

    public function countUp() {
        global $wpdb;

        // 同時アクセスでカウントが抜けないよう、DB 側で加算する
        $updated = $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->options} SET option_value = option_value + 1 WHERE option_name = %s",
            self::NPMCOUNTER
        ) );
        if ( ! $updated ) {
            add_option( self::NPMCOUNTER, 1 );
            return;
        }

        // オプションのキャッシュを DB の値に合わせる
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
            self::NPMCOUNTER
        ) );
        $alloptions = wp_load_alloptions();
        if ( isset( $alloptions[ self::NPMCOUNTER ] ) ) {
            $alloptions[ self::NPMCOUNTER ] = $count;
            wp_cache_set( 'alloptions', $alloptions, 'options' );
        } else {
            wp_cache_set( self::NPMCOUNTER, $count, 'options' );
        }
    }

    public function getCookie() {
        if (isset($_COOKIE[self::NPMCOUNTER])) {
            return $_COOKIE[self::NPMCOUNTER];
        }
        return 'none';
    }

    public function unsetMyCookie() {
        if (!isset($_COOKIE[self::NPMCOUNTER])) {
            return;
        }
        setCookie(self::NPMCOUNTER, '', time() - 1800, COOKIEPATH, COOKIE_DOMAIN);
    }

    public static function npmCounterInstall() {
        if (get_option( self::NPMCOUNTER, false ) === false) {
            update_option( self::NPMCOUNTER, 5000 );
        }
    }
    
    public static function npmCounterUninstall() {
        if (get_option( self::NPMCOUNTER, false ) !== false) {
            delete_option( self::NPMCOUNTER );
        }
    }
}