<?php 
namespace SmartCommerce;

class DeliveryPartner extends Post {

    public $post_type = 'sc_delivery_partner';
    public $post_slug = 'sc-delivery-partner';
    public $post_name = 'Delivery Partner';

    public $api_key;
    public $api_secret;
    public $api_url;

    protected $order_id;
    protected $order;

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
        if($this->id){
            $this->setupApiData();
        }

        // Filter PUblish Fields
        add_filter( 'smartcommerce_filter_'.$this->post_type.'_publish_fields', array( $this, 'filterPublishFields' ) );

    }

    /**
     * Get delivery partner list 
     * 
     * @return array
     * @since 1.0.1
     */
    public static function getDeliveryPartnerList($sanitized = false)
    {
        $partners = ['Steadfast', 'RedX', 'Pathao Courier', 'eCourier', 'Delivery Tiger'];
        if(!$sanitized) return $partners;
        $partner_list = [];
        foreach($partners as $partner){
            $k = strtolower(trim(str_replace(' ', '-', $partner)));
            $partner_list[$k] = $partner;
        }
        return $partner_list;
        
    }

    /**
     * Set up API Data  
     * 
     * @return void
     * @since 1.0.0
     */
    public function setupApiData()
    {
        $this->api_key = isset($this->metadata['api_key']) ? $this->metadata['api_key'][0] : '';
        $this->api_secret = isset($this->metadata['api_secret']) ? $this->metadata['api_secret'][0] : '';
        $this->api_url = isset($this->metadata['api_url']) ? $this->metadata['api_url'][0] : '';
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
     * FIlter Publish Fields
     * 
     * @param array $fields
     * @return array
     * @since 1.0.0
     */
    public function filterPublishFields($fields)
    {
        unset($fields['submit']);
        $fields['post_title'] = array(
            'type' => 'select',
            'name' => 'post_title',
            'settings' => array(
                'label' => __('Title', 'smartcommerce'),
                'value' => isset($this->post->post_title) ? $this->post->post_title : '',
                'options' => self::getDeliveryPartnerList(),
                'required' => true,
                'class' => 'regular-text',
            ),
        );
        $fields['api_key'] = array(
            'type' => 'text',
            'name' => 'api_key',
            'settings' => array(
                'label' => __('API Key', 'smartcommerce'),
                'value' => isset($this->metadata['api_key']) ? $this->metadata['api_key'][0] : '',
                'placeholder' => __('Enter API Key', 'smartcommerce'),
                'required' => true,
                'class' => 'regular-text',
            ),
        );
        $fields['api_secret'] = array(
            'type' => 'text',
            'name' => 'api_secret',
            'settings' => array(
                'label' => __('API Secret', 'smartcommerce'),
                'value' => isset($this->metadata['api_secret']) ? $this->metadata['api_secret'][0] : '',
                'placeholder' => __('Enter API Secret', 'smartcommerce'),
                'required' => true,
                'class' => 'regular-text',
            ),
        );
        $fields['api_url'] = array(
            'type' => 'text',
            'name' => 'api_url',
            'settings' => array(
                'label' => __('API URL', 'smartcommerce'),
                'value' => isset($this->metadata['api_url']) ? $this->metadata['api_url'][0] : '',
                'required' => true,
            ),
        );
        $fields['submit'] = array(
            'type' => 'submit',
            'name' => 'submit',
            'settings' => array(
                'value' => __( $this->id ? 'Update' : 'Publish', 'smartcommerce'),
                'class' => 'button button-primary',
            ),
        );

        return $fields;
    }

        /**
     * Get Header 
     * 
     * @return array
     * @since 1.0.0
     */
    public function getHeader(){
        return [];
    }

    /**
     * Create Single Request 
     * 
     * @return array
     * @since 1.0
     */
    public static function createDeliveryRequest($order_id=0)
    {
        if(!$order_id) return array('status' => false, 'message' => 'Order ID is required');
        // Getting delivery partner id from order 
        $partner_id = get_post_meta($order_id, 'delivery_partner_id', true);
        if(!$partner_id) return array('status' => false, 'message' => 'Delivery Partner is required');
        $partner = strtolower(trim(get_the_title($partner_id)));
        switch($partner){
            case 'steadfast':
                $partner = new DeliverySteadFast($order_id);
                return $partner->createSingleRequest();
            default:
                return array('status' => false, 'message' => 'Invalid partner');
        }
    }

    /**
     * Check Status 
     * 
     * @return array
     * @since 1.0.0
     */
    public static function checkDeliveryStatus($order_id=0)
    {
        if(!$order_id) return;
        // Getting delivery partner id from order 
        $partner_id = get_post_meta($order_id, 'delivery_partner_id', true);
        if(!$partner_id) return;
        $partner = strtolower(trim(get_the_title($partner_id)));
        switch($partner){
            case 'steadfast':
                $partner = new DeliverySteadFast($order_id);
                return $partner->checkStatus($order_id);
                break;
            default:
                return;
        }
    }

    /**
     * Get Delivery Consignment ID
     * 
     * @return string
     * @since 1.0.0
     */
    public static function getDeliveryConsignmentId($order_id=0)
    {
        if(!$order_id) return;

        // Getting delivery partner id from order 
        $partner_id = get_post_meta($order_id, 'delivery_partner_id', true);
        if(!$partner_id) return;
        
        $partner = strtolower(trim(get_the_title($partner_id)));
        switch($partner){
            case 'steadfast':
                $partner = new DeliverySteadFast($order_id);
                return $partner->getConsignmentId();
                break;
            default:
                return;
        }
    }

    /**
     * Create rquests 
     * 
     * @return array 
     * @since 1.0.0
     */
    public static function createRequests($order_ids=[])
    {

    }

    /**
     * Get Charge List 
     * 
     * @return array 
     * 
     * @since 1.0.0
     * @access public 
     * @static 
     */
    public static function getChargeList()
    {
        $inside_dhaka = Settings::get('delivery_charge_dhaka');
        $outside_dhaka = Settings::get('delivery_charge_outside_dhaka');
        $currency = Settings::get('currency_symbol');
        $inside_dhaka_label = __('Inside Dhaka', 'smartcommerce') . ' (' . $currency . SmartCommerce::convertENToBN($inside_dhaka) . ')';
        $outside_dhaka_label = __('Outside Dhaka', 'smartcommerce') . ' (' . $currency . SmartCommerce::convertENToBN($outside_dhaka) . ')';
        return array(
            $inside_dhaka => $inside_dhaka_label,
            $outside_dhaka => $outside_dhaka_label,
        );
    }
}

DeliveryPartner::instance();