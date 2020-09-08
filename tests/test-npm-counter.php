<?php

class NpmCounterTest extends WP_UnitTestCase {

    private $plugin;
    const NPMCOUNTER = "npm_counter";
    const NPMSHORTCODE = "[npm_counter]";

    function setUp() {
        parent::setUp();
        $this->plugin = $GLOBALS[ self::NPMCOUNTER ];
        $_COOKIE[ self::NPMCOUNTER ] = "1";
    }

    function test_my_counter_shortcode() {
        $count = do_shortcode( self::NPMSHORTCODE );
        $this->assertTrue(is_int(intval($count)));
    }

    function test_shortcode_widget() {
        $this->assertEquals( 11, has_filter('widget_text', 'do_shortcode') );
    }

    function test_countup() {
        $this->plugin->countUp();
        $count0 = $this->plugin->getCount();
        $this->plugin->countUp();
        $count1 = $this->plugin->getCount();
        $this->assertEquals( $count1, $count0 + 1 );
    }

    function test_not_increment_while_session() {
        $count0 = do_shortcode( self::NPMSHORTCODE );
        $count1 = do_shortcode( self::NPMSHORTCODE );
        $this->assertEquals( $count1, $count0 );
    }

    function test_cookie_is_set() {
        $cookie = $this->plugin->getCookie();
        $this->assertEquals( true, $cookie );
    }

}