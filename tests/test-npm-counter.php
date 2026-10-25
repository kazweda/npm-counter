<?php

class NpmCounterTest extends WP_UnitTestCase {

    private $plugin;
    const NPMCOUNTER = "npm_counter";
    const NPMSHORTCODE = "[npm_counter]";

    function set_up() {
        parent::set_up();
        $this->plugin = $GLOBALS[ self::NPMCOUNTER ];
        $_COOKIE[ self::NPMCOUNTER ] = "1";
    }

    function tear_down() {
        unset( $_COOKIE[ self::NPMCOUNTER ] );
        parent::tear_down();
    }

    function test_my_counter_shortcode() {
        update_option( self::NPMCOUNTER, 5001 );
        $this->assertSame( '5,001', do_shortcode( self::NPMSHORTCODE ) );
    }

    function test_shortcode_widget() {
        $this->assertEquals( 11, has_filter('widget_text', 'do_shortcode') );
    }

    function test_countup() {
        update_option( self::NPMCOUNTER, 5000 );
        $this->plugin->countUp();
        $this->assertSame( 5001, intval( get_option( self::NPMCOUNTER ) ) );
        $this->plugin->countUp();
        $this->assertSame( 5002, intval( get_option( self::NPMCOUNTER ) ) );
    }

    function test_not_increment_while_session() {
        update_option( self::NPMCOUNTER, 5000 );
        $this->plugin->mySetCookie();
        $this->plugin->mySetCookie();
        $this->assertSame( 5000, intval( get_option( self::NPMCOUNTER ) ) );
    }

    function test_cookie_is_set() {
        $this->assertSame( '1', $this->plugin->getCookie() );
    }

    function test_cookie_is_not_set() {
        unset( $_COOKIE[ self::NPMCOUNTER ] );
        $this->assertSame( 'none', $this->plugin->getCookie() );
    }

}
