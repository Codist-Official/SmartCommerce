<?php 
namespace SmartCommerce;

class Shortcode {

 
    /**
     * Instance 
     */
    private static $_instance;

    /**
     * Constructor 
     * 
     * @return void 
     * @since 1.0.0
     */
    public function __construct() 
    {
        add_shortcode('sc_dashboard', [$this, 'dashboard']);
        add_shortcode('sc_checkout', [$this, 'checkout']);
    }

    /**
     * Initialize instance 
     * 
     * @return self 
     * @since 1.0.0
     */
    public static function instance()
    {
        if( self::$_instance == null ) self::$_instance = new self();
        return self::$_instance;
    }

    /**
     * Dashboard page 
     * 
     * @return string 
     * @since 1.0.0
     * @access public 
     */
    public function dashboard()
    {
        $frontend = new Frontend();
        return $frontend->showDashboard();
    }
    
}

Shortcode::instance();