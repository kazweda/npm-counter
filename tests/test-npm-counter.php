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
        unset( $_COOKIE[ self::NPMCOUNTER ], $_SERVER['HTTP_USER_AGENT'] );
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

    function test_countup_without_option() {
        delete_option( self::NPMCOUNTER );
        $this->plugin->countUp();
        $this->assertSame( 1, intval( get_option( self::NPMCOUNTER ) ) );
        $this->plugin->countUp();
        $this->assertSame( 2, intval( get_option( self::NPMCOUNTER ) ) );
    }

    function test_countup_not_autoloaded_option() {
        delete_option( self::NPMCOUNTER );
        add_option( self::NPMCOUNTER, 5000, '', false );
        get_option( self::NPMCOUNTER );
        $this->plugin->countUp();
        $this->assertSame( 5001, intval( get_option( self::NPMCOUNTER ) ) );
    }

    // 別のリクエストが先に加算していても、その分を失わない
    function test_countup_keeps_concurrent_increment() {
        global $wpdb;
        update_option( self::NPMCOUNTER, 5000 );
        get_option( self::NPMCOUNTER );
        $wpdb->update( $wpdb->options, array( 'option_value' => '5001' ), array( 'option_name' => self::NPMCOUNTER ) );
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

    function test_count_on_first_visit() {
        $counter = $this->newVisit( true );
        $counter->mySetCookie();
        $this->assertSame( 5001, intval( get_option( self::NPMCOUNTER ) ) );
    }

    function test_not_count_when_cookie_cannot_be_set() {
        $counter = $this->newVisit( false );
        $counter->mySetCookie();
        $counter->mySetCookie();
        $this->assertNotCounted();
    }

    function test_count_on_template_redirect() {
        $this->assertNotFalse( has_action( 'template_redirect', array( $this->plugin, 'mySetCookie' ) ) );
        $this->assertFalse( has_action( 'init', array( $this->plugin, 'mySetCookie' ) ) );
    }

    function test_not_count_in_admin() {
        $counter = $this->newVisit( true );
        set_current_screen( 'dashboard' );
        $counter->mySetCookie();
        set_current_screen( 'front' );
        $this->assertNotCounted();
    }

    function test_not_count_on_ajax() {
        $counter = $this->newVisit( true );
        add_filter( 'wp_doing_ajax', '__return_true' );
        $counter->mySetCookie();
        $this->assertNotCounted();
    }

    function test_not_count_on_cron() {
        $counter = $this->newVisit( true );
        add_filter( 'wp_doing_cron', '__return_true' );
        $counter->mySetCookie();
        $this->assertNotCounted();
    }

    function test_not_count_crawler() {
        $counter = $this->newVisit( true );
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
        $counter->mySetCookie();
        $this->assertNotCounted();
    }

    function test_not_count_without_user_agent() {
        $counter = $this->newVisit( true );
        unset( $_SERVER['HTTP_USER_AGENT'] );
        $counter->mySetCookie();
        $this->assertNotCounted();
    }

    // Cookie を持たない通常のブラウザからの初回アクセスを用意する
    private function newVisit( $cookieResult ) {
        update_option( self::NPMCOUNTER, 5000 );
        unset( $_COOKIE[ self::NPMCOUNTER ] );
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Safari/605.1.15';
        return new NpmCounterTestDouble( $cookieResult );
    }

    private function assertNotCounted() {
        $this->assertSame( 5000, intval( get_option( self::NPMCOUNTER ) ) );
    }

}

// Cookie の送信結果を差し替えるためのテスト用クラス
class NpmCounterTestDouble extends NpmCounter {

    private $cookieResult;

    public function __construct( $cookieResult ) {
        $this->cookieResult = $cookieResult;
    }

    protected function sendCookie() {
        return $this->cookieResult;
    }

}
