<?php 
namespace SmartCommerce;

class Order extends Post {

    public $post_type = 'sc_order';
    public $post_slug = 'sc-order';
    public $post_name = 'Order';
    public $posts_per_page = 10;

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


    public function wpInit()
    {
        // filter register post type args 
        add_filter('smartcommerce_filter_'.$this->post_type.'_register_args', [$this, 'filterRegisterPostTypeArgs'], 10, 1);

        // Filter List Fields 
        add_filter('smartcommerce_filter_'.$this->post_type.'_list_fields', [$this, 'filterListFields'], 10, 1);

        // filter meta value
        add_filter('smartcommerce_filter_'.$this->post_type.'_list_meta_value', [$this, 'filterListMetaValue'], 10, 4);

        // before list html 
        add_filter('smartcommerce_filter_'.$this->post_type.'_list_before_html', [$this, 'filterListBeforeHtml'], 10, 1);

        // Filter List Query Args 
        add_filter('smartcommerce_filter_'.$this->post_type.'_list_query_args', [$this, 'filterListQueryArgs'], 10, 1);

        // Filter Bulk Actions
        add_filter('smartcommerce_filter_'.$this->post_type.'_bulk_actions_html', [$this, 'filterBulkActionsHtml'], 10, 1);
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
     * Filter register post type args 
     * 
     * @param array $args 
     * @return array 
     * @since 1.0.0
     */
    public function filterRegisterPostTypeArgs( $args )
    {
        $args['publicly_queryable'] = false;
        return $args;
    }

    /**
     * Filter List Fields 
     * 
     * @param array $fields 
     * @return array 
     * @since 1.0.0
     */
    public function filterListFields( $fields )
    {
        $args = array(
            'post_id' => __('ID', 'smartcommerce'),
            'product_details' => __('Product Details', 'smartcommerce'),
            'order_details' => __('Order Details', 'smartcommerce'),
            'amount' => __('Amount', 'smartcommerce'),
            'actions' => __('Actions', 'smartcommerce'),
        );
        return $args;
    }

    /**
     * Filter List Query Args
     * 
     * @param array $args 
     * @return array 
     * @since 1.0.0
     */
    public function filterListQueryArgs($args)
    {
        $order_status = isset($_GET['order_status']) ? $_GET['order_status'] : '';
        if($order_status !== 'all'){
            $args['meta_key'] = 'order_status';
            $args['meta_value'] = $order_status;
        }
        return $args;
    }

    /**
     * Filter List Meta Value 
     * 
     * @param string $value 
     * @param string $key 
     * @param object $post 
     * @return string 
     * @since 1.0.0
     */
    public function filterListMetaValue( $value, $key, $post, $metadata )
    {
        if($post->post_type != $this->post_type) return $value;
        switch($key){
            case 'post_id':
                $order_status = isset($metadata['order_status']) ? $metadata['order_status'][0] : 'pending';
                $delivery_charge = isset($metadata['delivery_charge']) ? $metadata['delivery_charge'][0] : '';
                $delivery_weight = isset($metadata['order_weight']) ? $metadata['order_weight'][0] : '';

                $delivery_input = Form::generateElement('select', 'order_delivery', array(
                    'name' => 'delivery_charge',
                    'id' => 'order_delivery_charge_'.$post->ID,
                    'value' => $delivery_charge,
                    'class' => 'updateInlineMetaValue inline-input',
                    'options' => Product::getDeliveryChargeOptions($post->ID),
                    'data' => array(
                        'data-id' => $post->ID,
                        'data-meta_key' => 'delivery_charge',
                        'data-post_type' => $post->post_type,
                    ),
                    'placeholder' => __('Select Delivery Charge', 'smartcommerce'),
                ));
                $delivery_partner_input = Form::generateElement('select', 'delivery_partner_id', array(
                    'options' => DeliveryPartner::instance()->getAllPosts(array('post_status' => 'publish','orderby' => 'title','order' => 'ASC'), true),
                    'value' => isset($metadata['delivery_partner_id']) ? $metadata['delivery_partner_id'][0] : '',
                    'placeholder' => __('Select Delivery Partner', 'smartcommerce'),
                    'class' => 'updateInlineMetaValue inline-select',
                    'data' => array(
                        'data-id' => $post->ID,
                        'data-meta_key' => 'delivery_partner_id',
                        'data-post_type' => $post->post_type,
                    ),
                ));
                $weight_input = Form::generateElement('select', 'order_weight', array(
                    'placeholder' => __('Select Weight', 'smartcommerce'),
                    'name' => 'order_weight',
                    'id' => 'order_weight_'.$post->ID,
                    'value' => $delivery_weight,
                    'class' => 'updateInlineMetaValue inline-select',
                    'data' => array(
                        'data-id' => $post->ID,
                        'data-meta_key' => 'order_weight',
                        'data-post_type' => $post->post_type,
                    ),
                    'options' => array(
                        '0.5' => '0.5 kg',
                        '1' => '1 kg',
                        '1.5' => '1.5 kg', 
                        '2' => '2 kg',
                        '2.5' => '2.5 kg',
                        '3' => '3 kg',
                        '3.5' => '3.5 kg',
                        '4' => '4 kg',
                        '4.5' => '4.5 kg',
                        '5' => '5 kg',
                        '5.5' => '5.5 kg',
                        '6' => '6 kg',
                        '6.5' => '6.5 kg',
                        '7' => '7 kg',
                        '7.5' => '7.5 kg',
                        '8' => '8 kg',
                        '8.5' => '8.5 kg',
                        '9' => '9 kg',
                        '9.5' => '9.5 kg',
                        '10' => '10 kg',
                    )
                ));
                $status_input = Form::generateElement('select', 'order_status', array(
                    'name' => 'order_status',
                    'id' => 'order_status_'.$post->ID,
                    'options' => self::getOrderStatusList(),
                    'value' =>$order_status,
                    'placeholder' => __('Update Order Status', 'smartcommerce'),
                    'class' => 'updateInlineMetaValue inline-select',
                    'data' => array(
                        'data-id' => $post->ID,
                        'data-meta_key' => 'order_status',
                        'data-post_type' => $post->post_type,
                    )
                ));

                $discount_input = Form::generateElement('text', 'order_discount', array(
                    'name' => 'order_discount',
                    'id' => 'order_discount_'.$post->ID,
                    'value' => isset($metadata['order_discount']) ? $metadata['order_discount'][0] : 0,
                    'placeholder' => __('Enter Discount', 'smartcommerce'),
                    'class' => 'updateInlineMetaValue inline-input',
                    'data' => array(
                        'data-id' => $post->ID,
                        'data-meta_key' => 'order_discount',
                        'data-post_type' => $post->post_type,
                    )
                ));
                $value = "<input name='id[]' type='checkbox' value='{$post->ID}' id='id_{$post->ID}'><label for='id_{$post->ID}'>#{$post->ID}</label><br>";
                $value .= "<div class='sc-inline-form-field'><label for='order_status_".$post->ID."'>" . __('Status', 'smartcommerce') . "</label> {$status_input}</div>";
                $value .= "<div class='sc-inline-form-field'><label for='order_delivery_charge_".$post->ID."'>" . __('Delivery Charge', 'smartcommerce') . "</label> {$delivery_input}</div>";
                $value .= "<div class='sc-inline-form-field'><label for='order_delivery_partner_".$post->ID."'>" . __('Delivery Partner', 'smartcommerce') . "</label> {$delivery_partner_input}</div>";
                $value .= "<div class='sc-inline-form-field'><label for='order_weight_".$post->ID."'>" . __('Weight', 'smartcommerce') . "</label> {$weight_input}</div>";
                $value .= "<div class='sc-inline-form-field'><label for='order_discount_".$post->ID."'>" . __('Discount', 'smartcommerce') . "</label> {$discount_input}</div>";
                break;

            case 'order_details':
                $delivery_name = isset($metadata['delivery_name']) ? $metadata['delivery_name'][0] : '';
                $delivery_mobile = isset($metadata['delivery_mobile']) ? $metadata['delivery_mobile'][0] : '';
                $delivery_address = isset($metadata['delivery_address']) ? $metadata['delivery_address'][0] : '';
                $delivery_note = isset($metadata['delivery_note']) ? $metadata['delivery_note'][0] : '';
                $value = '';
                if(!empty($delivery_name)) $value .= "<span class='sc-tag sc-delivery-name'>{$delivery_name}</span><br>";
                if(!empty($delivery_mobile)) $value .= "<span class='sc-tag sc-delivery-phone'><a href='tel:{$delivery_mobile}'>{$delivery_mobile}</a></span><br>";
                if(!empty($delivery_address)) $value .= "<span class='sc-tag sc-delivery-address'>{$delivery_address}</span><br>";
                if(!empty($delivery_note)) $value .= "<br><span class='sc-tag sc-delivery-note'><strong>" . __('Note', 'smartcommerce') . ":</strong> {$delivery_note}</span><br>";
                $value .= "<br>";
                $value .= "<span class='sc-tag sc-order-date'>" . date('h:i A, l', strtotime($post->post_date)) . '<br>' . date('d F, y', strtotime($post->post_date)) . "</span>";
                break;

            case 'amount':
                $order = new Order($post->ID);
                $total =(float) $order->getTotal();
                $paid =(float) $order->getPaidAmount();
                $due = (float) $order->getDueAmount();
                $delivery = (float) $order->getDeliveryCharge();
                $delivery = number_format($delivery, 2);
                $payable = (float) $order->getPayableAmount();
                $discount = (float) $order->getDiscount();
                $currency = Settings::get('currency_symbol', 'BDT');
                $value = "";
                $value .= "<span class='sc-tag sc-order-total'>".__("Total", 'smartcommerce').": {$currency}{$total}</span><br>";
                $value .= "<span class='sc-tag sc-order-total'>".__("Discount", 'smartcommerce').": {$currency}{$discount}</span><br>";
                $value .= "<span class='sc-tag sc-order-total'>".__("Delivery", 'smartcommerce').": {$currency}{$delivery}</span><br>";
                $value .= "<span class='sc-tag sc-order-total'>".__("Payable", 'smartcommerce').": {$currency}{$payable}</span><br>";
                $value .= "<span class='sc-tag sc-order-paid'>".__("Paid", 'smartcommerce').": {$currency}{$paid}</span><br>";
                $value .= "<span class='sc-tag sc-order-due'>".__("Due", 'smartcommerce').": {$currency}{$due}</span>";
                break;

            case 'product_details':
                $data = $this->getProductDetails($post->ID);
                if(!empty($data)){
                    $value = "<div class=''>";
                    $value .= "<table class='sc-product-details-table'>";
                    foreach($data as $product_id=>$items){
                        $product = new Product($product_id);
                        $value .= "<tbody>";
                        foreach($items as $item){
                            if($item['product_quantity'] == 0 && $item['variation_quantity'] == 0) continue;
                            if(!isset($item['variation_sku'])) $item['variation_sku'] = '';
                            if(!isset($item['product_sku'])) $item['product_sku'] = '';
                            $price = !empty($item['variation_id']) ? $item['variation_price'] : $item['product_price'];
                            $qty = !empty($item['variation_id']) ? $item['variation_quantity'] ?? 0 : $item['product_quantity'];
                            $size = !empty($item['variation_id']) ? $item['variation_size'] ?? 0 : $item['product_size'];
                            $color = !empty($item['variation_id']) ? $item['variation_color'] ?? 0 : $item['product_color'];
                            $sku = !empty($item['variation_id']) ? $item['variation_sku'] ?? 0 : $item['product_sku'];
                            if(empty($size)) $size = "N/A";
                            if(empty($color)) $color = "N/A";
                            if(empty($sku)) $sku = "N/A";
                            $product_name = $item['variation_id'] ? $item['variation_name'] : $product->post->post_title;
                            $value .= "<tr data-product_id='{$product_id}' data-variation_id='{$item['variation_id']}'>";
                            if($item['variation_id']){
                                $value .= "<td width='50'>";
                                $value .= is_numeric($item['variation_image']) ? wp_get_attachment_image($item['variation_image'], [50,50]) : "<img async loading='lazy' src='{$item['variation_image']}' width='50'>"; 
                                $value .= "</td>";
                                $value .= "<td>";
                                $value .= "<span class='sc-tag sc-product-title'><a itemprop='url' target='_blank' href='".get_the_permalink($product_id)."'>{$product_name}</a></span><br>"; 
                                $value .= "Price: {$price} | Qty: {$qty} | Size: {$size} | Color: {$color} | SKU: {$sku}";
                                $value .= "</td>";
                            } else {
                                $value .= "<td width='50'>" . wp_get_attachment_image($item['image_id'], [50,50]) . "</td>";
                                $value .= "<td>";
                                $value .= "<span class='sc-tag sc-product-title'><a itemprop='url' target='_blank' href='".get_the_permalink($product_id)."'>{$product->post->post_title}</a></span><br>"; 
                                $value .= "Price: {$price} | Qty: {$qty} | Size: {$size} | Color: {$color} | SKU: {$sku}";
                                $value .= "</td>";
                            }
                            $value .= "</tr>";
                        }
                        $value .= "</tbody>";
                    }
                    $value .= "</table>";
                    $value .= "</div>";
                }
                break;
                
            case 'actions':
                $value = "<div class='sc-icons-wrap'>";
                $value .= '<a title="Register with Delivery Partner" data-success_callback=""  href="javascript:void(0)" data-before_send_callback="scConfirm" data-post_type="'.$this->post_type.'" class="sc-ajax-link" data-ajax_action="forceRegisterDeliveryPartner" data-post_id="'.$post->ID.'">'.SmartCommerce::getIcon('truck').'</a>';
                $value .= '<a title="Print Invoice" data-success_callback="printInvoiceSuccessCallback"  href="javascript:void(0)" class="sc-ajax-link" data-ajax_action="printInvoice" data-post_id="'.$post->ID.'">'.SmartCommerce::getIcon('print').'</a>';
                $value .= '<a title="View"  href="javascript:void(0)" class="sc-ajax-link" data-success_callback="viewOrderSuccessCallback" data-ajax_action="viewOrder" data-id="'.$post->ID.'">'.SmartCommerce::getIcon('view').'</a>';
                $value .= '<a title="Edit" href="'. site_url("/dashboard/?submenu=edit&id={$post->ID}&type={$this->post_type}") .'" class="sc-icon-link">'.SmartCommerce::getIcon('edit').'</a>';
                $value .= '<a title="Delete" href="javascript:void(0)" class="sc-icon-link sc-ajax-link" data-post_type="'.$this->post_type.'" data-success_callback="deletePostSuccessCallback" data-before_send_callback="scConfirm" data-ajax_action="deletePost" data-id="'.$post->ID.'">'.SmartCommerce::getIcon('delete').'</a><br>';
                $value .= "</div>";
                $value .= Form::generateElement('textarea', 'admin_note', array(
                    'id' => 'admin_note_'.$post->ID,
                    'value' => isset($metadata['admin_note']) ? $metadata['admin_note'][0] : '',
                    'placeholder' => __('Enter Admin Note', 'smartcommerce'),
                    'class' => 'updateInlineMetaValue inline-textarea',
                    'data' => array(
                        'data-id' => $post->ID,
                        'data-meta_key' => 'admin_note',
                        'data-post_type' => $post->post_type,
                    )
                ));
                break;
        }
        return $value;
    }

    /**
     * Get Publish Form 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function getPublishForm()
    {

        ob_start();
        ?>
        <form action="" class="sc-ajax-form sc-form ">
            <div class="sc-flex-wrap">
                    <!-- left Sidebar --> 
                    <div class="flex-3">

                        <!-- Order Summary -->
                        <div class="sc-form-group">
                            <?php echo $this->getSummary(); ?>
                        </div>

                        <!-- Order Details -->
                        <div class="sc-form-group">

                            <!-- Order Status -->
                            <div class="sc-form-row">
                                <label for="order_status"><?php _e('Order Status', 'smartcommerce'); ?></label>
                                <?php echo Form::generateElement('select', 'order_status', array(
                                    'name' => 'order_status',
                                    'id' => 'order_status',
                                    'options' => self::getOrderStatusList(),
                                    'value' => isset($this->metadata['order_status']) ? $this->metadata['order_status'][0] : 'pending',
                                    'placeholder' => __('Select Status', 'smartcommerce'),
                                )); ?>
                            </div>

                            <!-- Assigned To -->
                            <div class="sc-form-row">
                                <label for="assigned_to"><?php _e('Assigned To', 'smartcommerce'); ?></label>
                                <?php echo Form::generateElement('select', 'assigned_to', array(
                                    'name' => 'assigned_to',
                                    'id' => 'assigned_to',
                                    'options' => User::getAllAdmins(),
                                    'value' => isset($this->metadata['assigned_to']) ? $this->metadata['assigned_to'][0] : '',
                                    'placeholder' => __('Select User', 'smartcommerce'),
                                )); ?>
                            </div>

                            <!-- Delivery Charge -->
                            <?php 
                                $currency = Settings::get('currency_symbol');
                                
                            ?>
                            <div class="sc-form-row">
                                <label for="delivery_charge"><?php _e('Delivery Charge', 'smartcommerce'); ?></label>
                                <?php echo Form::generateElement('select', 'delivery_charge', array(
                                    'name' => 'delivery_charge',
                                    'id' => 'delivery_charge',
                                    'options' => Product::getDeliveryChargeOptions($this->post->ID),
                                    'value' => isset($this->metadata['delivery_charge']) ? $this->metadata['delivery_charge'][0] : '',
                                    'placeholder' => __('Enter Delivery Charge', 'smartcommerce'),
                                )); ?>
                            </div>

                            <!-- Delivery Partner -->
                            <div class="sc-form-row">
                                <label for="delivery_partner_id"><?php _e('Delivery Partner', 'smartcommerce'); ?></label>
                                <?php echo Form::generateElement('select', 'delivery_partner_id', array(
                                    'name' => 'delivery_partner_id',
                                    'id' => 'delivery_partner_id',
                                    'options' => DeliveryPartner::instance()->getAllPosts(array('post_status' => 'publish','orderby' => 'title','order' => 'ASC'), true),
                                    'value' => isset($this->metadata['delivery_partner_id']) ? $this->metadata['delivery_partner_id'][0] : '',
                                    'placeholder' => __('Select Delivery Partner', 'smartcommerce'),
                                )); ?>
                             </div>

                            <!-- Order Date -->
                            <div class="sc-form-row"></div>
                        </div>
                    </div>

                    <!-- Main Content Item Details  --> 
                    <div class="flex-6">
                        <?php 
                            $product_data = maybe_unserialize($this->metadata['product_data'][0]);
                            foreach($product_data as $product_id=>$product_items){
                                $product = new Product($product_id);
                                $type = 'simple';
                                $variations = $product->getVariations();
                                if(!empty($variations)){
                                    $type = 'variable';
                                }
                                if($type == 'simple'){

                                } else {
                                    ?>

                                    <?php 
                                }
                            }
                        ?>

                    </div>

                    <!-- Right Sidebar --> 
                    <div class="flex-3">sidebar</div>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Get Order Status List
     * 
     * @return array 
     * @since 1.0.0
     */
    public static function getOrderStatusList()
    {
        return array(
            'pending' => __('Pending', 'smartcommerce'),
            'confirmed' => __('Confirmed', 'smartcommerce'),
            'ready-to-ship' => __('Ready to Ship', 'smartcommerce'),
            'shipped' => __('Shipped', 'smartcommerce'),
            'delivered' => __('Delivered', 'smartcommerce'),
            'partial-delivered' => __('Partial Delivered', 'smartcommerce'),
            'on-hold' => __('On Hold', 'smartcommerce'),
            'returned' => __('Returned', 'smartcommerce'),
            'cancelled_customer' => __('Cancelled (Customer)', 'smartcommerce'),
            'cancelled_admin' => __('Cancelled (Admin)', 'smartcommerce'),
            'cancelled_fake' => __('Cancelled (Fake Order)', 'smartcommerce'),
            'cancelled_outofstock' => __('Cancelled (Out of Stock)', 'smartcommerce'),
            'refunded' => __('Refunded', 'smartcommerce'),
            'failed' => __('Failed', 'smartcommerce'),
            'processing' => __('Processing', 'smartcommerce'),
        );
    }

    public function getTotal($formatted = false)
    {
        $total = (float) isset($this->metadata['order_total']) ? $this->metadata['order_total'][0] : 0;
        return $formatted ? number_format($total, 2) : $total;
    }

    public function getDiscount($formatted = false)
    {
        $amount = (float) isset($this->metadata['order_discount']) ? $this->metadata['order_discount'][0] : 0;
        return $formatted ? number_format($amount, 2) : (float) $amount;
    }

    public function getNet($formatted = false)
    {
        $amount = (float) $this->getTotal() - (float) $this->getDiscount() + (float) $this->getDeliveryCharge();
        return $formatted ? number_format($amount, 2) : (float) $amount;
    }

    function getDeliveryCharge($formatted = false)
    {
        $delivery = (float) isset($this->metadata['delivery_charge']) ? $this->metadata['delivery_charge'][0] : 0;
        return $formatted ? number_format($delivery, 2) : (float) $delivery;
    }

    function getPayableAmount($formatted = false)
    {
        $amount = (float) $this->getTotal() - (float) $this->getDiscount() + (float) $this->getDeliveryCharge();
        return $formatted ? number_format($amount, 2) : $amount;
    }

    function getPaidAmount($formatted = false)
    {
        $paid = (float) isset($this->metadata['order_paid']) ? $this->metadata['order_paid'][0] : 0;
        return $formatted ? number_format($paid, 2) : $paid;
    }
    
    function getDueAmount($formatted = false)
    {
        $amount = floatval($this->getPayableAmount()) - floatval($this->getPaidAmount());
        return $formatted ? number_format($amount, 2) : $amount;
    }

    public function getStatus()
    {
        $status = isset($this->metadata['order_status']) ? $this->metadata['order_status'][0] : 'pending';
        return self::getOrderStatusList()[$status];
    }

    public function getAssignedTo($type='id')
    {
        $assigned_to = isset($this->metadata['assigned_to']) ? $this->metadata['assigned_to'][0] : 'N/A';
        if( $type == 'id' ) return $assigned_to;
        if($type == 'name' && $assigned_to){
            $user = new User($assigned_to);
            return $user ? $user->user->display_name : 'N/A';
        }
        return 'N/A';
    }

    /**
     * Get Order Summary 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function getSummary()
    {
        $currency_symbol = Settings::get('currency_symbol');
        ob_start();
        ?>
        <div class="sc-dashboard-order-summary">
            <h5 class='sc-widget-title'><?php _e('Order Details', 'smartcommerce'); ?></h5>
            <ul class="sc-ul sc-dashboard-order-data">
                <li class="order_id">
                    <div class="field-label"><?php _e('ID#', 'smartcommerce'); ?></div>
                    <div class="field-value"><?php echo $this->id; ?></div>
                </li>
                <li class="order_date">
                    <div class="field-label"><?php _e('Date', 'smartcommerce'); ?></div>
                    <div class="field-value"><?php echo $this->post ? date('h:i A, d M, Y', strtotime($this->post->post_date)) : ''; ?></div>
                </li>
                <li class="order_status">
                    <div class="field-label"><?php _e('Status', 'smartcommerce'); ?></div>
                    <div class="field-value"><?php echo $this->getStatus(); ?></div>
                </li>
                <li class="order_total">
                    <div class="field-label"><?php _e('Total', 'smartcommerce'); ?></div>
                    <div class="field-value"><?php echo $currency_symbol . $this->getTotal(); ?></div>
                </li>
                <li class="order_discount">
                    <div class="field-label"><?php _e('Discount', 'smartcommerce'); ?></div>
                    <div class="field-value"><?php echo $currency_symbol . $this->getDiscount(); ?></div>
                </li>
                <li class="order_delivery">
                    <div class="field-label"><?php _e('Delivery', 'smartcommerce'); ?></div>
                    <div class="field-value"><?php echo $currency_symbol . $this->getDeliveryCharge(); ?></div>
                </li>
                <li class="order_payable">
                    <div class="field-label"><?php _e('Payable', 'smartcommerce'); ?></div>
                    <div class="field-value"><?php echo $currency_symbol . $this->getPayableAmount(); ?></div>
                </li>
                <li class="order_paid">
                    <div class="field-label"><?php _e('Paid', 'smartcommerce'); ?></div>
                    <div class="field-value"><?php echo $currency_symbol . $this->getPaidAmount(); ?></div>
                </li>
                <li class="order_net">
                    <div class="field-label"><?php _e('Due', 'smartcommerce'); ?></div>
                    <div class="field-value"><?php echo $currency_symbol . $this->getDueAmount(); ?></div>
                </li>
                <li class="order_assigned_to">
                    <div class="field-label"><?php _e('Assigned To', 'smartcommerce'); ?></div>
                    <div class="field-value"><?php echo $this->getAssignedTo('name'); ?></div>
                </li>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get Order Items 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function getItems()
    {
        $items = isset($this->metadata['product_data']) ? $this->metadata['product_data'][0] : array();
        $items = maybe_unserialize($items);
        return $items;
    }

    /**
     * Get Order Item Details 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function getItemDetails()
    {
        ob_start();
        ?>
        <div class="sc-table-wrap">
            <table class="sc-table">
                <thead>
                    <tr>
                        <th><?php _e('Item', 'smartcommerce'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($this->getItems() as $item): ?>
                        <tr>
                            <td><?php echo $item['name']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php
        return ob_get_clean();
    }

 
    /**
     * Get customer order form 
     * 
     * @param int $product_id 
     * @return string 
     * 
     * @since 1.0
     * @access public 
     * @static 
     */
       /**
     * Get Order Form 
     * 
     * @return string 
     * @since 1.0.0
     */
    public static function getOrderForm( $product_id = 0, $order_form_visible = true )
    {
        if(!$product_id) return; 
        $product = new Product($product_id);
        $product_type = 'simple';
        $variations = $product->getVariations();
 
        $size_options_exist = false;
        $color_options_exist = false;

        $all_variations = array();
        if(!empty($variations)){

            $product_type = 'variation';

            // Checking if size options or color options exist for any variation 
            foreach($variations as $variation){
                if($color_options_exist) continue;
                if($size_options_exist) continue;
                $size_options = $product->getSizeOptions($variation['uid']);
                $color_options = $product->getColorOptions($variation['uid']);
                $size_options_exist = !empty($size_options);
                $color_options_exist = !empty($color_options);
            }

            foreach($variations as $variation){
                $all_variations[] = array(
                    'product_id' => $product->id,
                    'product_name' => $product->getTitle(),
                    'product_image' => has_post_thumbnail($product->id) ? get_post_thumbnail_id($product->id) : '',
                    'product_sku' => $product->getSku(),
                    'product_price' => $product->getPrice(),
                    'product_quantity' => $product->getStockQuantity(),
                    'product_size' => '',
                    'product_color' => '',
                    'variation_id' => $variation['uid'] ?? '',
                    'variation_name' => $variation['name'] ?? '',
                    'variation_description' => $variation['description'] ?? '',
                    'variation_price' => $variation['price'] ?? '',
                    'variation_regular_price' => $variation['regular_price'] ?? '',
                    'variation_stock' => $variation['stock'] ?? '',
                    'variation_sku' => $variation['sku'] ?? '',
                    'variation_unit' => $variation['unit'] ?? '',
                    'variation_image' => $product->getThumbnail($variation['uid']),
                    'variation_color' => '',
                    'variation_size' => '',
                    'variation_color_options' => $product->getColorOptions($variation['uid']),
                    'variation_size_options' => $product->getSizeOptions($variation['uid']),
                );
            }
        } else {
            $size_options = $product->getSizeOptions();
            $color_options = $product->getColorOptions();
            $size_options_exist = !empty($size_options);
            $color_options_exist = !empty($color_options);
            $all_variations[] = array(
                'product_id' =>  $product->id,
                'product_name' => $product->getTitle(),
                'product_image' => has_post_thumbnail($product->id) ? get_post_thumbnail_id($product->id) : '',    
                'product_sku' =>  $product->getSku(),
                'product_price' => $product->getPrice(),
                'product_quantity' =>  $product->getStockQuantity(),
                'product_size' => '',
                'product_color' => '',
                'product_size_options' => $size_options,
                'product_color_options' => $color_options,
                'variation_id' => 0,
                'variation_name' => '',
                'variation_price' => '',
                'variation_regular_price' => '',
                'variation_unit' => '',
                'variation_description' => '',
                'variation_stock' => $product->getStockQuantity(),
                'variation_sku' => '',
                'variation_image' => '',
                'variation_size' => '',
                'variation_color' => '',
            );
        }
        $total_cols = 4;

        $total = $product->getPrice();
        $discount = 0;
        $net = $total - $discount;
        $total = number_format($total, 0);
        $discount = number_format($discount, 0);
        $net = number_format($net, 0);

        ob_start();
        ?>

        <form class="sc-form sc-order-form sc-ajax-form">
            <!-- Product Items -->
            <div class="sc-table-wrap sc-order-table">
                <table class="sc-table sc-order">
                    <thead>
                        <tr>
                            <th><?php _e('Image', 'smartcommerce'); ?></th>
                            <th><?php _e('Item', 'smartcommerce'); ?></th>
                            <th style='text-align: center;'><?php _e('Quantity', 'smartcommerce'); ?></th>
                            <th style='text-align: center;'><?php _e('Total', 'smartcommerce'); ?></th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php if(!empty($variations)): ?>
                            <!-- Variable Product -->
                            <?php 
                                $total = 0.00;
                                $net = 0.00;
                                $discount = 0.00;
                            ?>
                            <?php foreach($all_variations as $variation) :?>
                                <tr data-product-id="<?php echo $variation['product_id']; ?>" data-variation-id="<?php echo $variation['variation_id']; ?>" class='sc-order-item-row'>
                                    <td class="sc-order-table-img">
                                        <?php echo !is_numeric($variation['variation_image']) ? "<img src='".$variation['variation_image']."' class='sc-order-item-image'>" : wp_get_attachment_image($variation['variation_image'], 'sc-thumbnail', false, array('class' => 'sc-order-item-image')); ?>
                                        <?php echo Form::generateElement('hidden', 'variation_image[]', array(
                                            'value' => $variation['variation_image'],
                                        )); ?>
                                    </td>
                                    <td class="sc-order-table-details">
                                        <div class="sc-item-name"><?php echo $variation['variation_name']; ?></div>
                                        <div class="sc-item-description"><?php echo $variation['variation_description'] ?? ''; ?></div>

                                        <div class="sc-item-price-wrap">
                                            <span><?php _e('Price', 'smartcommerce'); ?>:</span> 
                                            <?php if(!empty($variation['variation_regular_price'])): ?>
                                                <span class="sc-item-regular-price" data-price="<?php echo $variation['variation_regular_price']; ?>"><strong><?php echo Settings::get('currency_symbol'); ?><?php echo SmartCommerce::convertENToBN($variation['variation_regular_price']); ?></strong></span>
                                            <?php endif; ?>
                                            <span class="sc-item-price" data-price="<?php echo $variation['variation_price']; ?>"><strong><?php echo Settings::get('currency_symbol'); ?><?php echo SmartCommerce::convertENToBN($variation['variation_price']); ?></strong></span>
                                            <?php if(!empty($variation['variation_unit'])): ?> <span class="sc-item-unit">/<?php _e($variation['variation_unit'] ?? ''); ?></span><?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="sc-order-table-qty">
                                        <?php 
                                            if($size_options_exist): 
                                                if(!empty($variation['variation_size_options'])):
                                                    echo Form::generateElement('select', 'variation_size[]', array(
                                                        'options' => $variation['variation_size_options'],
                                                        'value' => '',
                                                        'placeholder' => __('Size', 'smartcommerce'),
                                                    )); 
                                                endif;
                                            endif; 
                                        ?>
                                        <?php if($color_options_exist):
                                            if(!empty($variation['variation_color_options'])):
                                                echo Form::generateElement('select', 'variation_color[]', array(
                                                    'options' => $variation['variation_color_options'],
                                                    'value' => '',
                                                    'placeholder' => __('Color', 'smartcommerce'),
                                                ));
                                            endif;
                                        endif; ?>


                                        <div class="sc-item-qty-wrap">
                                            <a href='javascript:void(0)' data-action='remove' class='sc-button sc-button-primary sc-item-qty-btn sc-item-qty-remove'>-</a>
                                            <?php echo Form::generateElement('number', 'variation_quantity[]', array(
                                                'value' => 0,
                                                'placeholder' => __('Enter Quantity', 'smartcommerce'),
                                                'data' => array(
                                                    'min' => 0,
                                                    'step' => 1,
                                                    'max' => $variation['product_quantity'],
                                                    'data-unit-price' => $variation['variation_price'],
                                                ),
                                                'id' => 'sc_'.uniqid(),
                                                'class' => 'sc-item-qty no-arrows'
                                            )); ?>
                                            <a href='javascript:void(0)' data-action='add' class='sc-button sc-button-primary sc-item-qty-btn sc-item-qty-add'>+</a>
                                        </div>
                                        
                                        <?php 
                                            echo Form::generateElement('hidden', 'product_id[]', array('value' => $variation['product_id'])); 
                                            echo Form::generateElement('hidden', 'product_sku[]', array('value' => $variation['product_sku'])); 
                                            echo Form::generateElement('hidden', 'product_quantity[]', array('value' => 0)); 
                                            echo Form::generateElement('hidden', 'product_price[]', array('value' => $variation['product_price'])); 
                                            echo Form::generateElement('hidden', 'variation_id[]', array('value' => $variation['variation_id']));
                                            echo Form::generateElement('hidden', 'variation_name[]', array('value' => $variation['variation_name']));
                                            echo Form::generateElement('hidden', 'variation_price[]', array('value' => $variation['variation_price']));
                                            echo Form::generateElement('hidden', 'variation_color[]', array('value' => $variation['variation_color']));
                                            echo Form::generateElement('hidden', 'variation_size[]', array('value' => $variation['variation_size']));
                                            echo Form::generateElement('hidden', 'variation_sku[]', array('value' => $variation['variation_sku']));
                                        ?>
                                        
                                    </td>
                                    <td class="sc-order-table-amt">
                                        <?php echo Settings::get('currency_symbol'); ?><span class="sc-item-total" data-price="<?php echo $variation['variation_price']; ?>">0</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php foreach($all_variations as $variation) :?>
                                <!-- Simple Product -->
                                <tr data-product-id="<?php echo $variation['product_id']; ?>" data-variation-id="<?php echo $variation['variation_id']; ?>" class='sc-order-item-row'>
                                    <td>
                                        <?php echo wp_get_attachment_image(get_post_thumbnail_id($variation['product_id']), 'sc-thumbnail', false, array('class' => 'sc-order-item-image')); ?>
                                    </td>
                                    <td>
                                        <?php echo get_the_title($variation['product_id']); ?>
                                        <div class="sc-item-price-wrap" style='display: inline-block; width: 100%; font-weight: bold; margin-top: 10px;'>
                                            <span><?php _e('Price', 'smartcommerce'); ?>:</span>
                                            <span class="sc-item-price" data-price="<?php echo $variation['product_price']; ?>"><?php echo Settings::get('currency_symbol'); ?><?php echo SmartCommerce::convertENToBN($variation['product_price']); ?></span>    
                                        </div>
                                        <?php echo Form::generateElement('hidden', 'product_id[]', array(
                                            'value' => $variation['product_id'],
                                        )); ?>
                                    </td>

                                    <td width='100' style='text-align: center;'>
                                        
                                        <?php if($size_options_exist): ?>
                                            <?php
                                                if(!empty($variation['product_size_options'])):
                                                    echo Form::generateElement('select', 'product_size[]', array(
                                                        'options' => $variation['product_size_options'],
                                                        'value' => '',
                                                        'placeholder' => __('Select Size', 'smartcommerce'),
                                                    )); 
                                                endif;
                                            ?>
                                        <?php endif; ?>
                                        <?php if($color_options_exist): ?>
                                            <?php 
                                                if(!empty($variation['product_color_options'])):
                                                    echo Form::generateElement('select', 'product_color[]', array(
                                                        'options' => $variation['product_color_options'],
                                                        'value' => '',
                                                        'placeholder' => __('Select Color', 'smartcommerce'),
                                                    ));
                                                endif;
                                            ?>
                                        <?php endif; ?>

                                        <div class="sc-item-qty-wrap" style='display: inline-block; width: 100%;'>
                                            <a href='javascript:void(0)' data-action='remove' class='sc-button sc-button-primary sc-item-qty-btn sc-item-qty-remove'>-</a>
                                            <?php echo Form::generateElement('number', 'product_quantity[]', array(
                                                'value' => 0,
                                                'placeholder' => __('Enter Quantity', 'smartcommerce'),
                                                'data' => array(
                                                    'min' => 0,
                                                    'step' => 1,
                                                    'max' => $variation['product_quantity'],
                                                    'data-unit-price' => $variation['product_price'],
                                                ),
                                                'id' => 'sc_'.uniqid(),
                                                'class' => 'sc-item-qty no-arrows'
                                            )); ?>
                                            <a href='javascript:void(0)' data-action='add' class='sc-button sc-button-primary sc-item-qty-btn sc-item-qty-add'>+</a>
                                        </div>
                                        <?php 
                                            echo Form::generateElement('hidden', 'product_price[]', array('value' => $variation['product_price'],)); 
                                            echo Form::generateElement('hidden', 'variation_id[]', array('value' => '',)); 
                                            echo Form::generateElement('hidden', 'variation_sku[]', array('value' => '',)); 
                                            echo Form::generateElement('hidden', 'variation_quantity[]', array('value' => 0,)); 
                                            echo Form::generateElement('hidden', 'variation_price[]', array('value' => 0,)); 
                                            echo Form::generateElement('hidden', 'variation_name[]', array('value' => '',));
                                            echo Form::generateElement('hidden', 'variation_color[]', array('value' => '',));
                                            echo Form::generateElement('hidden', 'variation_size[]', array('value' => '',));
                                            echo Form::generateElement('hidden', 'variation_sku[]', array('value' => '',));
                                        ?>
                                    </td>
                                    <td width='75' style='text-align: center;'>
                                        <div class="sc-item-total-wrap" style='display: inline-block; width: 100%;'>
                                            <?php echo Settings::get('currency_symbol'); ?><span class="sc-item-total" data-price="<?php echo $variation['product_price']; ?>">0</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Total & Discount -->
                        <tr>
                            <td style="text-align: right;" colspan="<?php echo $total_cols-1; ?>" class="sc-text-right"><span class="sc-order-total-label sc-fright"><?php _e('Total', 'smartcommerce'); ?></span></td>
                            <td width='75' style="text-align: center;"><?php echo Settings::get('currency_symbol'); ?><span class="sc-order-total" data-price="<?php echo $product->getPrice(); ?>"><?php echo $total; ?></span></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;" colspan="<?php echo $total_cols-1; ?>" class="sc-text-right"><span class="sc-order-total-label sc-fright"><?php _e('Delivery Charge', 'smartcommerce'); ?></span></td>
                            <td width='75' style="text-align: center;"><?php echo Settings::get('currency_symbol'); ?><span class="sc-order-delivery-charge" data-charge="0">0</span></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;" colspan="<?php echo $total_cols-1; ?>" class="sc-text-right"><span class="sc-order-total-label sc-fright"><?php _e('Grand Total', 'smartcommerce'); ?></span></td>
                            <td width='75' style="text-align: center;"><?php echo Settings::get('currency_symbol'); ?><span class="sc-order-grand-total" data-grand-total="0">0</span></td>
                        </tr>
                        <tr style="display: none;">
                            <td style="text-align: right;" colspan="<?php echo $total_cols-1; ?>" class="sc-text-right"><span class="sc-order-discount-label sc-fright"><?php _e('Discount', 'smartcommerce'); ?></span></td>
                            <td style="text-align: right;"><span class="sc-order-discount" data-price="<?php echo $product->getDiscount(); ?>"><?php echo $discount; ?></span></td>
                        </tr>

                        <tr style="display: none;">
                            <td style="text-align: right;" colspan="<?php echo $total_cols-1; ?>" class="sc-text-right"><span class="sc-order-net-label sc-fright"><?php _e('Net Total', 'smartcommerce'); ?></span></td>
                            <td style="text-align: right;"><span class="sc-order-net-total" data-price="<?php echo $product->getPrice(); ?>"><?php echo $net; ?></span></td>
                        </tr>

                    </tbody>
    
                </table>
            </div>

            <!-- content before order form -->
            <?php $content_before_order_form = stripcslashes(Settings::get('content_before_order_form')); ?>
            <?php if(!empty($content_before_order_form)): ?>
                <div class="sc-order-content-before-order-form">
                    <?php echo $content_before_order_form; ?>
                </div>
            <?php endif; ?>

           <!-- delivery info -->
           <div class="sc-order-delivery" style="display:<?php echo $order_form_visible ? 'block' : 'none'; ?>;" id="sc_order_delivery">
                <h3 class="sc-order-section-title"><?php _e('Delivery Information', 'smartcommerce'); ?></h3>
                <?php 
                    $user = new User(get_current_user_id());
                    $delivery_fields = array(
                        'delivery_name' => array(
                            'type' => 'text',
                            'name' => 'delivery_name',
                            'settings' => array(
                                'placeholder' => __('Enter Name', 'smartcommerce'),
                                'required' => true,
                                'value' => $user->getName(),
                                'label' => __('Name', 'smartcommerce'),
                                'id' => 'delivery_name'
                            )
                        ),
                        'delivery_mobile' => array(
                            'type' => 'text',
                            'name' => 'delivery_mobile',
                            'settings' => array(
                                'placeholder' => __('Enter Mobile', 'smartcommerce'),
                                'required' => true,
                                'value' => $user->getMobile(),
                                'label' => __('Mobile', 'smartcommerce'),
                                'id' => 'user_mobile'
                            )
                        ),
                        'delivery_address' => array(
                            'type' => 'text',
                            'name' => 'delivery_address',
                            'settings' => array(
                                'placeholder' => __('Enter Full Address', 'smartcommerce'),
                                'required' => true,
                                'value' => $user->getAddress(),
                                'label' => __('Address', 'smartcommerce'),
                                'id' => 'delivery_address'
                            )
                        ),
                        'delivery_note' => array(
                            'type' => 'text',
                            'name' => 'delivery_note',
                            'settings' => array(
                                'placeholder' => __('Enter Note', 'smartcommerce'),
                                'value' => '',
                                'label' => __('Note', 'smartcommerce'),
                                'id' => 'delivery_note'
                            )
                        ),
                        'delivery_charge' => array(
                            'type' => 'select',
                            'name' => 'delivery_charge',
                            'settings' => array(
                                'options' => Product::getDeliveryChargeOptions($product_id),
                                'placeholder' => __('Select District', 'smartcommerce'),
                                'value' => '',
                                'label' => __('Select Delivery Location', 'smartcommerce'),
                                'id' => 'delivery_charge',
                                'required' => true,
                            )
                        ),
                        'payable_html'=>array(
                            'type' => 'html',
                            'name' => 'payable_html',
                            'settings' => array(
                                'html' => '<div class="sc-order-payable-html" style="font-weight: bold; margin-top: 10px;">'.__('Payable Amount', 'smartcommerce').': '.Settings::get('currency_symbol').'<span class="sc-order-payable-amount" data-payable="0">0</span></div>',
                            )
                        ),
                        'assigned_to' => array(
                            'type' => 'hidden',
                            'name' => 'assigned_to',
                            'settings' => array(
                                'value' => 0,
                            )
                        ),
                        'order_status' => array(
                            'type' => 'hidden',
                            'name' => 'order_status',
                            'settings' => array(
                                'value' => 'pending',
                            )
                        ),
                        'submit'=>array(
                            'type' => 'submit',
                            'name' => 'submit',
                            'settings' => array(
                                'value' => __('Submit Order', 'smartcommerce'),
                                'class' => 'sc-button sc-button-primary sc-fright',
                                'type' => 'submit',
                                'id' => 'sc_order_submit',
                            )
                        )
                    );
                    foreach($delivery_fields as $field){
                        ?>
                        <div class="sc-form-row">
                            <?php if(isset($field['settings']['label']) && !empty($field['settings']['label'])): ?>
                            <div class="sc-form-row-label">
                                <label for="<?php echo $field['settings']['id'] ?? $field['name']; ?>"><?php echo $field['settings']['label'] ?? ''; ?></label>
                            </div>
                            <?php endif; ?>
                            <div class="sc-form-row-input">
                                <?php echo Form::generateElement($field['type'], $field['name'], $field['settings']); ?>
                            </div>
                        </div>
                        <?php 
                    }
                ?>
            </div>

            <div class="sc-order-actions" style="margin-top: 20px;">
                <?php 
                    $whatsapp_link = Product::getWhatsappShareLink($product_id);
                    $messenger_link = Product::getMessengerShareLink($product_id);

                    $phone_number = Settings::get('phone_number');
                    $phone_link = '';
                    if(!empty($phone_number)){
                        $phone_link = 'tel:' . $phone_number;
                    }
                ?>

                <!-- Desktop Buttons -->
                <div class="desktop-only" style="width: 100%;">
                    <div style="display: flex; flex: 1; gap: 10px; justify-content: space-between; align-items: center;">
                        <!-- <button type="button" class="sc-button sc-button-primary sc-add-to-cart"><i class='fas fa-cart-plus'></i> <?php _e('Add to Cart', 'smartcommerce'); ?></button> -->
                        <?php if(!empty($whatsapp_link)): ?>    
                            <button type="button" class="sc-button sc-button-primary sc-whatsapp" onclick="window.open('<?php echo $whatsapp_link; ?>', '_blank')"><i class='fab fa-whatsapp'></i> <?php _e('WhatsApp Order', 'smartcommerce'); ?></button>
                        <?php endif; ?>
                        <?php if(!empty($messenger_link)): ?>
                            <button type="button" class="sc-button sc-button-primary sc-messenger" onclick="window.open('<?php echo $messenger_link; ?>', '_blank')"><i class='fab fa-facebook-messenger'></i> <?php _e('Messenger Order', 'smartcommerce'); ?></button>
                        <?php endif; ?>
                        <?php if(!empty($phone_link)): ?>
                            <button type="button" class="sc-button sc-button-primary sc-phone" onclick="window.open('<?php echo $phone_link; ?>', '_blank')"><i class='fas fa-phone'></i> <?php _e('Phone Order', 'smartcommerce'); ?></button>
                        <?php endif; ?>
                        <?php if(!$order_form_visible): ?>
                            <button type="button" class="sc-button sc-button-primary sc-order-now"><i class='fas fa-shopping-cart'></i> <?php _e('Order Now', 'smartcommerce'); ?></button>
                        <?php endif; ?>
                    </div>
                </div>


                <!-- Mobile Buttons -->
                <div class="mobile-only" style="width: 100%;">
                    <div class='sc-mobile-buttons' style="display: flex; flex: 1; gap: 10px; justify-content: space-between; align-items: center; flex-direction: column;">
                        <?php if(!$order_form_visible): ?>
                            <button type="button" class="sc-button sc-button-primary sc-order-now"><i class='fas fa-shopping-cart'></i> <?php _e('Order Now', 'smartcommerce'); ?></button>
                        <?php endif; ?>
                        <?php if(!empty($whatsapp_link)): ?>    
                            <button type="button" class="sc-button sc-button-primary sc-whatsapp" onclick="window.open('<?php echo $whatsapp_link; ?>', '_blank')"><i class='fab fa-whatsapp'></i> <?php _e('WhatsApp Order', 'smartcommerce'); ?></button>
                        <?php endif; ?>
                        <?php if(!empty($messenger_link)): ?>
                            <button type="button" class="sc-button sc-button-primary sc-messenger" onclick="window.open('<?php echo $messenger_link; ?>', '_blank')"><i class='fab fa-facebook-messenger'></i> <?php _e('Messenger Order', 'smartcommerce'); ?></button>
                        <?php endif; ?>
                        <?php if(!empty($phone_link)): ?>
                            <button type="button" class="sc-button sc-button-primary sc-phone" onclick="window.open('<?php echo $phone_link; ?>', '_blank')"><i class='fas fa-phone'></i> <?php _e('Phone Order', 'smartcommerce'); ?></button>
                        <?php endif; ?>
                        <?php if(!$order_form_visible): ?>
                            <!-- <button type="button" class="sc-button sc-button-primary sc-add-to-cart"><i class='fas fa-cart-plus'></i> <?php _e('Add to Cart', 'smartcommerce'); ?></button> -->
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Hidden Fields -->
            <?php 
                $fields = array(
                    array('type' => 'hidden','name' => 'action','settings' => array('value' => 'smartcommerce_ajax',)),
                    array('type' => 'hidden','name' => 'ajax_action','settings' => array('value' => 'publishPost',)),
                    array('type' => 'hidden','name' => 'product_type','settings' => array('value' => $product_type,)),
                    array('type' => 'hidden','name' => 'post_type','settings' => array('value' => self::$_instance->post_type,)),
                    array('type' => 'hidden','name' => '_wpnonce','settings' => array('value' => wp_create_nonce('smartcommerce'),)),
                    array('type' => 'hidden','name' => 'before_send_callback','settings' => array('value' => 'orderBeforePublishCallback',)),
                    array('type' => 'hidden','name' => 'success_callback','settings' => array('value' => 'orderSuccessCallback',)),
                    array('type' => 'hidden','name' => 'user_ip','settings' => array('value' => SmartCommerce::getUserIP(),)),
                    array('type' => 'hidden','name' => 'user_id','settings' => array('value' => get_current_user_id(),)),
                    array('type' => 'hidden','name' => 'device_type','settings' => array('value' => SmartCommerce::getDeviceType(),)),
                    array('type' => 'hidden','name' => 'referer','settings' => array('value' => $_SERVER['HTTP_REFERER'] ?? '',)),
                    array('type' => 'hidden','name' => 'order_total','settings' => array('value' => $total,)),
                    array('type' => 'hidden','name' => 'order_discount','settings' => array('value' => $discount,)),
                    array('type' => 'hidden','name' => 'product_type','settings' => array('value' => $product_type))
                );
                $fields = apply_filters('smartcommerce_filter_order_form_fields', $fields);
                foreach($fields as $field){
                    echo Form::generateElement($field['type'], $field['name'], $field['settings']);
                }
            ?>
        </form>
        <?php 
        return ob_get_clean(); 
        
    }


    /**
     * Publish an order 
     * 
     * @since 1.0.0
     * @access public 
     */
    public function publish($data=[])
    {
        $args = [];
        $args['post_type'] = $this->post_type;
        $args['post_author'] = get_current_user_id();
        $args['post_title'] = 'Order - ' . strtotime(current_time('mysql'));
        $args['post_status'] = 'publish';
        
        $skip_fields = ['action', 'ajax_action', 'before_send_callback', 'success_callback', 'post_type', '_wpnonce'];
       
        $order_id = wp_insert_post($args);
        if(is_wp_error($order_id)){
            return ['status' => false, 'message' => $order_id->get_error_message()];
        }

        $product_data = [];
        for($i = 0; $i < count($data['product_id']); $i++){
            $item_data = array( 
                'product_id' => $data['product_id'][$i],
                'product_quantity' => $data['product_quantity'][$i],
                'product_size' => $data['product_size'][$i] ?? '',
                'product_color' => $data['product_color'][$i] ?? '',
                'product_price' => $data['product_price'][$i] ?? 0,
                'product_sku' => $data['product_sku'][$i] ?? '',
                'variation_id' => $data['variation_id'][$i] ?? 0,
                'variation_name' => $data['variation_name'][$i] ?? '',
                'variation_price' => $data['variation_price'][$i] ?? '',
                'variation_color' => $data['variation_color'][$i] ?? '',
                'variation_size' => $data['variation_size'][$i] ?? '',
                'variation_sku' => $data['variation_sku'][$i] ?? '',
                'variation_quantity' => $data['variation_quantity'][$i] ?? 0,
                'variation_image' => $data['variation_image'][$i] ?? '',
            );
            $id = $data['product_id'][$i];
            $product_data[$id][] = $item_data;
        }
        update_post_meta($order_id, 'product_data', $product_data);

        // get unique product ids 
        $product_ids = array_keys($product_data);
        foreach($product_ids as $product_id){
            add_post_meta($order_id, 'product_id', $product_id);
        }

        foreach($data as $key => $value){
            if(str_contains($key, 'product_') || str_contains($key, 'variation_')) continue;
            if(in_array($key, $skip_fields)) continue;
            if($key == 'delivery_mobile'){
                // remove all special characters 
                $value = preg_replace('/[^0-9]/', '', $value);
                // remove 88 from left 
                $value = ltrim(strval($value), '88');
            }
            if($key == 'delivery_charge'){
                $values = explode(':', $value);
                $value = $values[1] ?? 0; 
                $delivery_type = $values[0] ?? '';
                if(!empty($delivery_type)) update_post_meta($order_id, 'delivery_type', $delivery_type);
            }
            update_post_meta($order_id, $key, $value);
        }

        $uniqid = uniqid();
        update_post_meta($order_id, 'tracking_id', $uniqid);

        $sms_active = Settings::get('sms_active');
        $sms = null;
        if($sms_active == 'yes'){
            $sms_format = Settings::get('sms_order_placed_format');
            $sms_mobile = preg_replace('/[^0-9]/', '', $data['delivery_mobile']);
            $sms_mobile = ltrim(strval($sms_mobile), '88');
            if(strlen($sms_mobile) == 10 || strlen($sms_mobile) == 11) {
                $sms_name = (string) $data['delivery_name'];
                $message = str_replace( array("{name}", "{order_id}"), array($sms_name, $order_id), $sms_format);
                $text = "{$message} Track the order: " . site_url('/tracking/?tid=' . $uniqid);
                $sms = Sms::send($sms_mobile, $text);
            }
        }

        if(Settings::get('order_notification') == 'yes'){
            $shop_name = Settings::get('shop_name');
            $urlparts = wp_parse_url(home_url());
            $domain = $urlparts['host'];
            $shop_email = 'inof@' . $domain;
            $to = Settings::get('order_notification_email');
            $headers = array(
                "Content-Type: text/html; charset=UTF-8",
                "From: {$shop_name} <{$shop_email}>",
            );
            if(!empty($to)){
                $link = site_url('/tracking/?tid=' . $uniqid);
                $subject = "New order placed on " . $shop_name;
                $body = "Dear Admin, <br>
                New order placed on your site. To view the order, <a href='{$link}'>please click here.</a>
                <br><br>
                Thank you!<br>
                Team SmartCommerce";
                wp_mail($to, $subject, $body, $headers);
            }
        }

        // Return Order Details 
        return array(
            'id' => $order_id,
            'status' => true,
            'message' => __('Order placed successfully', 'smartcommerce'),
            'tracking_id' => $link,
            'sms_response' => $sms
        );

    }

    /**
     * Count total 
     * 
     * @param string $status 
     * 
     * @return int 
     * 
     * @since 1.0.0
     */
    public function countTotal($status='')
    {
        $args = array(
            'post_type' => $this->post_type,
            'post_status' => 'publish',
            'count' => true,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_key' => 'order_status',
            'meta_value' => $status,
            'meta_compare' => '=',
        );
        if($status == 'cancelled') $args['meta_compare'] = 'LIKE';
        if($status == 'all'){
            unset($args['meta_key']);
            unset($args['meta_value']);
            unset($args['meta_compare']);
        }        
        $qry = new \WP_Query($args);
        return $qry->found_posts;
        
    }

    /**
     * Show Filter List Before HTml 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function filterListBeforeHtml($before_html)
    {
        $order_status = sanitize_text_field($_GET['order_status'] ?? '');
        ob_start();
        $status_list = $this->getStatusListToShowCounts();
        ?>
        <div class="sc-filter-wrap">
            <ul class="sc-order-status-filter desktop-only">
                <?php foreach($status_list as $status => $data){ ?>
                    <li data-status="<?php echo $status; ?>">
                        <a href="<?php echo site_url('/dashboard/?submenu='.$this->post_type.'&order_status='.$status); ?>" class="<?php echo $status == $order_status ? 'active' : ''; ?>">
                            <?php echo $data['label']; ?>(<?php echo $data['count']; ?>)
                        </a>
                    </li>
                <?php } ?>
            </ul>
            <div class="mobile-only">
                <select name='filter_post_status' class="filter-post-status">
                    <?php foreach($status_list as $status => $data){ ?>
                        <option data-url="<?php echo site_url('/dashboard/?submenu='.$this->post_type.'&order_status='.$status); ?>" value="<?php echo $status; ?>"><?php echo $data['label']; ?> (<?php echo $data['count']; ?>)</option>
                    <?php } ?>
                </select>
            </div>
            <div class="search-form">
                <?php echo $this->getSearchForm(); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * GEt Search form on search inside 
     * 
     * @return string 
     */
    public function getSearchForm()
    {
        ob_start();
        ?>
        <form class='sc-search-form' action="<?php echo site_url('/dashboard/?submenu='.$this->post_type); ?>" method="get">
            <?php 
                echo Form::generateElement('text', 'search', array(
                    'placeholder' => __('Search ID or Name', 'smartcommerce'),
                    'value' => sanitize_text_field($_GET['search'] ?? ''),
                    'name' => 'search',
                ));
                echo Form::generateElement('number', 'mobile', array(
                    'name' => 'mobile',
                    'value' => sanitize_text_field($_GET['mobile'] ?? ''),
                    'placeholder' => __('Search Mobile', 'smartcommerce'),
                ));
                echo Form::generateElement('button', 'search_submit', array(
                    'value' => __('Search', 'smartcommerce'),
                    'type' => 'submit',
                    'class' => 'sc-button',
                ));
                echo Form::generateElement('hidden', 'submenu', array(
                    'value' => sanitize_text_field($_GET['submenu'] ?? ''),
                    'name' => 'submenu',
                ));
            ?>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Get Status List to show counts
     * 
     * @return array 
     * @since 1.0.0
     */
    public function getStatusListToShowCounts()
    {
        return array(
            'all' => array(
                'count' => $this->countTotal('all'),
                'label' => __('All', 'smartcommerce'),
            ),
            'pending' => array(
                'count' => $this->countTotal('pending'),
                'label' => __('Pending', 'smartcommerce'),
            ),
            'confirmed' => array(
                'count' => $this->countTotal('confirmed'),
                'label' => __('Confirmed', 'smartcommerce'),
            ),
            'ready-to-ship' => array(
                'count' => $this->countTotal('ready-to-ship'),
                'label' => __('Ready to Ship', 'smartcommerce'),
            ),
            'shipped' => array(
                'count' => $this->countTotal('shipped'),
                'label' => __('Shipped', 'smartcommerce'),
            ),
            'delivered' => array(
                'count' => $this->countTotal('delivered'),
                'label' => __('Delivered', 'smartcommerce'),
            ),
            'returned' => array(
                'count' => $this->countTotal('returned'),
                'label' => __('Returned', 'smartcommerce'),
            ),
            'cancelled' => array(
                'count' => $this->countTotal('cancelled'),
                'label' => __('Cancelled', 'smartcommerce'),
            ),
        );
    }

    /**
     * Filter Bulk Action HTML 
     * 
     * @param string $html 
     * @return string 
     * @since 1.0.0
     */
    public function filterBulkActionsHtml($html)
    {
        ob_start();
        ?>
        <div class="sc-bulk-actions">
            <?php
            echo Form::generateElement('select', 'bulk_action_print', array(
                'options' => array(
                    'print_invoice' => __('Print Invoice', 'smartcommerce'),
                    'print_delivery_label' => __('Print Delivery Label (Small)', 'smartcommerce'),
                    'print_delivery_label_2' => __('Print Delivery Label (Big)', 'smartcommerce'),
                ),
                'class' => 'bulk-action bulk-print-action',
                'placeholder' => __('Bulk Print Action', 'smartcommerce'),
                'data' => array(
                    'data-ajax_action' => 'bulkPrint',
                    'data-post_type' => $this->post_type,
                    'data-success_callback' => 'bulkPrintSuccessCallback',
                )
            ));
            echo Form::generateElement('select', 'bulk_action_order_status', array(
                'class' => 'bulk-action bulk-order-status-action',
                'options' => array(
                    'pending' => __('Change status to Pending', 'smartcommerce'),
                    'confirmed' => __('Change status Confirmed', 'smartcommerce'),
                    'ready-to-ship' => __('Change status Ready to Ship', 'smartcommerce'),
                    'shipped' => __('Change status Shipped', 'smartcommerce'),
                    'delivered' => __('Change status Delivered', 'smartcommerce'),
                ),
                'placeholder' => __('Bulk Change Status', 'smartcommerce'),
                'data' => array(
                    'data-ajax_action' => 'bulkChangeOrderStatus',
                    'data-post_type' => $this->post_type,
                    'data-success_callback' => 'bulkChangeOrderStatusCallback',
                )
            ));
            ?>
        </div>
        <?php 
        return ob_get_clean();
    }

    /**
     * Get Product Details 
     * 
     * @return array 
     * 
     * @since 1.0.0
     */
    public function getProductDetails($order_id=0)
    {
        if(!$order_id) $order_id = $this->id;
        $product_data = get_post_meta($order_id, 'product_data', true);
        if(empty($product_data)) return [];

        foreach($product_data as $product_id => $items){
            foreach($items as $key=>$item){
                if(!$item['variation_id']){
                    $image_id = get_post_thumbnail_id($product_id);
                    $product_data[$product_id][$key]['image_id'] = $image_id;
                }
            }
        }
        return $product_data;
    }

    /**
     * Print Invoice 
     * 
     * @return string   
     * @since 1.0.0
     * @access public 
     */
    public function getInvoice()
    {
        
        $order_id = $this->id;
        $order_date = date('d/m/Y', strtotime($this->post->post_date));
        $order_total = number_format($this->getTotal(), 2);
        $order_discount = number_format($this->getDiscount(), 2);
        $order_net = number_format($this->getNet(), 2);
        $order_delivery = number_format($this->getDeliveryCharge(), 2);
        $order_payable = number_format($this->getPayableAmount(), 2);
        $order_paid = number_format($this->getPaidAmount(), 2);
        $order_due = number_format($this->getDueAmount(), 2);
        $order_status = $this->getStatus();
        $order_delivery_partner_id = $this->getDeliveryPartnerId();
        $order_delivery_partner_title = strtolower(trim(get_the_title($order_delivery_partner_id)));
        $consignment_id = DeliveryPartner::getDeliveryConsignmentId($order_id);

        $reciever_name = isset($this->metadata['delivery_name']) ? $this->metadata['delivery_name'][0] : '';
        $reciever_mobile = isset($this->metadata['delivery_mobile']) ? $this->metadata['delivery_mobile'][0] : '';
        $reciever_address = isset($this->metadata['delivery_address']) ? $this->metadata['delivery_address'][0] : '';
        $product_details = $this->getProductDetails();
        

        $template = SMART_COMMERCE_DIR . 'templates/invoice/invoice-1.php';
        ob_start();
        ?>
        <style>
            @media print {
                @page {
                    size: <?php echo Settings::get('print_invoice_paper_size', 'A5'); ?>;
                    orientation: <?php echo Settings::get('print_invoice_paper_orientation', 'portrait'); ?>;
                    margin-top: <?php echo Settings::get('print_invoice_paper_margin_top', '0.25'); ?>in;
                    margin-bottom: <?php echo Settings::get('print_invoice_paper_margin_bottom', '0.25'); ?>in;
                    margin-left: <?php echo Settings::get('print_invoice_paper_margin_left', '0.25'); ?>in;
                    margin-right: <?php echo Settings::get('print_invoice_paper_margin_right', '0.25'); ?>in;
                }
                body{
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            }
        </style>

        <?php
        require($template);
        $html = ob_get_clean();
        return $html;
        
    }

    /**
     * Log updates or changes to this post 
     * 
     * @return array 
     * 
     * @since 1.0.0
     * @access public 
     */
    public static function logChanges($post_id, $key, $value)
    {
        $log_key = 'change_log';
        $logs = get_post_meta($post_id, $log_key, true);
        if(empty($logs)) $logs = [];
        $logs = maybe_unserialize($logs);
        $logs[] = array(
            'key' => $key,
            'value' => $value,
            'date' => current_time('mysql'),
            'user_id' => get_current_user_id(),
        );
        update_post_meta($post_id, $log_key, $logs);
    }
    
    /**
     * Update Post Meta 
     * 
     * @return boolean 
     * @param string $key 
     * @param string $value 
     * @param string old 
     */
    public static function updatePostMeta($post_id, $key, $value, $old_value=null)
    {
        $update = update_post_meta($post_id, $key, $value, $old_value);
        if($update) self::logChanges($post_id, $key, $value);
        return $update;
    }

    /**
     * Get Customer Name
     * 
     * @return string
     * @since 1.0.0
     */
    public function getDeliveryInfo($field='')
    {
        return isset($this->metadata[$field]) ? $this->metadata[$field][0] : '';
    }

    /**
     * Get Delivery Partner ID
     * 
     * @return int
     * @since 1.0.0
     */
    public function getDeliveryPartnerId()
    {
        return isset($this->metadata['delivery_partner_id']) ? $this->metadata['delivery_partner_id'][0] : 0;
    }

    /**
     * Get Delivery Data
     * 
     * @return array
     * @since 1.0.0
     */
    public function getDeliveryStatus($key='')
    {
        $partner_id = $this->getDeliveryPartnerId();
        $key = 'delivery_status_'.$partner_id;
        return isset($this->metadata[$key]) ? $this->metadata[$key][0] : 0;
    }
}

$order = Order::instance();
$order->wpInit();