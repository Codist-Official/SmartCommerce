<?php 
namespace SmartCommerce;


class Debug {

    public $post_type = 'sc_brand';
    public $post_slug = 'sc-brand';
    public $post_name = 'Brand';

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
    public function __construct( ) 
    {
        add_shortcode('sc_debug', [$this, 'shortcode']);
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
     * Shortcode 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function shortcode()
    {
        var_dump(MetaPixel::getClientIp());
    }
}

Debug::instance();