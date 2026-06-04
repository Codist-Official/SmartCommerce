<?php 
namespace SmartCommerce;

class Brand extends Post {

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
    public function __construct( $id = 0 ) 
    {
        parent::__construct($id);
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

}

Brand::instance();