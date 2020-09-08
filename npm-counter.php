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

    public function __construct() {
        add_action( 'init', array( $this, 'mySetCookie'));
        add_shortcode( 'npm_counter', array( $this, 'getCount' ));
        add_filter( 'widget_text', 'do_shortcode', 11 );
    }

    public function mySetCookie() {
        if (!isset($_COOKIE[self::NPMCOUNTER]) || $_COOKIE[self::NPMCOUNTER] != "1") {
            @setCookie(self::NPMCOUNTER, "1", time() + 1800, COOKIEPATH, COOKIE_DOMAIN);
            $this->countUp();
        }
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