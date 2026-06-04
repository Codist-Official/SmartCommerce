<?php 
namespace SmartCommerce;

defined('ABSPATH') || exit;

class Tracking {

    /**
     * $_instance 
     */
    private static $_instance;

    /**
     * __construct 
     */
    public function __construct()
    {
    }

    /**
     * Initialize instance 
     */
    public static function instance()
    {
        if( self::$_instance == null ) self::$_instance = new self();
        return self::$_instance;
    }
    /**
     * Search order id by tracking id 
     * 
     * @param string $tracking_id   
     * 
     * @return int 
     * @since 1.0.0
     * @access public
     */
    public static function searchOrderId( $tracking_id = '' )
    {
        if(empty($tracking_id)) return 0;
        $order = get_posts(array(
            'post_type' => 'sc_order',
            'meta_key' => 'tracking_id',
            'meta_value' => $tracking_id,
        ));
        if(empty($order)) return 0;
        return $order[0]->ID;
    }
}
Tracking::instance();