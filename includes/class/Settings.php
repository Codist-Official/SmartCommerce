<?php 
namespace SmartCommerce;

class Settings {

    /**
     * Setting key
     */
    private static $setting_key = 'sc_settings';

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
     * Get setting
     * 
     * @return mixed 
     * @since 1.0.0
     */
    public static function get($field='', $default='', $force = false)
    {
        $setting = maybe_unserialize(get_option(self::$setting_key));
        if(empty($field)) return $setting;
        $value = isset($setting[$field]) ? $setting[$field] : $default;
        if(empty($value) && $force){
            $value = $default;
        }
        return $value;
    }

    /**
     * Update setting
     * 
     * @return void 
     * @since 1.0.0
     */
    public static function update($field='', $value='')
    {
        $settings = maybe_unserialize(get_option(self::$setting_key));
        if(empty($settings)) $settings = array();
        if($field == 'shop_name') {
            update_option('blogname', $value);
        }
        $settings[$field] = $value;
        if($field == 'site_language'){
            $value = $value == 'bn_BD' ? 'bn_BD' : '';
            update_option('WPLANG', $value);
        }
        return update_option(self::$setting_key, $settings, 'no');
    }

    /**
     * Delete setting
     * 
     * @return void 
     * @since 1.0.0
     */
    public static function delete($field='')
    {
        $settings = maybe_unserialize(get_option(self::$setting_key));
        if(empty($settings)) $settings = array();
        unset($settings[$field]);
        update_option(self::$setting_key, $settings, 'no');
    }

    /**
     * Get logo 
     * 
     * @return string 
     * @since 1.0.0
     */
    public static function getLogo($size=512)
    {
        $logo = self::get('shop_logo');
        if(empty($logo)) return SMART_COMMERCE_IMG_URL . 'logo-placeholder.png';
        return wp_get_attachment_image_src($logo, 'full')[0];
    }

    /**
     * Get Panel 
     * 
     * @return string 
     * 
     * @since 1.0.0
     */
    public static function getPanel()
    {
        ob_start();
        ?>
        <div class="sc-settings-panel sc-flex-wrap">
            <!-- Menu Bar --> 
            <div class="sc-settings-sidebar flex-2">
                <?php echo self::getSettingsMenuHtml(); ?>
            </div>
            <!-- Content --> 
            <div class="sc-settings-content flex-10">
                <?php echo self::getSettingsContent(); ?>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Get Settings Menu 
     * 
     * @return array 
     * 
     * @since 1.0.0
     */
    public static function getSettingsMenu()
    {
        $menu = array(
            'basic' => array(
                'title' => __('Basic', 'smartcommerce'),
                'url' => home_url('/dashboard/?submenu=settings&tab=basic'),
                'icon' => 'fa-solid fa-gear',
            ),
            'design' => array(
                'title' => __('Design', 'smartcommerce'),
                'url' => home_url('/dashboard/?submenu=settings&tab=design'),
                'icon' => 'fa-solid fa-palette',
            ),
            'pages' => array(
                'title' => __('Pages', 'smartcommerce'),
                'url' => home_url('/dashboard/?submenu=settings&tab=pages'),
                'icon' => 'fa-solid fa-file-alt',
            ),
            'payment' => array(
                'title' => __('Payment', 'smartcommerce'),
                'url' => home_url('/dashboard/?submenu=settings&tab=payment'),
                'icon' => 'fa-solid fa-credit-card',
            ),
            'delivery' => array(
                'title' => __('Delivery', 'smartcommerce'),
                'url' => home_url('/dashboard/?submenu=settings&tab=delivery'),
                'icon' => 'fa-solid fa-truck',
            ),
            'sms' => array(
                'title' => __('SMS Service', 'smartcommerce'),
                'url' => home_url('/dashboard/?submenu=settings&tab=sms'),
                'icon' => 'fa-solid fa-sms',
            ),
            'print' => array(
                'title' => __('Print Settings', 'smartcommerce'),
                'url' => home_url('/dashboard/?submenu=settings&tab=print'),
                'icon' => 'fa-solid fa-print',
            ),
            'seo' => array(
                'title' => __('SEO Settings', 'smartcommerce'),
                'url' => home_url('/dashboard/?submenu=settings&tab=seo'),
                'icon' => 'fa-solid fa-search',
            ),
        );
        return apply_filters('smartcommerce_filter_settings_menu', $menu);
    }

    /**
     * Get Settings Menu Html
     * 
     * @return string 
     * 
     * @since 1.0.0
     */
    public static function getSettingsMenuHtml()
    {
        $menu = self::getSettingsMenu();
        if(empty($menu)) return '';
        ob_start();
        ?>
        <ul class="sc-settings-menu">
            <?php foreach($menu as $key => $item){
                $active_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'basic';
                $active_class = $active_tab == $key ? 'active' : '';
                ?>
                <li data-item="<?php echo $key; ?>" class="<?php echo $active_class; ?>">
                    <a href="<?php echo $item['url'] ?? '#'; ?>" class="sc-settings-menu-item ">
                        <i class="<?php echo $item['icon'] ?? ''; ?>"></i>
                        <span><?php echo $item['title'] ?? ''; ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
        <?php
        return ob_get_clean();
    }

    /**
     * Get Settings Content 
     * 
     * @return string 
     * 
     * @since 1.0.0
     */
    public static function getSettingsContent()
    {
        $tab = sanitize_text_field($_REQUEST['tab'] ?? 'basic');
        if(empty($tab)) return;
        ob_start();
        ?>
        <div class="sc-settings-content">
            <?php echo self::getSettingsForm($tab); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get Settigns Fields 
     * 
     * @param string $tab
     * 
     * @return array 
     * 
     * @since 1.0.0
     */
    public static function getSettingsFields()
    {
        $fields = array();
        $fields['basic'] = array(
            'title' => __('Basic Information', 'smartcommerce'),
            'fields' => array(
                'shop_name' => array(
                    'type' => 'text',
                    'name' => 'shop_name',
                    'settings' => array(
                        'placeholder' => __('Enter your shop name', 'smartcommerce'),
                        'label' => __('Shop Name', 'smartcommerce'),
                        'value' => self::get('shop_name'),
                        'required' => true,
                        'id' => 'shop_name',
                    ),
                ),
                'shop_logo' => array(
                    'type' => 'ajax_file',
                    'name' => 'shop_logo',
                    'settings' => array(
                        'label' => __('Shop Logo', 'smartcommerce'),
                        'value' => self::get('shop_logo'),
                        'required' => false,
                        'id' => 'shop_logo',
                        'preview_content' => array(
                            wp_get_attachment_image(self::get('shop_logo'), 'thumbnail'),
                        ),
                        'preview' => true,
                    ),
                ),
                'shop_address' => array(
                    'type' => 'text',
                    'name' => 'shop_address',
                    'settings' => array(
                        'placeholder' => __('Enter your shop address', 'smartcommerce'),
                        'label' => __('Shop Address', 'smartcommerce'),
                        'value' => self::get('shop_address'),
                        'required' => false,
                        'id' => 'shop_address',
                        'row-class' => 'col-3',
                    ),
                ),
                'shop_phone' => array(
                    'type' => 'text',
                    'name' => 'shop_phone',
                    'settings' => array(
                        'placeholder' => __('Enter your shop phone', 'smartcommerce'),
                        'label' => __('Shop Phone', 'smartcommerce'),
                        'value' => self::get('shop_phone'),
                        'required' => false,
                        'id' => 'shop_phone',
                        'row-class' => 'col-3',
                    ),
                ),
                'shop_email' => array(
                    'type' => 'text',
                    'name' => 'shop_email',
                    'settings' => array(
                        'placeholder' => __('Enter your shop email', 'smartcommerce'),
                        'label' => __('Shop Email', 'smartcommerce'),
                        'value' => self::get('shop_email'),
                        'required' => false,
                        'id' => 'shop_email',
                        'row-class' => 'col-3',
                    ),
                ),
                'site_language' => array(
                    'type' => 'select',
                    'name' => 'site_language',
                    'settings' => array(
                        'label' => __('Site Language', 'smartcommerce'),
                        'value' => self::get('site_language'),
                        'options' => array(
                            'en_US' => __('English', 'smartcommerce'),
                            'bn_BD' => __('Bengali', 'smartcommerce'),
                        ),
                        'placeholder' => __('Select site language', 'smartcommerce'),
                        'id' => 'site_language',
                    ),
                ),
                'welcome_message' => array(
                    'type' => 'textarea',
                    'name' => 'welcome_message',
                    'settings' => array(
                        'label' => __('Welcome Message', 'smartcommerce'),
                        'value' => self::get('welcome_message'),
                    ),
                ),
                'whatsapp_number' => array(
                    'type' => 'text',
                    'name' => 'whatsapp_number',
                    'settings' => array(
                        'label' => __('WhatsApp Number', 'smartcommerce'),
                        'value' => self::get('whatsapp_number'),
                        'row-class' => 'col-4'
                    ),
                ),
                'phone_number' => array(
                    'type' => 'text',
                    'name' => 'phone_number',
                    'settings' => array(
                        'label' => __('Phone Number', 'smartcommerce'),
                        'value' => self::get('phone_number'),
                        'row-class' => 'col-4',
                        'placeholder' => __('Enter shop phone number', 'smartcommerce'),
                    ),
                ),
                'messenger_username' => array(
                    'type' => 'text',
                    'name' => 'messenger_username',
                    'settings' => array(
                        'label' => __('Messenger Username', 'smartcommerce'),
                        'value' => self::get('messenger_username'),
                        'row-class' => 'col-4',
                        'placeholder' => __('Enter Messenger username', 'smartcommerce'),

                    ),
                ),
                'seprator_html' => array(
                    'type' => 'html',
                    'name' => uniqid()  ,
                    'settings' => array(
                        'html' => ""
                    )
                ),
                'facebook_url' => array(
                    'type' => 'url',
                    'name' => 'facebook_url',
                    'settings' => array(
                        'label' => __('Facebook URL', 'smartcommerce'),
                        'value' => self::get('facebook_url'),
                        'row-class' => 'col-4',
                        'placeholder' => __('Enter shop Facebook URL', 'smartcommerce'),
                    )
                ),
                'instagram_url' => array(
                    'type' => 'url',
                    'name' => 'instagram_url',
                    'settings' => array(
                        'label' => __('Instagram URL', 'smartcommerce'),
                        'value' => self::get('instagram_url'),
                        'row-class' => 'col-4',
                        'placeholder' => __('Enter shop Instagram URL', 'smartcommerce'),
                    )
                ),
                'youtube_url' => array(
                    'type' => 'url',
                    'name' => 'youtube_url',
                    'settings' => array(
                        'label' => __('YouTube URL', 'smartcommerce'),
                        'value' => self::get('youtube_url'),
                        'row-class' => 'col-4',
                        'placeholder' => __('Enter shop YouTube URL', 'smartcommerce'),
                    )
                ),
                'website_url' => array(
                    'type' => 'url',
                    'name' => 'website_url',
                    'settings' => array(
                        'label' => __('Website URL', 'smartcommerce'),
                        'value' => self::get('website_url'),
                        'row-class' => 'col-4',
                        'placeholder' => __('Enter Website URL', 'smartcommerce'),
                    )
                ),
                'stock_units' => array(
                    'type' => 'text',
                    'name' => 'stock_units',
                    'settings' => array(
                        'label' => __('Stock Unit Options', 'smartcommerce'),
                        'value' => self::get('stock_units', 'Pcs, Dozen, KG, L, ML'),
                        'placeholder' => __('Enter stock unit options', 'smartcommerce'),
                    ),
                ),
                'image_quality' => array(
                    'type' => 'select',
                    'name' => 'image_quality',
                    'settings' => array(
                        'label' => __('Image Quality', 'smartcommerce'),
                        'value' => self::get('image_quality', 60),
                        'options' => array(
                            10 => __('10%', 'smartcommerce'),
                            20 => __('20%', 'smartcommerce'),
                            30 => __('30%', 'smartcommerce'),
                            40 => __('40%', 'smartcommerce'),
                            50 => __('50%', 'smartcommerce'),
                            60 => __('60%', 'smartcommerce'),
                            70 => __('70%', 'smartcommerce'),
                            80 => __('80%', 'smartcommerce'),
                            90 => __('90%', 'smartcommerce'),
                            100 => __('100%', 'smartcommerce'),
                        ),
                        'placeholder' => __('Select image quality', 'smartcommerce'),
                    ),
                ),
                'order_notification' => array(
                    'type' => 'select',
                    'name' => 'order_notification',
                    'settings' => array(
                        'label' => __('Send Order Notification', 'smartcommerce'),
                        'value' => self::get('order_notification', 'yes'),
                        'options' => array(
                            'yes' => __('Yes', 'smartcommerce'),
                            'no' => __('No', 'smartcommerce')
                        ),
                        'placeholder' => __('Select order notification', 'smartcommerce'),
                        'row-class' => 'col-4'
                    ),
                ),
                'order_notification_email' => array(
                    'type' => 'text',
                    'name' => 'order_notification_email',
                    'settings' => array(
                        'label' => __('Send Order Notification', 'smartcommerce'),
                        'value' => self::get('order_notification_email'),
                        'placeholder' => __('Email to send notification', 'smartcommerce'),
                        'row-class' => 'col-4'
                    ),
                ),
                'custom_css' => array(
                    'type' => 'textarea',
                    'name' => 'custom_css',
                    'settings' => array(
                        'label' => __('Custom CSS', 'smartcommerce'),
                        'value' => self::get('custom_css'),
                        'placeholder' => __('Enter custom CSS code, do not include <style> tag', 'smartcommerce'),
                    ),
                ),
                'custom_js' => array(
                    'type' => 'textarea',
                    'name' => 'custom_js',
                    'settings' => array(
                        'label' => __('Custom JS', 'smartcommerce'),
                        'value' => stripcslashes(self::get('custom_js')),
                        'placeholder' => __('Enter custom JS code, do not include <script> tag', 'smartcommerce'),
                    ),
                ),
                'custom_html' => array(
                    'type' => 'textarea',
                    'name' => 'custom_html',
                    'settings' => array(
                        'label' => __('Custom HTML', 'smartcommerce'),
                        'value' => stripcslashes(self::get('custom_html')),
                        'placeholder' => __('Enter custom HTML', 'smartcommerce'),
                    ),
                ),
                'content_before_order_form' => array(
                    'type' => 'textarea',
                    'name' => 'content_before_order_form',
                    'settings' => array(
                        'label' => __('Content Before Order Form', 'smartcommerce'),
                        'value' => stripcslashes(self::get('content_before_order_form')),
                        'placeholder' => __('Enter content before order form', 'smartcommerce'),
                    ),
                ),
                'flush_rewrite_rules' => array(
                    'type' => 'html',
                    'name' => 'flush_rewrite_rules', 
                    'settings' => array(
                        'html' => Form::generateElement('button', 'flushRewriteRules',array(
                            'value' => __('Flush Rewrite Rules', 'smartcommerce'),
                            'class' => 'button button-primary sc-ajax-link',
                            'type' => 'button',
                            'data' => array(
                                'data-ajax_action' => 'flushRewriteRules'
                            )
                        ))
                    )
                )

            ),
        );
        $fields['payment'] = array(
            'title' => __('Payment', 'smartcommerce'),
            'fields' => array(
                'currency_symbol' => array(
                    'type' => 'text',
                    'name' => 'currency_symbol',
                    'settings' => array(
                        'label' => __('Currency Symbol', 'smartcommerce'),
                        'value' => self::get('currency_symbol', '৳'),
                        'placeholder' => __('Enter your currency symbol', 'smartcommerce'),
                        'id' => 'currency_symbol',
                    ),
                ),
                'payment_method' => array(
                    'name' => 'payment_method',
                    'type' => 'checkbox',
                    'settings' => array(
                        'label' => __('Payment Methods', 'smartcommerce'),
                        'options' => array(
                            'cod' => __('Cash on Delivery', 'smartcommerce'),
                            'bank' => __('Bank Transfer', 'smartcommerce'),
                            'bkash' => __('Bkash', 'smartcommerce'),
                            'nagad' => __('Nagad', 'smartcommerce'),
                            'rocket' => __('Rocket', 'smartcommerce'),
                            'upay' => __('Upay', 'smartcommerce'),
                            'none' => __('None', 'smartcommerce'),
                        ),
                        'value' => self::get('payment_method', ['cod']),
                    ),
                ),
            ),
        );
        $privacy_policy_text = file_get_contents(SMART_COMMERCE_TEMPLATE_DIR . 'text/privacy-policy.txt');
        $terms_conditions_text = file_get_contents(SMART_COMMERCE_TEMPLATE_DIR . 'text/terms-conditions.txt');
        $return_policy_text = file_get_contents(SMART_COMMERCE_TEMPLATE_DIR . 'text/return-policy.txt');
        $fields['pages'] = array(
            'title' => __('Pages', 'smartcommerce'),
            'fields' => array(
                'home_slider_images' => array(
                    'type' => 'ajax_file',
                    'name' => 'home_slider_images',
                    'settings' => array(
                        'label' => __('Home Page Slider Images', 'smartcommerce'),
                        'value' => self::get('home_slider_images'),
                        'preview' => true,
                        'multiple' => 'true',
                    ),
                ),
                'terms_and_conditions' => array(
                    'type' => 'wp_editor',
                    'name' => 'terms_and_conditions',
                    'settings' => array(
                        'label' => __('Terms and Conditions', 'smartcommerce'),
                        'value' => self::get('terms_and_conditions', $terms_conditions_text, true),
                        'placeholder' => __('Enter your terms and conditions', 'smartcommerce'),
                        'id' => 'terms_and_conditions',
                    )
                ),
                'privacy_policy' => array(
                    'type' => 'wp_editor',
                    'name' => 'privacy_policy',
                    'settings' => array(
                        'label' => __('Privacy Policy', 'smartcommerce'),
                        'value' => self::get('privacy_policy', $privacy_policy_text, true),
                        'placeholder' => __('Enter your privacy policy', 'smartcommerce'),
                        'id' => 'privacy_policy',
                    ),
                ),
                'return_policy' => array(
                    'type' => 'wp_editor',
                    'name' => 'return_policy',
                    'settings' => array(
                        'label' => __('Return Policy', 'smartcommerce'),
                        'value' => self::get('return_policy', $return_policy_text, true),
                        'placeholder' => __('Enter your return policy', 'smartcommerce'),
                        'id' => 'return_policy',
                    ),
                ),
            ),
        );
        
        $default_value = array(
            1 => array(
                'title' => __('Quality Products', 'smartcommerce'),
                'details' => __('We ensure every product meets high standards of durability and performance for maximum customer satisfaction.', 'smartcommerce'),
            ),
            2 => array(
                'title' => __('Fast & Reliable Delivery', 'smartcommerce'),
                'details' => __('Enjoy quick, doorstep delivery with real-time tracking so you know exactly when your order will arrive.', 'smartcommerce'),
            ),
            3 => array(
                'title' => __('Easy Returns & Refunds', 'smartcommerce'),
                'details' => __('Hassle-free return process with flexible policies to give you complete peace of mind.', 'smartcommerce'),
            ),
            4 => array(
                'title' => __('Exclusive Discounts & Offers', 'smartcommerce'),
                'details' => __('Special deals, seasonal sales, and member-only discounts to give you the best value.', 'smartcommerce'),
            ),
            5 => array( 
                'title' => __('24/7 Customer Support', 'smartcommerce'),
                'details' => __('Our support team is available anytime to assist you with orders, queries, or issues.', 'smartcommerce'),
            ),
        );

        // Footer Columns Upto 5
        for($i= 1; $i<=4; $i++){
            $fields['pages']['fields']['footer_column_'.$i] = array(
                'type' => 'text',
                'name' => 'footer_column_'.$i . '_title',
                'settings' => array(
                    'label' => __('Footer Column '.$i . ' Title ', 'smartcommerce'),
                    'value' => self::get('footer_column_'.$i . ' title', $default_value[$i]['title']),
                    'id' => 'footer_column_'.$i . ' title',
                    'row-class' => 'col-4',
                ),
            );
            // Details 
            $fields['pages']['fields']['footer_column_'.$i . '_details'] = array(
                'type' => 'text',
                'name' => 'footer_column_'.$i . '_details',
                'settings' => array(
                    'label' => __('Footer Column '.$i . ' Details', 'smartcommerce'),
                    'value' => self::get('footer_column_'.$i . '_details', $default_value[$i]['details']),
                    'row-class' => 'col-4',
                ),
            );
        }

        $methods = self::get('payment_method', ['cod']);
        if(in_array('bank', $methods)){
            $fields['payment']['fields']['payment_bank'] = array(
                'type' => 'textarea',
                'name' => 'payment_bank',
                'settings' => array(
                    'label' => __('Bank Account Information', 'smartcommerce'),
                    'value' => self::get('bank_account_info'),
                    'placeholder' => __('Enter your bank account information', 'smartcommerce'),
                    'id' => 'payment_bank',
                ),
            );
        }
        $mfs = array(
            'bkash' => __('bKash', 'smartcommerce'),
            'nagad' => __('Nagad', 'smartcommerce'),
            'rocket' => __('Rocket', 'smartcommerce'),
            'upay' => __('uPay', 'smartcommerce'),
        );
        foreach($mfs as $key => $value){
            if(in_array($key, $methods)){
                $fields['payment']['fields'][$key] = array(
                    'type' => 'text',
                    'name' => 'payment_' . $key,
                    'settings' => array(
                        'label' => __($value . ' Account', 'smartcommerce'),
                        'value' => self::get('payment_' . $key),
                        'placeholder' => __('Enter your ' . $value . ' account number', 'smartcommerce'),
                        'id' => 'payment_' . $key,
                    ),
                );
            }
        }



        $fields['delivery'] = array(
            'title' => __('Delivery', 'smartcommerce'),
            'fields' => array(
                'delivery_default_charge' => array(
                    'type' => 'number',
                    'name' => 'delivery_default_charge',
                    'settings' => array(
                        'label' => __('Default Delivery Charge', 'smartcommerce'),
                        'value' => self::get('delivery_default_charge'),
                        'placeholder' => __('Enter default delivery charge', 'smartcommerce'),
                        'data' => array(
                            'min' => 0,
                            'step' => '1',
                        ),
                    ),
                ),
                'delivery_charges' => array(
                    'type' => 'textarea',
                    'name' => 'delivery_charges',
                    'settings' => array(
                        'label' => __('Delivery Charges', 'smartcommerce') . " <a href='javascript:void(0)' data-ajax_action='insertDeliveryChoicesWithPrice' data-success_callback='insertDeliveryChocies' data-before_send_callback='scConfirm' class='sc-ajax-link sc-insert-districts'>". __("Insert districts", 'smartcommerce') ."</a>",
                        'value' => self::get('delivery_charges'),
                        'placeholder' => __('Enter delivery charges', 'smartcommerce'),
                    )
                ),
            )
        );

        $order_sms_options = array(
            'order_placed' => __('Order Placed', 'smartcommerce'),
            'order_confirmed' => __('Order Confirmed', 'smartcommerce'),
            'order_shipped' => __('Order Shipped', 'smartcommerce'),
            'order_delivered' => __('Order Delivered', 'smartcommerce'),
            'order_cancelled' => __('Order Cancelled', 'smartcommerce'),
            'order_completed' => __('Order Completed', 'smartcommerce'),
            'order_refunded' => __('Order Refunded', 'smartcommerce'),
        );
        $order_sms_formats = array(
            'order_placed' => __('Dear {name}, your order has been placed successfully. Your order ID is #{order_id}.', 'smartcommerce'),
            'order_confirmed' => __('Dear {name}, your order has been confirmed. Your order ID is #{order_id}.', 'smartcommerce'),
            'order_shipped' => __('Dear {name}, your order has been shipped. Your order ID is #{order_id}.', 'smartcommerce'),
            'order_delivered' => __('Dear {name}, your order has been delivered. Your order ID is #{order_id}.', 'smartcommerce'),
            'order_cancelled' => __('Dear {name}, your order has been cancelled. Your order ID is #{order_id}.', 'smartcommerce'),
            'order_completed' => __('Dear {name}, your order has been completed. Your order ID is #{order_id}.', 'smartcommerce'),
            'order_refunded' => __('Dear {name}, your order has been refunded. Your order ID is #{order_id}.', 'smartcommerce'),
        );
        $order_sms_options = apply_filters('smartcommerce_filter_order_sms_options', $order_sms_options);
        $fields['sms'] = array(
            'title' => __('SMS Settings', 'smartcommerce'),
            'fields' => array(
                'sms_active' => array(
                    'type' => 'radio',
                    'name' => 'sms_active',
                    'settings' => array(
                        'label' => __('Enable SMS', 'smartcommerce'),
                        'value' => self::get('sms_active'),
                        'placeholder' => __('Enable SMS', 'smartcommerce'),
                        'options' => array(
                            'yes' => __('Yes', 'smartcommerce'),
                            'no' => __('No', 'smartcommerce'),
                        ),
                    ),
                ),
                'sms_api_key' => array(
                    'type' => 'text',
                    'name' => 'sms_api_key',
                    'settings' => array(
                        'label' => __('SMS API Key', 'smartcommerce'),
                        'value' => self::get('sms_api_key'),
                        'placeholder' => __('Enter SMS API key', 'smartcommerce'),
                    ),
                ),
                'sms_sender' => array(
                    'type' => 'text',
                    'name' => 'sms_sender',
                    'settings' => array(
                        'label' => __('SMS Sender No', 'smartcommerce'),
                        'value' => self::get('sms_sender'),
                        'placeholder' => __('Enter SMS sender', 'smartcommerce'),
                    ),
                ),
                'sms_footer' => array(
                    'type' => 'text',
                    'name' => 'sms_footer',
                    'settings' => array(
                        'label' => __('SMS Footer', 'smartcommerce'),
                        'value' => self::get('sms_footer', self::get('shop_name')),
                        'placeholder' => __('Enter SMS footer', 'smartcommerce'),
                    ),
                ),
                'sms_rate' => array(
                    'type' => 'number',
                    'name' => 'sms_rate',
                    'settings' => array(
                        'label' => __('SMS Rate', 'smartcommerce'),
                        'value' => self::get('sms_rate', 0.4),
                        'placeholder' => __('Enter SMS rate', 'smartcommerce'),
                        'disabled' => !current_user_can('administrator'),
                    ),
                ),
                'sms_services' => array(
                    'type' => 'checkbox',
                    'name' => 'sms_services',
                    'settings' => array(
                        'label' => __('Send SMS when ', 'smartcommerce'),
                        'value' => self::get('sms_services'),
                        'placeholder' => __('Select SMS Services', 'smartcommerce'),
                        'options' => $order_sms_options,
                    ),
                )
            )
        );
        foreach($order_sms_options as $k=>$v){
            if(in_array($k, self::get('sms_services', []))){
                $fields['sms']['fields'][$k] = array(
                    'type' => 'textarea',
                    'name' => 'sms_' . $k . '_format',
                    'settings' => array(
                        'label' => __($v . ' SMS Format', 'smartcommerce'),
                        'value' => self::get('sms_' . $k . '_format', $order_sms_formats[$k], true),
                        'placeholder' => __('Enter SMS format for ' . $v, 'smartcommerce'),
                        'id' => 'sms_' . $k . '_format',
                    ),
                );
            }
        }
        $fields['design'] = array(
            'title' => __('Design', 'smartcommerce'),
            'fields' => array(
                'primary_color' => array(
                    'type' => 'color',
                    'name' => 'primary_color',
                    'settings' => array(
                        'label' => __('Primary Color', 'smartcommerce'),
                        'value' => self::get('primary_color'),
                        'row-class' => 'col-4'
                    ),
                ),
                'secondary_color' => array(
                    'type' => 'color',
                    'name' => 'secondary_color',
                    'settings' => array(
                        'label' => __('Secondary Color', 'smartcommerce'),
                        'value' => self::get('secondary_color'),
                        'row-class' => 'col-4'
                    ),
                ),
                'accent_color' => array(
                    'type' => 'color',
                    'name' => 'accent_color',
                    'settings' => array(
                        'label' => __('Accent Color', 'smartcommerce'),
                        'value' => self::get('accent_color'),
                        'row-class' => 'col-4'
                    ),
                ),
                'text_color' => array(
                    'type' => 'color',
                    'name' => 'text_color',
                    'settings' => array(
                        'label' => __('Text Color', 'smartcommerce'),
                        'value' => self::get('text_color'),
                        'row-class' => 'col-4'
                    ),
                ),
                'font_family' => array(
                    'type' => 'select',
                    'name' => 'font_family',
                    'settings' => array(
                        'label' => __('Font Family', 'smartcommerce'),
                        'value' => self::get('font_family'),
                        'placeholder' => __('Select font family', 'smartcommerce'),
                        'options' => array_combine(Admin::getGoogleFonts(), Admin::getGoogleFonts()),
                        'row-class' => 'col-4'
                    ),
                ),
                'font_size' => array(
                    'type' => 'select',
                    'name' => 'font_size',
                    'settings' => array(
                        'label' => __('Font Size (px)', 'smartcommerce'),
                        'value' => self::get('font_size', 16),
                        'placeholder' => __('Select font size', 'smartcommerce'),
                        'options' => array_combine(range(10, 30), range(10, 30)),
                        'row-class' => 'col-4'
                    ),
                ),
                'tablet_font_size' => array(
                    'type' => 'select',
                    'name' => 'tablet_font_size',
                    'settings' => array(
                        'label' => __('Tablet Font Size (px)', 'smartcommerce'),
                        'value' => self::get('tablet_font_size', 16),
                        'placeholder' => __('Select tablet font size', 'smartcommerce'),
                        'options' => array_combine(range(10, 100), range(10, 100)),
                        'row-class' => 'col-4'
                    ),
                ),
                'mobile_font_size' => array(
                    'type' => 'select',
                    'name' => 'mobile_font_size',
                    'settings' => array(
                        'label' => __('Mobile Font Size (px)', 'smartcommerce'),
                        'value' => self::get('mobile_font_size', 16),
                        'placeholder' => __('Select mobile font size', 'smartcommerce'),
                        'options' => array_combine(range(10, 100), range(10, 100)),
                        'row-class' => 'col-4'
                    ),
                ),
                'h1_font_size' => array(
                    'type' => 'select',
                    'name' => 'h1_font_size',
                    'settings' => array(
                        'label' => __('H1 Font Size (px)', 'smartcommerce'),
                        'value' => self::get('h1_font_size', 48),
                        'placeholder' => __('Select h1 font size', 'smartcommerce'),
                        'options' => array_combine(range(10, 100), range(10, 100)),
                        'row-class' => 'col-4'
                    ),
                ),
                'h2_font_size' => array(
                    'type' => 'select',
                    'name' => 'h2_font_size',
                    'settings' => array(
                        'label' => __('H2 Font Size (px)', 'smartcommerce'),
                        'value' => self::get('h2_font_size', 36),
                        'placeholder' => __('Select h2 font size', 'smartcommerce'),
                        'options' => array_combine(range(10, 100), range(10, 100)),
                        'row-class' => 'col-4'
                    ),
                ),
                'h3_font_size' => array(
                    'type' => 'select',
                    'name' => 'h3_font_size',
                    'settings' => array(
                        'label' => __('H3 Font Size (px)', 'smartcommerce'),
                        'value' => self::get('h3_font_size', 24),
                        'placeholder' => __('Select h3 font size', 'smartcommerce'),
                        'options' => array_combine(range(10, 100), range(10, 100)),
                        'row-class' => 'col-4'
                    ),
                ),
                'h4_font_size' => array(
                    'type' => 'select',
                    'name' => 'h4_font_size',
                    'settings' => array(
                        'label' => __('H4 Font Size (px)', 'smartcommerce'),
                        'value' => self::get('h4_font_size', 20),
                        'placeholder' => __('Select h4 font size', 'smartcommerce'),
                        'options' => array_combine(range(10, 100), range(10, 100)),
                        'row-class' => 'col-4'
                    ),
                ),
                'h5_font_size' => array(
                    'type' => 'select',
                    'name' => 'h5_font_size',
                    'settings' => array(
                        'label' => __('H5 Font Size (px)', 'smartcommerce'),
                        'value' => self::get('h5_font_size', 18),
                        'placeholder' => __('Select h5 font size', 'smartcommerce'),
                        'options' => array_combine(range(10, 100), range(10, 100)),
                        'row-class' => 'col-4'
                    ),
                ),
                'h6_font_size' => array(
                    'type' => 'select',
                    'name' => 'h6_font_size',
                    'settings' => array(
                        'label' => __('H6 Font Size (px)', 'smartcommerce'),
                        'value' => self::get('h6_font_size', 14),
                        'placeholder' => __('Select h6 font size', 'smartcommerce'),
                        'options' => array_combine(range(10, 100), range(10, 100)),
                        'row-class' => 'col-4'
                    ),
                ),
                'tablet_heading_scale' => array(
                    'type' => 'number',
                    'name' => 'tablet_heading_scale',
                    'settings' => array(
                        'label' => __('Tablet Heading Scale', 'smartcommerce'),
                        'value' => self::get('tablet_heading_scale', 0.8),
                        'row-class' => 'col-4',
                        'data' => array(
                            'min' => 0,
                            'step' => 'any',
                            'max' => 5,
                        ),
                    ),
                ),
                'mobile_heading_scale' => array(
                    'type' => 'number',
                    'name' => 'mobile_heading_scale',
                    'settings' => array(
                        'label' => __('Mobile Heading Scale', 'smartcommerce'),
                        'value' => self::get('mobile_heading_scale', 0.65),
                        'row-class' => 'col-4',
                        'data' => array(
                            'min' => 0,
                            'step' => 'any',
                            'max' => 5,
                        ),
                    ),
                ),
                'container_width' => array(
                    'type' => 'select',
                    'name' => 'container_width',
                    'settings' => array(
                        'label' => __('Layout Width (px)', 'smartcommerce'),
                        'value' => self::get('container_width', 1024),
                        'placeholder' => __('Select container width', 'smartcommerce'),
                        'options' => array(
                            '1024' => __('1024px', 'smartcommerce'),
                            '1280' => __('1280px', 'smartcommerce'),
                            '1366' => __('1366px', 'smartcommerce'),
                            '1440' => __('1440px', 'smartcommerce'),
                            '1600' => __('1600px', 'smartcommerce'),
                            '1920' => __('1920px', 'smartcommerce'),
                        ),
                        'row-class' => 'col-4'
                    ),
                ),
                'border_radius' => array(
                    'type' => 'select',
                    'name' => 'border_radius',
                    'settings' => array(
                        'label' => __('Border Radius (px)', 'smartcommerce'),
                        'value' => self::get('border_radius', 4),
                        'placeholder' => __('Select border radius', 'smartcommerce'),
                        'options' => array_combine(range(0, 100), range(0, 100)),
                        'row-class' => 'col-4'
                    ),
                ),
                'desktop_logo_height' => array(
                    'type' => 'number',
                    'name' => 'desktop_logo_height',
                    'settings' => array(
                        'label' => __('Desktop Logo Height (px)', 'smartcommerce'),
                        'value' => self::get('desktop_logo_height', 50),
                        'placeholder' => __('Input desktop height', 'smartcommerce'),
                        'row-class' => 'col-4'
                    ),
                ),
                'mobile_logo_height' => array(
                    'type' => 'number',
                    'name' => 'mobile_logo_height',
                    'settings' => array(
                        'label' => __('Mobile Logo Height (px)', 'smartcommerce'),
                        'value' => self::get('mobile_logo_height', 25),
                        'placeholder' => __('Input mobile height', 'smartcommerce'),
                        'row-class' => 'col-4'
                    ),
                ),
            ),
        );
        $fields['print'] = array(
            'title' => __('Print Settings', 'smartcommerce'),
            'fields' => array(
                'invoice_header' => array(
                    'type' => 'html',
                    'name' => 'invoice_header',
                    'settings' => array(
                        'html' => '<h5>' . __('Invoice Settings', 'smartcommerce') . '</h5>',
                    )
                ),
                'print_invoice_template' => array(
                    'type' => 'select',
                    'name' => 'print_invoice_template',
                    'settings' => array(
                        'label' => __('Invoice Template', 'smartcommerce'),
                        'value' => self::get('print_invoice_template'),
                        'placeholder' => __('Select invoice template', 'smartcommerce'),
                        'options' => array(
                            'invoice-1' => __('Invoice 1', 'smartcommerce'),
                        ),
                        'row-class' => 'col-4'
                    )
                ),
                'print_invoice_paper_size' => array(
                    'type' => 'select',
                    'name' => 'print_invoice_paper_size',
                    'settings' => array(
                        'label' => __('Paper Size', 'smartcommerce'),
                        'value' => self::get('print_invoice_paper_size', 'a4'),
                        'placeholder' => __('Select paper size', 'smartcommerce'),
                        'options' => array(
                            'a5' => __('A5 (5.83in x 8.27in)', 'smartcommerce'),
                            'a4' => __('A4 (8.27in x 11.69in)', 'smartcommerce'),
                            'a3' => __('A3 (11.69in x 16.54in)', 'smartcommerce'),
                            'a2' => __('A2 (11.69in x 16.54in)', 'smartcommerce'),
                            'a1' => __('A1 (16.54in x 23.39in)', 'smartcommerce'),
                            'a0' => __('A0 (23.39in x 33.11in)', 'smartcommerce'),
                            'letter' => __('Letter (8.5in x 11in)', 'smartcommerce'),
                            'legal' => __('Legal (8.5in x 14in)', 'smartcommerce'),
                        ),
                        'row-class' => 'col-4'
                    ),
                ),
                'print_invoice_paper_orientation' => array(
                    'type' => 'select',
                    'name' => 'print_invoice_paper_orientation',
                    'settings' => array(
                        'label' => __('Paper Orientation', 'smartcommerce'),
                        'value' => self::get('print_invoice_paper_orientation', '0.25'),
                        'placeholder' => __('Select paper orientation', 'smartcommerce'),
                        'options' => array(
                            'portrait' => __('Portrait', 'smartcommerce'),
                            'landscape' => __('Landscape', 'smartcommerce'),
                        ),
                        'row-class' => 'col-4'
                    ),
                ),
                'print_invoice_font_size' => array(
                    'type' => 'select',
                    'name' => 'print_invoice_font_size',
                    'settings' => array(
                        'label' => __('Font Size (px)', 'smartcommerce'),
                        'value' => self::get('print_invoice_font_size', 12),
                        'placeholder' => __('Select font size', 'smartcommerce'),
                        'options' => array_combine(range(8, 30), range(8, 30)),
                        'row-class' => 'col-4'
                    ),
                ),
                'print_invoice_paper_margin_top' => array(
                    'type' => 'number',
                    'name' => 'print_invoice_paper_margin_top',
                    'settings' => array(
                        'label' => __('Margin Top (in)', 'smartcommerce'),
                        'value' => self::get('print_invoice_paper_margin_top', '0.25'),
                        'placeholder' => __('Enter paper margin top', 'smartcommerce'),
                        'data' => array(
                            'min' => 0,
                            'step' => 'any',
                        ),
                        'row-class' => 'col-4'
                    ),
                ),
                'print_invoice_paper_margin_bottom' => array(
                    'type' => 'number',
                    'name' => 'print_invoice_paper_margin_bottom',
                    'settings' => array(
                        'label' => __('Margin Bottom (in)', 'smartcommerce'),
                        'value' => self::get('print_invoice_paper_margin_bottom', '0.25'),
                        'placeholder' => __('Enter paper margin bottom', 'smartcommerce'),
                        'data' => array(
                            'min' => 0,
                            'step' => 'any',
                        ),
                        'row-class' => 'col-4'
                    ),
                ),
                'print_invoice_paper_margin_left' => array(
                    'type' => 'number',
                    'name' => 'print_invoice_paper_margin_left',
                    'settings' => array(
                        'label' => __('Margin Left (in)', 'smartcommerce'),
                        'value' => self::get('print_invoice_paper_margin_left', '0.25'),
                        'placeholder' => __('Enter paper margin left', 'smartcommerce'),
                        'data' => array(
                            'min' => 0,
                            'step' => 'any',
                        ),
                        'row-class' => 'col-4'
                    ),
                ),
                'print_invoice_paper_margin_right' => array(
                    'type' => 'number',
                    'name' => 'print_invoice_paper_margin_right',
                    'settings' => array(
                        'label' => __('Margin Right (in)', 'smartcommerce'),
                        'value' => self::get('print_invoice_paper_margin_right', '0.25'),
                        'placeholder' => __('Enter paper margin right', 'smartcommerce'),
                        'data' => array(
                            'min' => 0,
                            'step' => 'any',
                        ),
                        'row-class' => 'col-4'
                    ),
                ),
            ),
        );
        $fields['seo'] = array(
            'title' => __('SEO Settings', 'smartcommerce'),
            'fields' => array(
                'google_analytics_id' => array(
                    'type' => 'text',
                    'name' => 'google_analytics_id',
                    'settings' => array(
                        'label' => __('Google Analytics ID', 'smartcommerce'),
                        'value' => self::get('google_analytics_id', ''),
                        'placeholder' => __('Enter Google Analytics ID', 'smartcommerce'),
                    ),
                ),
                'google_tag_id' => array(
                    'type' => 'text',
                    'name' => 'google_tag_id',
                    'settings' => array(
                        'label' => __('Google Tag ID', 'smartcommerce'),
                        'value' => self::get('google_tag_id', ''),
                        'placeholder' => __('Enter Google Tag ID', 'smartcommerce'),
                    ),
                ),
                'facebook_app_id' => array(
                    'type' => 'text',
                    'name' => 'facebook_app_id',
                    'settings' => array(
                        'label' => __('Facebook App ID', 'smartcommerce'),
                        'value' => self::get('facebook_app_id', '942342894766699'),
                        'row-class' => 'col-4',
                        'placeholder' => __('Enter Facebook App ID', 'smartcommerce'),
                    ),
                ),
                'facebook_pixel_id' => array(
                    'type' => 'text',
                    'name' => 'facebook_pixel_id',
                    'settings' => array(
                        'label' => __('Facebook Pixel ID', 'smartcommerce'),
                        'value' => self::get('facebook_pixel_id', ''),
                        'placeholder' => __('Enter Facebook Pixel ID', 'smartcommerce'),
                        'row-class' => 'col-4',
                    ),
                ),
                'facebook_pixel_access_token' => array(
                    'type' => 'text',
                    'name' => 'facebook_access_token',
                    'settings' => array(
                        'label' => __('Facebook Access Token', 'smartcommerce'),
                        'value' => self::get('facebook_access_token', ''),
                        'row-class' => 'col-4',
                        'placeholder' => __('Enter Facebook Access Token', 'smartcommerce'),
                    ),
                ),
                'empty'=> array(
                    'type' => 'html',
                    'name' => 'empty',
                    'settings' => array(
                        'row-class' => 'col-4',
                        'html' => "<div style='width: 100%; height: 60px;'></div>",
                    )
                ),
                'google_product_category' => array(
                    'type' => 'text',
                    'name' => 'google_product_category',
                    'settings' => array(
                        'label' => __("Google Product Category ID <a href='https://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt' target='_blank'>View Category List</a>" , 'smartcommerce'),
                        'value' => self::get('google_product_category'),
                        'row-class' => 'col-6'
                    )
                ),
                'facebook_product_category' => array(
                    'type' => 'text',
                    'name' => 'facebook_product_category',
                    'settings' => array(
                        'label' => __("Facebook Product Category ID <a href='".SMART_COMMERCE_ASSETS_URL."txt/fb_product_categories_en_US.txt' target='_blank'>View Cateogry List</a>" , 'smartcommerce'),
                        'value' => self::get('facebook_product_category', ''),
                        'row-class' => 'col-6',
                    ),
                )
            ),
        );

        return apply_filters('smartcommerce_filter_settings_fields', $fields);
    }

    /**
     * Get Settings Form 
     * 
     * @return string 
     * 
     * @since 1.0
     * @access public 
     */
    public static function getSettingsForm($group)
    {
        $setting_fields = self::getSettingsFields();
        if(empty($setting_fields)) return '';
        $fields = $setting_fields[$group] ?? [];
        $fields['fields']['submit'] = array(
            'type' => 'button',
            'name' => 'submit',
            'settings' => array(
                'id' => 'submit',
                'type' => 'submit',
                'value' => __('Save', 'smartcommerce'),
                'class' => 'sc-button sc-button-primary'
            ),
        );
        $hidden_fields = array(
            'action' => array(
                'type' => 'hidden',
                'name' => 'action',
                'settings' => array(
                    'value' => 'smartcommerce_ajax',
                )
            ),
            'ajax_action' => array(
                'type' => 'hidden',
                'name' => 'ajax_action',
                'settings' => array(
                    'value' => 'saveSettings',
                )
            ),
            '_wpnonce' => array(
                'type' => 'hidden',
                'name' => '_wpnonce',
                'settings' => array(
                    'value' => wp_create_nonce('smartcommerce'),
                )
                ),
            'before_send_callback' => array(
                'type' => 'hidden',
                'name' => 'before_send_callback',
                'settings' => array(
                    'value' => 'saveSettingsBeforeSendCallback',
                )
                ),
            'success_callback' => array(
                'type' => 'hidden',
                'name' => 'success_callback',
                'settings' => array(
                    'value' => 'saveSettingsSuccessCallback',
                )
            ),
            'error_callback' => array(
                'type' => 'hidden',
                'name' => 'error_callback',
                'settings' => array(
                    'value' => 'saveSettingsErrorCallback',
                )
            ),
        );
        ob_start();
        ?>
        <div class="sc-admin-settings-wrap">
            <form action="" class="sc-form sc-ajax-form">
                <div class="sc-form-group">
                    <h3 class="sc-form-group-title sc-fleft"><?php echo $fields['title'] ?? ''; ?></h3>
                    <?php foreach($fields['fields'] as $field){ ?>
                        <?php 
                            if($field['type'] == 'hidden'){
                                $hidden_fields[] = $field;
                                continue;
                            } 
                        ?>
                        <div class="sc-form-row <?php echo $field['settings']['row-class'] ?? ''; ?>" data-field="<?php echo $field['name'] ?? ''; ?>">
                            <div class="sc-label-wrap">
                                <label for="<?php echo $field['name'] ?? ''; ?>"><?php echo $field['settings']['label'] ?? ''; ?></label>
                            </div>
                            <div class="sc-field-wrap">
                                <?php echo Form::generateElement($field['type'], $field['name'], $field['settings']); ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php foreach($hidden_fields as $field){ ?>
                        <?php echo Form::generateElement($field['type'], $field['name'], $field['settings']); ?>
                    <?php } ?>
                </div>
            </form>

        </div>
        <?php 
        return ob_get_clean();
    }
}

Settings::instance();