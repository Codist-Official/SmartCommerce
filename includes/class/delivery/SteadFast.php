<?php 
namespace SmartCommerce;

defined('ABSPATH') || die();

class SteadFast extends DeliveryPartner{

    /**
     * $_instance 
     */
    private static $_instance = null;

    protected $order;

    /**
     * __construct 
     * 
     * @param int $id
     */
    public function __construct($order_id=0){
        parent::__construct(0);
        if($order_id){
            $this->order = new Order($order_id);
            $this->id = $this->order->getDeliveryPartnerId();
            $this->post = get_post($this->id);
            $this->metadata = get_metadata('post', $this->id);
            $this->setupApiData();
        }
    }

    /**
     * Initiailze Instance 
     * 
     * @return DeliverySteadFast
     * @since 1.0.0
     */
    public static function instance(){
        if(self::$_instance == null) self::$_instance = new self();
        return self::$_instance;
    }

    /**
     * Get Header 
     * 
     * @return array
     * @since 1.0.0
     */
    public function getHeader(){
        return array(
            'Api-Key' => $this->api_key,
            'Secret-Key' => $this->api_secret,
            'Content-Type' => 'application/json',
        );
    }

    /**
     * Create single request 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function createSingleRequest($force = false)
    {
        // First Checking if we have already created request for this order with this partner
        if(!$force){
            $check_exists = get_post_meta($this->order->id, 'delivery_data_'.$this->order->getDeliveryPartnerId(), true);
            if($check_exists) return $check_exists;
        }
        
        $data = array(
            'recipient_name' => $this->order->getDeliveryInfo('delivery_name'),
            'recipient_phone' => $this->order->getDeliveryInfo('delivery_mobile'),
            'recipient_address' => $this->order->getDeliveryInfo('delivery_address'),
            'invoice' => $this->order->id,
            'cod_amount' => $this->order->getDueAmount(),
        );
        $response = wp_remote_post($this->api_url.'/create_order', array(
            'headers' => $this->getHeader(),
            'body' => json_encode($data),
        ));
        $response_code = wp_remote_retrieve_response_code($response);
        $response_message = wp_remote_retrieve_response_message($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        $status = $response_code == 200 ? 1 : 0;
        $data = array(
            'status' => $status,
            'data' => $response_body,
            'response_message' => $response_message,
            'response_code' => $response_code,
        );
        $key = 'delivery_data_'.$this->order->getDeliveryPartnerId();
        update_post_meta($this->order->id, $key, $data);
        update_post_meta($this->order->id, 'delivery_status_'.$this->order->getDeliveryPartnerId(), 'awaiting_pickup');
        $this->order->updatePostMeta($this->order->id, 'delivery_partner', $this->order->getDeliveryPartnerId());
        return $data;
    }

    /**
     * Check Status
     * 
     * @return void
     * @since 1.0.0
     */
    public function checkStatus($key = 'invoice', $value = '')
    {
        $url = '';
        if(empty($value)) $value = $this->order->id;

        if($key == 'consignment') $url = $this->api_url.'/status_by_cid/'.$value;
        else if($key == 'tracking_code') $url = $this->api_url.'/status_by_trackingcode/'.$value;
        else $url = $this->api_url.'/status_by_invoice/'.$value;

        $request = wp_remote_get($url, array(
            'headers' => $this->getHeader(),
        ));
        $response_code = wp_remote_retrieve_response_code($request);
        $response_message = wp_remote_retrieve_response_message($request);
        $response_body = json_decode(wp_remote_retrieve_body($request), true);
        $status = $response_code == 200 ? 1 : 0;
        $data = array(
            'status' => $status,
            'data' => $response_body,
            'response_message' => $response_message,
            'response_code' => $response_code,
        );
        return $data;
    }

    /**
     * Update delivery status
     * 
     * @return void
     * @since 1.0.0
     */
    public function updateStatus($status='')
    {
        $key = 'delivery_status_'.$this->order->getDeliveryPartnerId();
        $status = $this->order->getDeliveryStatus($key);
        $final_status = ['delivered', 'cancelled', 'partial_delivered'];
        if(!in_array($status, $final_status)){
            $response = $this->checkStatus();
            if($response['status'] == 1){
                $status = $response['data']['delivery_status'];
                update_post_meta($this->order->id, $key, $status);
            }
        }
        return $status;
    }

    public function getStatus()
    {
        $key = 'delivery_status_'.$this->order->getDeliveryPartnerId();
        return isset($this->order->metadata[$key]) ? $this->order->metadata[$key][0] : '';
    }

    public function getTrackingCode()
    {
        $key = 'delivery_data_'.$this->order->getDeliveryPartnerId();
        $data = isset($this->order->metadata[$key]) ? $this->order->metadata[$key][0] : '';
        $data = maybe_unserialize($data);
        return $data['data']['consignment']['tracking_code'] ?? '';
    }

    public function getConsignmentId()
    {
        $key = 'delivery_data_'.$this->order->getDeliveryPartnerId();
        $data = isset($this->order->metadata[$key]) ? $this->order->metadata[$key][0] : '';
        $data = maybe_unserialize($data);
        return $data['data']['consignment']['consignment_id'] ?? '';
    }
}

SteadFast::instance();