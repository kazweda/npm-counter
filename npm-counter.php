<?php
/*
Plugin Name: Npm Counter
Version: 1.0
Description: Website Visit Counter
Author: Kazuaki UEDA
Author URI: http://www.netplan.co.jp/
Plugin URI: http://www.netplan.co.jp/
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
        update_option( self::NPMCOUNTER, intval( get_option( self::NPMCOUNTER ) ) + 1 );
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