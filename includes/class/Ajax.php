<?php 
namespace SmartCommerce;

class Ajax {

 
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
        add_action( 'wp_ajax_smartcommerce_ajax', [$this, 'ajax'] );
        add_action( 'wp_ajax_nopriv_smartcommerce_ajax', [$this, 'ajax'] );
        add_action( 'wp_ajax_get_nonce', [$this, 'createNonce'] );
        add_action( 'wp_ajax_nopriv_get_nonce', [$this, 'createNonce'] );
    }

    /**
     * Generate nonce 
     * 
     * @return void 
     * @since 1.0.0
     */
    function createNonce() {
        echo wp_create_nonce( 'smartcommerce' );
        wp_die();
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
     * Ajax 
     * 
     * @return void 
     */
    public function ajax()
    {
        check_ajax_referer('smartcommerce', '_wpnonce');
        $method = sanitize_text_field($_REQUEST['ajax_action']);
        if(!method_exists($this, $method)){
            wp_send_json_error(array('message'=>'Invalid method ' . $method));
        }
        $result = $this->$method();
        if($result['status']){
            wp_send_json_success($result);
        }else{
            wp_send_json_error($result);
        }
    }

    /**
     * Publish Post 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function publishPost($data = [])
    {
        if(empty($data)) $data = $_REQUEST;
        $id = sanitize_text_field($data['id'] ?? 0);
        $post_type = sanitize_text_field($data['post_type'] ?? '');

        $post = null;

        switch($post_type){
            case 'sc_product':
                $post = new Product($id);
                break;
            case 'sc_brand':
                $post = new Brand($id);
                break;
            case 'sc_delivery_partner':
                $post = new DeliveryPartner($id);
                break;
            case 'sc_order':
                $post = new Order($id);
                break;
            case 'sc_user':
                $post = new User($id);
                break;      
            default:
                return ['status' => false, 'data' => 'Invalid post type'];
        }

        return $id ? $post->edit($data) : $post->publish($data);
    }

    /**
     * Edit post 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function editPost()
    {
        return $this->publishPost($_REQUEST);
    }

    /**
     * Duplicate post 
     * 
     * @return arary 
     * 
     * @since 1.0.0
     */
    public function duplicatePost()
    {
        $id = sanitize_text_field($_POST['id']);
        $post_type = sanitize_text_field($_POST['post_type']);

        switch($post_type){
            case 'sc_product':
                $post = new Product($id);
                break;
            case 'sc_brand':
                $post = new Brand($id);
                break;
            case 'sc_delivery_partner':
                $post = new DeliveryPartner($id);
                break;
            case 'sc_order':
                $post = new Order($id);
                break;
            case 'sc_user':
                $post = new User($id);
                break;
            default:
                return ['status' => false, 'data' => 'Invalid post type'];
        }

        return $post->duplicate($id);
    }

    /**
     * Delete post 
     * 
     * @return array    
     * 
     * @since 1.0.0
     */
    public function deletePost()
    {
        $id = sanitize_text_field($_POST['id']);
        $post_type = sanitize_text_field($_POST['post_type']);

        switch($post_type){
            case 'sc_product':
                $post = new Product();
                break;
            case 'sc_brand':
                $post = new Brand();
                break;
            case 'sc_delivery_partner':
                $post = new DeliveryPartner();
                break;
            case 'sc_order':
                $post = new Order();
                break;
            case 'sc_user':
                $post = new User();
                break;
            default:
                return ['status' => false, 'message' => 'Invalid post type'];
        }

        return $post->delete($id);
    }

    /**
     * Login user 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function userLogin()
    {
        $username = sanitize_text_field($_POST['username'] ?? '');
        $password = sanitize_text_field($_POST['password'] ?? '');
        $redirect_to = sanitize_text_field($_POST['redirect_to'] ?? '');
        if(empty($username) || empty($password)) return array(
            'status' => false,
            'message' => __('Username and password are required', 'smartcommerce'),
        );
        return User::doLogin($username, $password, $redirect_to);
    }


    /**
     * Publish Taxonomy 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function publishTaxonomy()
    {
        $taxonomy = sanitize_text_field($_POST['taxonomy'] ?? '');
        switch($taxonomy){
            case 'sc_product_category':
                $taxonomy = new ProductCategory();
                break;
            default:
                return ['status' => false, 'message' => 'Invalid taxonomy'];
        }
        return $taxonomy->publish($_REQUEST);
    }

    /**
     * Edit Taxonomy 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function editTaxonomy()
    {
        $taxonomy = sanitize_text_field($_POST['taxonomy'] ?? '');
        $term_id = sanitize_text_field($_POST['term_id'] ?? '');
        switch($taxonomy){
            case 'sc_product_category':
                $taxonomy = new ProductCategory($term_id);
                break;
            default:
                return ['status' => false, 'message' => 'Invalid taxonomy'];
        }
        return $taxonomy->edit($_REQUEST);
    }

    /**
     * Delete Taxonomy 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function deleteTaxonomy()
    {
        $taxonomy = sanitize_text_field($_POST['taxonomy'] ?? '');
        $term_id = sanitize_text_field($_POST['term_id'] ?? '');
        switch($taxonomy){
            case 'sc_product_category':
                $taxonomy = new ProductCategory($term_id);
                break;
            default:
                return ['status' => false, 'message' => 'Invalid taxonomy'];
        }
        return $taxonomy->delete();
    }

    /**
     * Save Settings 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function saveSettings()
    {
        $skip_fields = array(
            'action',
            'ajax_action',
            '_wpnonce',
            'before_send_callback',
            'success_callback',
            'error_callback',
            'complete_callback',
        );
        foreach($_REQUEST as $key => $value){
            if(in_array($key, $skip_fields)) continue;
            Settings::update($key, $value);
        }
        return ['status' => true, 'message' => __('Settings saved successfully', 'smartcommerce')];
    }

    /**
     * Bulk Delete Post 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function bulkDelete()
    {
        $post_type = sanitize_text_field($_POST['post_type']);
        $ids = $_POST['id'];
        switch($post_type){
            case 'sc_product':
                $post = new Product();
                break;
            case 'sc_brand':
                $post = new Brand();
                break;
            case 'sc_delivery_partner':
                $post = new DeliveryPartner();
                break;
            case 'sc_order':
                $post = new Order();
                break;
            case 'sc_user':
                $post = new User();
                break;
            default:
                return ['status' => false, 'message' => 'Invalid post type'];
        }
        return $post->bulkDelete($ids);
    }

    /**
     * Print Invoice 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function printInvoice()
    {
        $id = sanitize_text_field($_POST['post_id']);
        $order = new Order($id);
        $data = $order->getInvoice();
        return ['status' => true, 'message' => 'Invoice printed successfully', 'data' => $data];
    }

    /**
     * Update inline meta value 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function updateInlineMetaValue()
    {
        $id = sanitize_text_field($_POST['id']);
        $meta_key = sanitize_text_field($_POST['meta_key']);
        $meta_value = sanitize_text_field($_POST['meta_value']);
        $post_type = sanitize_text_field($_POST['post_type']);
        if($post_type == 'sc_order'){
            $update = Order::updatePostMeta($id, $meta_key, $meta_value);
            if($meta_key == 'delivery_partner_id'){
                DeliveryPartner::createDeliveryRequest($id);
            }
        } else {
            $update = update_post_meta($id, $meta_key, $meta_value);
        }
        return ['status' => $update, 'id' => $id, 'payload'=>$_REQUEST, 'message' => $update ? __('Updated Successfully!', 'smartcommerce') : __('Failed to update!', 'smartcommerce')];
    }

    /**
     * Bulk Print 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function bulkPrint()
    {
        $ids = $_REQUEST['id'];
        $post_type = sanitize_text_field($_REQUEST['post_type']);
        $action_value = sanitize_text_field($_REQUEST['action_value']);
        $data = '';
        switch($action_value){
            case 'print_invoice':
                if(!empty($ids)){
                    foreach($ids as $id){
                        $order = new Order($id);
                        $data .= $order->getInvoice();
                    }
                }
                break;
            default:
                return ['status' => false, 'message' => 'Invalid post type'];
            break;
        }
        return ['status' => true, 'message' => 'Printed successfully', 'data' => $data];
    }

    /**
     * Bulk change order status 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function bulkChangeOrderStatus()
    {
        $ids = $_REQUEST['id'];
        $action_value = sanitize_text_field($_REQUEST['action_value']);
        if(empty($ids)) return ['status' => false, 'message' => 'No ids found', 'payload' => $_REQUEST];
        if(empty($action_value)) return ['status' => false, 'message' => 'No action value found', 'payload' => $_REQUEST];
        foreach($ids as $id){
            Order::updatePostMeta($id, 'order_status', $action_value);
        }
        return ['status' => true, 'message' => 'Changed status successfully', 'payload' => $_REQUEST, 'ids'=>$ids];
    }

    /**
     * Show an order 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function viewOrder()
    {
        $id = sanitize_text_field($_POST['id']);
        $order = new Order($id);
        $data = $order->getInvoice();
        return ['status' => true, 'message' => 'Order viewed successfully', 'data' => $data];
    }

    /**
     * Register Delivery Partner 
     * 
     * @return array
     * @since 1.0.0
     */
    public function forceRegisterDeliveryPartner()
    {
        $order_id = intval($_POST['post_id']);
        $order = new Order($order_id);
        $partner_id = $order->getDeliveryPartnerId();
        $partner_title = strtolower(trim(get_the_title($partner_id)));
        switch($partner_title){
            case 'steadfast':
                $partner = new DeliverySteadFast($order_id);
                return $partner->createSingleRequest(true);
                break;
            default:
                return ['status' => false, 'message' => 'Invalid Delivery Partner'];
        }
    }

    /**
     * Show Order Form on Click 
     * 
     * @return array 
     * 
     * @since 1.0.0
     */
    public function showOrderFormOnClick()
    {
        $id = sanitize_text_field($_POST['id']);
        $data = Order::getOrderForm($id, true);
        return ['status' => true, 'message' => 'Order form shown successfully', 'data' => $data, 'payload' => $_POST];
    }

    /**
     * Show Login Form 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function showLoginForm()
    {
        $redirect_to = sanitize_text_field($_POST['redirect_to'] ?? '');
        return ['status' => true, 'title' => 'Login', 'message' => 'Login form shown successfully', 'data' => User::getLoginForm(false, $redirect_to)];
    }

    /**
     * Flush Rewrite Rules 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function flushRewriteRules()
    {
        flush_rewrite_rules();
        return ['status' => true, 'message' => 'Rewrite rules flushed successfully'];
    }

    /**
     * Show Similar Products 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function showSimilarProducts()
    {
        $id = sanitize_text_field($_REQUEST['id']);
        $data = Product::showSimilarProducts($id, __('Similar Products Picked for You', 'smartcommerce'), false);
        wp_die($data);
    }

    public function popupProductViews()
    {
        $id = sanitize_text_field($_REQUEST['id']);
        $data = Post::getStatistics($id, 'views');
        return ['status' => true, 'message' => 'Page views details shown successfully', 'data' => $data];
    }

    public function popupProductOrders()
    {
        $id = sanitize_text_field($_REQUEST['id']);
        $data = Post::getStatistics($id, 'orders');
        return ['status' => true, 'message' => 'Page orders details shown successfully', 'data' => $data];
    }

    public function showSmsSendScreen()
    {
        $type = sanitize_text_field($_REQUEST['send_type']);
        return ['status' => true, 'message' => 'SMS send screen shown successfully', 'data' => Sms::getSendSmsScreen($type)];
    }

    public function sendBulkSms()
    {
        $type = sanitize_text_field($_REQUEST['type']);
        $numbers = [];
        if( in_array($type, ['mobile_numbers', 'all_customers'] )){
            $numbers = $_REQUEST['sms_mobile_numbers'];
            // convert textarea line break into new lines
            $numbers = explode("\r\n", $numbers);

            // convert no into string 
            $numbers = array_map('strval', $numbers);
            $numbers = array_map('trim', $numbers);
            $numbers = array_unique(array_filter($numbers));
            $message = sanitize_text_field($_REQUEST['sms_message']);
            if(!empty($numbers)){
                foreach($numbers as $number){
                    Sms::send($number, $message);
                }    
            }
            return ['status' => true, 'message' => 'SMS sent successfully', 'data' => $numbers];
        } else if( $type == 'order_status'){

        }
        return ['status' => true, 'message' => 'SMS sent successfully', 'data' => '', 'numbers' => $numbers];        
    }

    public function loadAllCategoryWisePosts()
    {
        // Get all taxonomies 
        $taxonomies = get_terms(array(
            'taxonomy' => 'sc_product_category',
            'hide_empty' => true,
        ));
        $post = new Post();
        $data = '';
        foreach($taxonomies as $taxonomy){
            $data .= $post->showCategoryWisePosts(['tax_id' => $taxonomy->term_id, 'posts_per_page' => 5]);
        }
        return ['status' => true, 'message' => 'Category wise posts loaded successfully', 'data' => $data];
    }

    public function showUserRegisterForm()
    {
        return ['status' => true, 'title' => 'Login', 'message' => 'Login form shown successfully', 'data' => User::getRegisterForm()];
    }

    public function insertDeliveryChoicesWithPrice()
    {
        $districts = SmartCommerce::getDistrict();
        $language = get_locale();
        $districts_formatted = $language == 'en_US' ? array_keys($districts) : array_values($districts);
        $default_delivery_price = Settings::get('delivery_default_charge');
        // add price after each district with colon : 
        $districts_formatted = array_map(function($district) use ($default_delivery_price){
            return $district . ': ' . $default_delivery_price;
        }, $districts_formatted);
        $districts_formatted = implode("\r\n", $districts_formatted);
        return ['status' => true, 'message' => 'Districts inserted successfully', 'data' => $districts_formatted];
    }
}

Ajax::instance();