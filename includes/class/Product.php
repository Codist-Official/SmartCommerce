<?php 
namespace SmartCommerce;

class Product extends Post {

    public $post_type = 'sc_product';
    public $post_slug = 'sc-product';
    public $post_name = 'Product';

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

        // Filter Publish Fields 
        add_filter('smartcommerce_filter_' . $this->post_type . '_publish_fields', [$this, 'filterPublishFields']);

        // Create facebook feed 
        add_action('init', [$this, 'registerFacebookFeed']);

        // Control page title 
        add_filter( 'document_title_parts', [$this, 'controlPageTitle'], 999, 1 );

    }

    /**
     * Register Facebook Feed 
     * @return void 
     * @since 1.0.0
     * @access public 
     */
    public function registerFacebookFeed()
    {
        add_feed( 'facebook-sc-products', [$this, 'renderFacebookFeed'] );
    }


    /**
     * WP Init
     * 
     * @return void
     * @since 1.0.0
     */
    public function wpInit()
    {

    }


    /**
     * Filter Product Details 
     * 
     * @return string 
     * @since 1.0.0
     * @access public 
     */
    public function filterContent($content)
    {
        return $content;
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
     * Filter Publish Fields 
     * 
     * @return array 
     * @since 1.0
     */
    public function filterPublishFields()
    {
        $action = $this->post ? 'editPost' : 'publishPost';
        $variation_html = "<button type='button' class='sc-button sc-button-primary sc-fright' id='add_product_variation_btn' data-action='add_var'>Add Product Variation</button><ul class='sc-product-variation-list'>";
        $variation_data = isset($this->metadata['variation_data']) ? maybe_unserialize($this->metadata['variation_data'][0]) : [];
        $variation_list = '';
        if(!is_array($variation_data)) $variation_data = [];

        if(!empty($variation_data)){
            ob_start();
            ?>
            <li class='header-row'>
                <div class='var-item' data-item-name='variation_name'><?php _e('Variation', 'smartcommerce'); ?></div>
                <div class='var-item' data-item-name='variation_images'><?php _e('Img', 'smartcommerce'); ?></div>
                <div class='var-item' data-item-name='variation_description'><?php _e('Desc', 'smartcommerce'); ?></div>
                <div class='var-item' data-item-name='variation_price'><?php _e('Selling<br>Price', 'smartcommerce'); ?></div>
                <div class='var-item' data-item-name='variation_regular_price'><?php _e('Regular<br>Price', 'smartcommerce'); ?></div>
                <div class='var-item' data-item-name='variation_sku'><?php _e('SKU', 'smartcommerce'); ?></div>
                <div class='var-item' data-item-name='variation_stock'><?php _e('Stock<br>Qty', 'smartcommerce'); ?></div>
                <div class='var-item' data-item-name='variation_size_options'><?php _e('Size<br>Options', 'smartcommerce'); ?></div>
                <div class='var-item' data-item-name='variation_color_options'><?php _e('Color<br>Options', 'smartcommerce'); ?></div>
                <div class='var-item' data-item-name='variation_actions'></div>
            </li>
            <?php
            foreach($variation_data as $var){
                ?>
                <li data-uid='<?php echo $var['uid']; ?>'>
                    <div class='var-item' data-item-name='variation_name'>
                        <?php echo Form::generateElement('text', 'variation_name[]', array('value' => $var['name'], 'required' => true)); ?>
                        <?php echo Form::generateElement('text', 'variation_unit[]', array('value' => $var['unit'] ?? '', 'placeholder' => 'Enter Unit')); ?>
                        <?php echo Form::generateElement('hidden', 'variation_uid[]', array('value' => $var['uid'] ?? '')); ?>
                    </div>
                    <div class='var-item' data-item-name='variation_images'>
                        <?php echo Form::generateElement('ajax_file', 'variation_images[]', array('value' => $var['images'] ?? '',  'preview' => true, 'data' => array('accept' => 'image/*', 'multiple' => true))); ?>
                    </div>
                    <div class='var-item' data-item-name='variation_description'>
                    <?php echo Form::generateElement('text', 'variation_description[]', array('value' => $var['description'] ?? '')); ?>
                    </div>
                    <div class='var-item' data-item-name='variation_price'>
                        <?php echo Form::generateElement('text', 'variation_price[]', array('value' => $var['price'] ?? '', 'required' => true)); ?>
                    </div>
                    <div class='var-item' data-item-name='variation_regular_price'>
                        <?php echo Form::generateElement('text', 'variation_regular_price[]', array('value' => $var['regular_price'] ?? '', 'required' => false)); ?>
                    </div>
                    <div class='var-item' data-item-name='variation_sku'>
                        <?php echo Form::generateElement('text', 'variation_sku[]', array('value' => $var['sku'] ?? '')); ?>
                    </div>
                    <div class='var-item' data-item-name='variation_stock'>
                        <?php echo Form::generateElement('text', 'variation_stock[]', array('value' => $var['stock'] ?? 99999)); ?>
                    </div>
                    <div class='var-item' data-item-name='variation_size_options'>
                        <?php echo Form::generateElement('text', 'variation_size_options[]', array('value' => $var['size_options'] ?? '')); ?>
                    </div>
                    <div class='var-item' data-item-name='variation_color_options'>
                        <?php echo Form::generateElement('text', 'variation_color_options[]', array('value' => $var['color_options'] ?? '')); ?>
                    </div>
                    <div class='var-item' data-item-name='variation_actions' style='flex-direction: column;'>
                        <a href='javascript:void(0)' class='sc-icon-wrap copy-product-var'><i class='fa fa-plus'></i></a>
                        <a href='javascript:void(0)' class='sc-icon-wrap delete-product-var'><i class='fa fa-trash'></i></a>
                    </div>
                </li>
                <?php 
            }
            $variation_list = ob_get_clean();
        }
        $variation_html .= $variation_list;
        $variation_html .= "</ul>";

        $parent_child_cats = [];
        $parent_cats = ProductCategory::getAll(0);
        if(!empty($parent_cats)){
            foreach($parent_cats as $cat){
                $parent_child_cats[$cat->term_id] = $cat->name;
                $child_cats = ProductCategory::getAll($cat->term_id);
                if(!empty($child_cats)){
                    foreach($child_cats as $child_cat){
                        $parent_child_cats[$child_cat->term_id] = '&nbsp; &nbsp; ---' . $child_cat->name;
                    }
                }
            }
        }

        // Get all product categories tagged to this post 
        $product_cats = $this->post ? wp_get_post_terms($this->post->ID, 'sc_product_category') : [];
        $current_cats = [];
        if(!empty($product_cats)){
            foreach($product_cats as $cat){
                $current_cats[] = $cat->term_id;
            }
        }

        $delivery_charges = $this->metadata['delivery_charges'][0] ?? ''; 
        if(empty($delivery_charges)) $delivery_charges = Settings::get('delivery_charges');

        $fields = array(
            'basic' => array(
                'title' => __('Basic Information', 'smartcommerce'),
                'fields' => array(
                    'post_title' => array(
                        'type' => 'text',
                        'name' => 'post_title',
                        'settings' => array(
                            'value' => $this->post->post_title ?? '',
                            'label' => __('Product Title', 'smartcommerce'),
                            'placeholder' => __('Enter Product Title', 'smartcommerce'),
                            'required' => true,
                            'class' => 'sc-form-control',
                        )
                    ),
                    'post_content' => array(
                        'type' => 'textarea',
                        'name' => 'post_content',
                        'settings' => array(
                            'value' => $this->post->post_content ?? '',
                            'label' => __('Long Description', 'smartcommerce'),
                            'placeholder' => __('Enter Long Description', 'smartcommerce'),
                            'required' => true,
                            'class' => 'sc-form-control',
                        )
                    ),
                    'post_excerpt' => array(
                        'type' => 'textarea',
                        'name' => 'post_excerpt',
                        'settings' => array(
                            'value' => $this->post->post_excerpt ?? '',
                            'label' => __('Short Description', 'smartcommerce'),
                            'placeholder' => __('Enter Short Description', 'smartcommerce'),
                            'required' => true,
                            'class' => 'sc-form-control',
                        )
                    ),
                    'google_product_category' => array(
                        'type' => 'number',
                        'name' => 'google_product_category',
                        'settings' => array(
                            'value' => isset($this->metadata['google_product_category']) ? $this->metadata['google_product_category'][0] : '',
                            'label' => __("Google Product Category ID <a href='https://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt' target='_blank'>View Category List</a>" , 'smartcommerce'),
                            'placeholder' => __('Enter Google Product Category', 'smartcommerce'),
                            'required' => false,
                            'row_class' => 'col-3',
                        )
                    ),
                    'facebook_product_category' => array(
                        'type' => 'number',
                        'name' => 'facebook_product_category',
                        'settings' => array(
                            'value' => isset($this->metadata['facebook_product_category']) ? $this->metadata['facebook_product_category'][0] : '',
                            'label' => __("Facebook Product Category ID <a href='".SMART_COMMERCE_ASSETS_URL."txt/fb_product_categories_en_US.txt' target='_blank'>View Cateogry List</a>" , 'smartcommerce'),
                            'placeholder' => __('Enter Facebook Product Category', 'smartcommerce'),
                            'required' => false,
                            'row_class' => 'col-3',
                        )
                    )
                )
            ),
            'price' => array(
                'title' => __('Price', 'smartcommerce'),
                'fields' => array(
                    'regular_price' => array(
                        'type' => 'number',
                        'name' => 'regular_price',
                        'settings' => array(
                            'value' => isset($this->metadata['regular_price']) ? $this->metadata['regular_price'][0] : 0,
                            'label' => __('Regular Price', 'smartcommerce'),
                            'placeholder' => __('Enter Price', 'smartcommerce'),
                            'required' => true,
                            'class' => 'sc-form-control',
                            'data' => array(
                                'step' => 'any',
                                'min' => 0,
                            ),
                            'row_class' => 'col-3',
                        )
                    ),
                    'discount' => array(
                        'type' => 'number',
                        'name' => 'discount',
                        'settings' => array(
                            'value' => isset($this->metadata['discount']) ? $this->metadata['discount'][0] : 0,
                            'label' => __('Discount Amount', 'smartcommerce'),
                            'placeholder' => __('Enter Discount Amount', 'smartcommerce'),
                            'data' => array(
                                'step' => 'any',
                                'min' => 0,
                            ),
                            'row_class' => 'col-3',
                        )
                    ),
                    'display_price' => array(
                        'type' => 'number',
                        'name' => 'display_price',
                        'settings' => array(
                            'value' => isset($this->metadata['regular_price']) ? $this->metadata['regular_price'][0] - $this->metadata['discount'][0] : 0,
                            'label' => __('Selling Price', 'smartcommerce'),
                            'required' => false,
                            'class' => 'sc-form-control',
                            'disabled' => true,
                            'data' => array(
                                'step' => 'any',
                                'min' => 0,
                            ),
                            'row_class' => 'col-3',
                        )
                    ),
                    'selling_price' => array(
                        'type' => 'hidden',
                        'name' => 'selling_price',
                        'settings' => array(
                            'value' => isset($this->metadata['regular_price']) ? $this->metadata['regular_price'][0] - $this->metadata['discount'][0] : 0,
                            'row_class' => 'col-3',
                        )
                    ),
                    'delivery_charges' => array(
                        'type' => 'textarea',
                        'name' => 'delivery_charges',
                        'settings' => array(
                            'value' => $delivery_charges,
                            'label' => __('Delivery Charges', 'smartcommerce'),
                            'placeholder' => __('Enter Delivery Charges', 'smartcommerce'),
                        )
                    )
                )
            ),
            'stock' => array(
                'title' => __('Stock', 'smartcommerce'),
                'fields' => array(
                    'sku' => array(
                        'type' => 'text',
                        'name' => 'sku',
                        'settings' => array(
                            'value' => isset($this->metadata['sku']) ? $this->metadata['sku'][0] : '',
                            'label' => __('SKU', 'smartcommerce'),
                            'placeholder' => __('Enter SKU', 'smartcommerce'),
                            'required' => false,
                            'class' => 'sc-form-control',
                         )
                    ),
                    'stock_type' => array(
                        'type' => 'select',
                        'name' => 'stock_type',
                        'settings' => array(
                            'value' => isset($this->metadata['stock_type']) ? $this->metadata['stock_type'][0] : '',
                            'options' => array(
                                'unlimited' => __('Unlimited Stock', 'smartcommerce'),
                                'limited' => __('Limited Stock', 'smartcommerce'),
                                'outofstock' => __('Out of Stock', 'smartcommerce'),
                            ),
                            'label' => __('Stock Type', 'smartcommerce'),
                            'placeholder' => __('Select Stock Type', 'smartcommerce'),
                            'required' => true,
                            'row_class' => 'col-3',
                        )
                    ),
                    'stock_quantity' => array(
                        'type' => 'number',
                        'name' => 'stock_quantity',
                        'settings' => array(
                            'value' => isset($this->metadata['stock_quantity']) ? $this->metadata['stock_quantity'][0] : '',
                            'label' => __('Stock Quantity', 'smartcommerce'),
                            'placeholder' => __('Enter Stock Quantity', 'smartcommerce'),
                            'required' => false,
                            'class' => 'sc-form-control',
                            'row_class' => 'col-3',
                        )
                    ),
                    'stock_unit' => array(
                        'type' => 'select',
                        'name' => 'stock_unit',
                        'settings' => array(
                            'value' => isset($this->metadata['stock_unit']) ? $this->metadata['stock_unit'][0] : '',
                            'label' => __('Stock Unit', 'smartcommerce'),
                            'placeholder' => __('Enter Stock Unit', 'smartcommerce'),
                            'options' => $this->getStockUnits(),
                            'required' => false,
                            'class' => 'sc-form-control',
                            'row_class' => 'col-3',
                        )
                    ),
                    'brand' => array(
                        'type' => 'select',
                        'name' => 'brand',
                        'settings' => array(
                            'value' => isset($this->metadata['brand']) ? $this->metadata['brand'][0] : '',
                            'label' => __('Brand', 'smartcommerce'),
                            'placeholder' => __('Select Brand', 'smartcommerce'),
                            'options' => Brand::instance()->getAllPosts(array('post_type' => 'sc_brand'), true),
                            'required' => false,
                            'row_class' => 'col-1'
                        )
                    ),
                    'size_options' => array(
                        'type' => 'text',
                        'name' => 'size_options',
                        'settings' => array(
                            'value' => isset($this->metadata['size_options']) ? $this->metadata['size_options'][0] : '',
                            'label' => __('Size Options (Comma Separated)', 'smartcommerce'),
                            'placeholder' => __('S, M, L, XL, etc.', 'smartcommerce'),
                            'required' => false,
                            'class' => 'sc-form-control',
                            'row_class' => 'col-3',
                        )
                    ),
                    'color_options' => array(
                        'type' => 'text',
                        'name' => 'color_options',
                        'settings' => array(
                            'value' => isset($this->metadata['color_options']) ? $this->metadata['color_options'][0] : '',
                            'label' => __('Color Options (Comma Separated)', 'smartcommerce'),
                            'placeholder' => __('Black, White, Red, etc.', 'smartcommerce'),
                            'required' => false,
                            'class' => 'sc-form-control',
                            'row_class' => 'col-3',
                        )
                    )

                )
            ),
            'media' => array(
                'title' => __('Image & Video', 'smartcommerce'),
                'fields' => array(
                    'post_thumbnail' => array(
                        'type' => 'ajax_file',
                        'name' => 'post_thumbnail',
                        'settings' => array(
                            'value' => has_post_thumbnail($this->id) ? get_post_thumbnail_id($this->id) : '',
                            'label' => __('Main Image', 'smartcommerce'),
                            'placeholder' => __('Select Main Image', 'smartcommerce'),
                            'required' => false,
                            'class' => 'wp_ajax_upload',
                            'preview' => true,
                            'data' => array(
                                'accept' => 'image/*',
                            ),
                        )
                    ),
                    'extra_images' => array(
                        'type' => 'ajax_file',
                        'name' => 'extra_images[]',
                        'settings' => array(
                            'value' => isset($this->metadata['extra_images']) ? $this->metadata['extra_images'][0] : '',
                            'label' => __('Extra Images', 'smartcommerce'),
                            'placeholder' => __('Select Extra Images', 'smartcommerce'),
                            'required' => false,
                            'class' => 'wp_ajax_upload',
                            'preview' => true,
                            'data' => array(
                                'accept' => 'image/*',
                                'multiple' => true,
                            ),
                        )
                    ),
                    'youtube_video_url' => array(
                        'type' => 'url',
                        'name' => 'youtube_video_url',
                        'settings' => array(
                            'value' => isset($this->metadata['youtube_video_url']) ? $this->metadata['youtube_video_url'][0] : '',
                            'label' => __('Youtube Video URL', 'smartcommerce'),
                            'placeholder' => __('Enter Youtube Video URL', 'smartcommerce'),
                            'required' => false,
                            'class' => 'sc-form-control',
                        )
                    )
                )
            ),
            'variation' => array(
                'title' => __('Product Variations', 'smartcommerce'),
                'fields' => array(
                    'variation_name' => array(
                        'type' => 'html',
                        'name' => 'add_var_btn',
                        'settings' => array(
                            'html' => $variation_html,
                        )
                    )
                )
            ),

            'seo' => array(
                'title' => __('SEO', 'smartcommerce'),
                'fields' => array(
                    'meta_title' => array(
                        'type' => 'text',
                        'name' => 'meta_title',
                        'settings' => array(
                            'value' => isset($this->metadata['meta_title']) ? $this->metadata['meta_title'][0] : '',
                            'label' => __('Meta Title', 'smartcommerce'),
                            'placeholder' => __('Enter Meta Title', 'smartcommerce'),
                            'required' => false,
                            'class' => 'sc-form-control',
                        )
                    ),
                    'meta_description' => array(
                        'type' => 'textarea',
                        'name' => 'meta_description',
                        'settings' => array(
                            'value' => isset($this->metadata['meta_description']) ? $this->metadata['meta_description'][0] : '',
                            'label' => __('Meta Description', 'smartcommerce'),
                            'placeholder' => __('Enter Meta Description', 'smartcommerce'),
                            'required' => false,
                            'class' => 'sc-form-control',
                        )
                        ),
                    'meta_tag' => array(
                        'type' => 'text',
                        'name' => 'meta_tag',
                        'settings' => array(
                            'value' => isset($this->metadata['meta_tag']) ? $this->metadata['meta_tag'][0] : '',
                            'label' => __('Meta Tags', 'smartcommerce'),
                            'placeholder' => __('Enter Meta Tags', 'smartcommerce'),
                            'required' => false,
                        )
                    ),
                    'meta_keyword' => array(
                        'type' => 'text',
                        'name' => 'meta_keyword',
                        'settings' => array(
                            'value' => isset($this->metadata['meta_keyword']) ? $this->metadata['meta_keyword'][0] : '',
                            'label' => __('Meta Keywords', 'smartcommerce'),
                            'placeholder' => __('Enter Meta Keywords', 'smartcommerce'),
                            'required' => false,
                            'class' => 'sc-form-control',
                        )
                    )
                )
            ),
            'sidebar' => array(
                'title' => __('Other Info', 'smartcommerce'),
                'fields' => array(
                    'main_category' => array(
                        'type' => 'select',
                        'name' => 'main_category',
                        'settings' => array(
                            'value' => $this->metadata['main_category'][0] ?? '',
                            'label' => __('Main Category', 'smartcommerce'),
                            'required' => false,
                            'class' => 'sc-form-control',
                            'options' => $parent_child_cats,
                            'placeholder' => __('Select Main Category', 'smartcommerce'),
                        )
                    ),
                    'tax_category' => array(
                        'type' => 'checkbox',
                        'name' => 'tax_category[]',
                        'settings' => array(
                            'value' => $current_cats,
                            'label' => __('Other Categories', 'smartcommerce'),
                            'required' => false,
                            'class' => 'sc-form-control',
                            'options' => $parent_child_cats,
                        )
                    ),
                    'post_status'=>array(
                        'type' => 'select',
                        'name' => 'post_status',
                        'settings' => array(
                            'value' => $this->post? $this->post->post_status : '',
                            'label' => __('Post Status', 'smartcommerce'),
                            'required' => false,
                            'options' => $this->getStatusList(),
                        )
                    ),
                    'featured' => array(
                        'type' => 'select',
                        'name' => 'featured',
                        'settings' => array(
                            'value' => isset($this->metadata['featured']) ? $this->metadata['featured'][0] : '',
                            'label' => __('Featured Product?', 'smartcommerce'),
                            'required' => false,
                            'options' => array(
                                'yes' => __('Yes', 'smartcommerce'),
                                'no' => __('No', 'smartcommerce'),
                            ),
                        )
                    ),
                    'landing_page_format' => array(
                        'type' => 'select',
                        'name' => 'landing_page_format',
                        'settings' => array(
                            'value' => isset($this->metadata['landing_page_format']) ? $this->metadata['landing_page_format'][0] : '',
                            'label' => __('Landing Page Format', 'smartcommerce'),
                            'required' => false,
                            'options' => array(
                                'format_1' => __('Format 1', 'smartcommerce'),
                                'format_2' => __('Format 2', 'smartcommerce'),
                                'format_3' => __('Format 3', 'smartcommerce'),
                                'format_4' => __('Format 4', 'smartcommerce'),
                                'format_5' => __('Format 5', 'smartcommerce'),
                                'format_6' => __('Format 6', 'smartcommerce'),
                                'format_7' => __('Format 7', 'smartcommerce'),
                                'format_8' => __('Format 8', 'smartcommerce'),
                                'format_9' => __('Format 9', 'smartcommerce'),
                                'format_10' => __('Format 10', 'smartcommerce'),
                            ),
                        )
                    )
                )
            ),
            'hidden' => array(
                'fields' => array(
                    'post_id' => array(
                        'type' => 'hidden',
                        'name' => 'post_id',
                        'settings' => array(
                            'value' => $this->post? $this->post->ID : 0,
                        )
                    ),
                    'post_type' => array(
                        'type' => 'hidden',
                        'name' => 'post_type',
                        'settings' => array(
                            'value' => $this->post_type,
                        )
                    ),
                    'post_author' => array(
                        'type' => 'hidden',
                        'name' => 'post_author',
                        'settings' => array(
                            'value' => $this->post? $this->post->post_author : get_current_user_id(),
                        )
                    ),
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
                            'value' => 'publishPost',
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
                            'value' => "{$action}BeforeSendCallback",
                        )
                    ),
                    'success_callback' => array(
                        'type' => 'hidden',
                        'name' => 'success_callback',
                        'settings' => array(
                            'value' => "{$action}SuccessCallback",
                        )
                    ),
                    'error_callback' => array(
                        'type' => 'hidden',
                        'name' => 'error_callback',
                        'settings' => array(
                            'value' => "{$action}ErrorCallback",
                        )
                    ),
                )
            )

        );
        return $fields;
    }


    /**
     * Publish Post 
     * 
     * @param array $data 
     * @return array 
     */
    public function publish($data = array())
    {
        $id = (int) $data['post_id'] ?? 0;
        $post_data = array();
        if($id) $post_data['ID'] = $id;
        $post_data['post_title'] = $data['post_title'] ?? '';
        $post_data['post_content'] = $data['post_content'] ?? '';
        $post_data['post_excerpt'] = $data['post_excerpt'] ?? '';
        $post_data['post_status'] = $data['post_status'] ?? 'publish';
        $post_data['post_type'] = $this->post_type;
        $post_data['post_author'] = $data['post_author'] ?? get_current_user_id();
        $post_data['post_parent'] = $data['post_parent'] ?? 0;
        if(isset($data['post_name'])) $data['post_name'] = sanitize_title($data['post_name']); 

        $post_id = $id ? wp_update_post($post_data) : wp_insert_post($post_data);
        if(!is_wp_error($post_id)){
            $skip_fields = $this->getSkipFields();
            foreach($data as $key => $value){
                if(in_array($key, $skip_fields) || str_contains($key, 'post_') || str_contains($key,'_callback') || str_contains($key, 'variation_')) continue;
                update_post_meta($post_id, $key, $value);
            }

            // storing variation data in a separate meta
            if(isset($data['variation_name']) && count($data['variation_name']) > 0){
                $vars = array();
                for($i = 0; $i<count($data['variation_name']); $i++){
                    $uid = $data['variation_uid'][$i] ?? '';
                    $vars[$uid] = array(
                        'name' => $data['variation_name'][$i] ?? '',
                        'description' => $data['variation_description'][$i] ?? '',
                        'price' => $data['variation_price'][$i] ?? '',
                        'regular_price' => $data['variation_regular_price'][$i] ?? '',
                        'stock' => $data['variation_stock'][$i] ?? '',
                        'sku' => $data['variation_sku'][$i] ?? '',
                        'images' => $data['variation_images'][$i] ?? '',
                        'size_options' => $data['variation_size_options'][$i] ?? '',
                        'color_options' => $data['variation_color_options'][$i] ?? '',
                        'uid' => $uid,
                        'unit' => $data['variation_unit'][$i] ?? '',
                    );
                }
                update_post_meta($post_id, 'variation_data', $vars);
            } else {
                delete_post_meta($post_id, 'variation_data');
            }

            // updating taxonomy 
            if(isset($data['tax_category'])){
                wp_delete_object_term_relationships($post_id, 'sc_product_category');
                wp_set_post_terms($post_id, $data['tax_category'], 'sc_product_category');
            } else {
                wp_delete_object_term_relationships($post_id, 'sc_product_category');
            }

            // updating main category 
            if(isset($data['main_category'])){
                wp_set_post_terms($post_id, $data['main_category'], 'sc_product_category', true);
                update_post_meta($post_id, 'main_category', $data['main_category']);
            } else {
                delete_post_meta($post_id, 'main_category');
            }

            // updating post thumbnail 
            if(isset($data['post_thumbnail'])){
                $thumbnail_id = $data['post_thumbnail'] ?? 0;
                if($thumbnail_id){
                    set_post_thumbnail($post_id, $thumbnail_id);
                }else{
                    delete_post_thumbnail($post_id);
                }
            }
        }

        $res = [];
        if($id){
            $res['status'] = true;
            $res['message'] = "{$this->post_name} updated successfully";
            $res['post_id'] = $post_id;
        }else {
            if(is_wp_error($post_id)){
                $res['status'] = false;
                $res['message'] = $post_id->get_error_message();
                $res['post_id'] = 0;
            } else {
                $res['status'] = true;
                $res['message'] = "{$this->post_name} published successfully";
                $res['post_id'] = $post_id;
            }
        }
        $res['payload'] = $data;
        $res['post_type'] = $this->post_type;
        return $res;
    }

    /**
     * Get stock units 
     * 
     * @return array 
     * @since 1.0
     * @access public 
     */
    public function getStockUnits()
    {
        $units = Settings::get('stock_units', '');
        $units = explode(',', $units);
        $units = array_map('trim', $units);
        $units = array_filter($units);
        $all_units = array();
        foreach($units as $unit){
            $all_units[$unit] = __($unit, 'smartcommerce');
        }
        return $all_units;
    }
    

    /**
     * Get Publish Form
     * 
     * @return string 
     * @since 1.0.0
     * @access public 
     */
    public function getPublishForm()
    {
        ob_start();
        ?>
        <div class="sc-page-title-wrap">
            <h1 class='sc-page-title'><?php echo $this->post ? 'Edit Product' : 'Add New Product'; ?></h1>
            <?php if($this->post) : ?>
                <a href="<?php echo site_url('/dashboard/?submenu=publish&type=sc_product'); ?>" class="sc-button sc-button-fadeout sc-fright"><?php _e('Add New Product', 'smartcommerce'); ?></a>
            <?php endif; ?>
        </div>
        <form action="" class="sc-form sc-ajax-form">
            <div class="sc-flex-wrap">
                <div class="flex-9">

                    <?php 
                    $fields = $this->getPublishFields();
                    if(!empty($fields)){
                        foreach($fields as $group => $field){
                            if( in_array($group, array('hidden', 'sidebar')) ) continue;
                            ?>
                            <div class="sc-form-group" data-form-group="<?php echo $group; ?>">
                                <div class="sc-form-group-title"><?php echo $field['title']; ?></div>
                                <div class="sc-form-group-fields">
                                    <?php 
                                    foreach($field['fields'] as $field_name => $field_data){
                                        $label = $field_data['settings']['label'] ?? '';
                                        $id = $field_data['settings']['id'] ?? '';
                                        $row_class = $field_data['settings']['row_class'] ?? '';
                                        if(empty($id)) $field_data['settings']['id'] = $field_data['name'];
                                        ?>
                                        <div class="sc-form-row <?php echo $row_class; ?>" data-field="<?php echo $field_name; ?>">
                                            <?php if(!empty($label)) : ?>
                                                <label for="<?php echo $field_data['settings']['id']; ?>"><?php echo $label; ?></label>
                                            <?php endif; ?>
                                            <?php 

                                                if($field_name == 'post_content'){
                                                    wp_editor($field_data['settings']['value'], $field_data['settings']['id'], array(
                                                        'textarea_name' => $field_data['name'],
                                                        'textarea_rows' => 10,
                                                        'editor_class' => 'sc-form-control',
                                                        'quicktags' => false,
                                                        'media_buttons' => false,
                                                        'tinymce' => true,
                                                        'textarea_rows' => 10,
                                                        'editor_class' => 'sc-form-control',
                                                        'editor_height' => 300,
                                                        'editor_width' => '100%',
                                                    ));
                                                }else{
                                                    echo Form::generateElement($field_data['type'], $field_name, $field_data['settings']); 
                                                }
                                                if(isset($field_data['settings']['after_html'])){ echo $field_data['settings']['after_html']; } 
                                            ?>
                                        </div>
                                        <?php 
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php 
                        }
                    }
                    ?>
                </div>
                <div class="flex-3">
                    <?php 
                        $sidebar_fields = $fields['sidebar'] ?? [];
                    ?>
                    <div class="sc-form-group" data-form-group="sidebar">
                        <div class="sc-form-group-fields">
                            <?php
                                if(!empty($sidebar_fields)){
                                    foreach($sidebar_fields['fields'] as $field_name => $field_val){
                                        $field_data = $field_val;
                                        $label = $field_data['settings']['label'] ?? '';
                                        $id = $field_data['settings']['id'] ?? '';
                                        if(empty($id)) $field_data['settings']['id'] = $field_data['name'];
                                        ?>
                                            <div class="sc-form-row" data-field="<?php echo $field_name; ?>">
                                                <label for="<?php echo $field_data['settings']['id']; ?>"><?php echo $label; ?></label>
                                                <?php echo Form::generateElement($field_data['type'], $field_data['name'], $field_data['settings']); ?>
                                                <?php if(isset($field_data['settings']['after_html'])){ echo $field_data['settings']['after_html']; } ?>
                                            </div>
                                        <?php 
                                    }
                                }
                            ?>
                        </div>
                    </div>
                    <?php 
                    $hidden_fields = $fields['hidden'] ?? [];
                    if(!empty($hidden_fields)){
                        foreach($hidden_fields['fields'] as $field_name => $field_val){
                            echo Form::generateElement($field_val['type'], $field_name, $field_val['settings']);
                        }
                    }
                    $btn_label = $this->post ? 'Update' : 'Publish';
                    if($this->id){
                        echo Form::generateElement('html', 'preview',array(
                            'html' => '<a href="'. get_the_permalink($this->id) .'" target="_blank" class="sc-button sc-button-fadeout">'. __('Preview', 'smartcommerce') .'</a>',
                        ));
                    }
                    echo Form::generateElement('button', 'publish', array(
                        'value' => $btn_label,
                        'class' => 'sc-button sc-button-primary sc-fright',
                        'type' => 'submit',
                    ));
                ?>

                </div>
            </div>
        </form>
        <?php 
        return ob_get_clean();
    }


    /**
     * Get Edit Form 
     * 
     * @return string 
     * @since 1.0
     * @access public 
     */
    public function getEditForm()
    {
        return $this->getPublishForm();
    }

    /**
     * Get Categories 
     * 
     * @return array 
     * 
     * @since 1.0
     * @access public 
     */
    public function getCategories()
    {
        $cats = array(
            'electronics' => __('Electronics', 'smartcommerce'),
            'clothing' => __('Clothing', 'smartcommerce'),
            'books' => __('Books', 'smartcommerce'),
            'toys' => __('Toys', 'smartcommerce'),
            'other' => __('Other', 'smartcommerce'),
        );
        return $cats;
    }

    /**
     * Get post title 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function getListFields()
    {
        $fields = array(
            'post_id'   => __('ID', 'smartcommerce'),
            'post_thumbnail' => __('Image', 'smartcommerce'),
            'stock_quantity' => __('Stock', 'smartcommerce'),
            'stock_unit' => __('Unit', 'smartcommerce'),
            'post_title' => __('Title', 'smartcommerce'),
            'price' => __('Price', 'smartcommerce'),
            'post_date' => __('Date', 'smartcommerce'),
            'count_views' => __('Views', 'smartcommerce'),
            'count_orders' => __('Orders', 'smartcommerce'),
            'post_status' => __('Status', 'smartcommerce'),
            'actions' => __('Actions', 'smartcommerce'),
        );
        $fields = apply_filters('smartcommerce_filter_' . $this->post_type . '_list_fields', $fields);
        return $fields;
    }

    /**
     * Get variations 
     * 
     * @return array 
     * @since 1.0
     * @access public 
     */
    public function getVariations()
    {
        $variations = maybe_unserialize(isset($this->metadata['variation_data']) ? $this->metadata['variation_data'][0] : '');
        if(!is_array($variations)) $variations = [];
        return $variations;
    }

    /**
     * Get Variation Meta value 
     * 
     * @param int $variation_id 
     * @param string meta key 
     * @return mixed 
     */
    public function getVariationMetaValue($variation_id, $meta_key)
    {
        $variation_data = $this->getVariations();
        return isset($variation_data[$variation_id]) ? $variation_data[$variation_id][$meta_key]  : '';
    }


    

    /**
     * Get Product Size Options 
     * @return array 
     * 
     * @since 1.0.0
     */
    public function getOptions($meta_key='', $variation_id='')
    {
        if(empty($variation_id)){
            $options = isset($this->metadata[$meta_key]) ? $this->metadata[$meta_key][0] : '';
            $options = explode(',', $options);
            $options = array_map('trim', $options);
            $options = array_filter($options);
        } else {
            $options = $this->getVariationMetaValue($variation_id, $meta_key);
            $options = explode(',', $options);
            $options = array_map('trim', $options);
            $options = array_filter($options);
        }
        if(empty($options)) return [];
        return array_combine($options, $options);
    }

    /**
     * Get Product Size Options 
     * @return array 
     * 
     * @since 1.0.0
     */
    public function getSizeOptions($variation_id='')
    {
        return $this->getOptions('size_options', $variation_id);
    }
    
    /**
     * Get Product Color Options 
     * @return array 
     * 
     * @since 1.0.0
     */
    public function getColorOptions($variation_id='')
    {
        return $this->getOptions('color_options', $variation_id);
    }

    /**
     * Get Product Price 
     * 
     * @return string 
     * 
     * @since 1.0.0
     */
    public function getPrice()
    {
        return (float) isset($this->metadata['selling_price']) ? $this->metadata['selling_price'][0] : 0;
    }

    /**
     * Get discount price 
     * 
     * @return float 
     * 
     * @since 1.0.0
     * @access public 
     */
    public function getDiscount()
    {
        return (float) isset($this->metadata['discount']) ? $this->metadata['discount'][0] : 0;
    }

    /**
     * Get Stock Type 
     * 
     * @return string 
     * 
     * @since 1.0.0
     */
    public function getStockType()
    {
        return isset($this->metadata['stock_type']) ? $this->metadata['stock_type'][0] : 'unlimited';
    }

    /**
     * Get Stock Quantity 
     * 
     * @return int 
     * 
     * @since 1.0.0
     */
    public function getStockQuantity()
    {
        $stock_type = $this->getStockType();
        if($stock_type == 'unlimited') return 9999999;
        if($stock_type == 'limited') return (int) isset($this->metadata['stock_quantity']) ? $this->metadata['stock_quantity'][0] : 0;
        return 0;
    }

    /**
     * Get Product Title 
     * 
     * @return string 
     * 
     * @since 1.0.0
     */
    public function getTitle($variation_id='')
    {
        if(!$variation_id) return $this->post ? $this->post->post_title : '';
        $variation_data = $this->getVariations();
        return isset($variation_data[$variation_id]['name']) ? $variation_data[$variation_id]['name'] : '';
    }

    /**
     * Get Thumbnail Image 
     * 
     * @return string 
     * 
     * @since 1.0.0
     */
    public function getThumbnail($variation_id='', $size='sc-thumbnail', $attr=[])
    {
        if(!($variation_id)) return has_post_thumbnail($this->id) ? get_post_thumbnail_id($this->id) : SMART_COMMERCE_PRODUCT_THUMBNAIL;
        $images = $this->getVariationMetaValue($variation_id, 'images');
        $images = explode(',', $images);
        $images = array_map('trim', $images);
        $images = array_filter($images);
        if(!empty($images)) return reset($images);
        return get_post_thumbnail_id($this->id);
    }

    /**
     * Get Images 
     * @return array 
     * @since 1.0.0
     * @access public 
     */
    public function getImages($variation_id='')
    {
        if(!($variation_id)){
            $images = isset($this->metadata['extra_images']) ? $this->metadata['extra_images'][0] : '';
        } else{
            $images = $this->getVariationMetaValue($variation_id, 'variation_images');
        }
        $images = explode(',', $images);
        $thumbnail = $this->getThumbnail();
        if($thumbnail) array_unshift($images, $thumbnail);
        $images = array_map('intval', $images);
        $images = array_filter(array_unique($images));
        return $images;
    }

    /**
     * Get Image Gallery 
     * 
     * @return string
     * @since 1.0.0
     * @access public
     */
    public function getImageGallery($size='full')
    {
        $images = $this->getImages();
        if(empty($images)) return '';
        ob_start();
        $img_1 = $images[0];
        $img_url = wp_get_attachment_url($img_1, $size);
        ?>
        <style>
            .sc-product-img-list{
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                margin: 0;
                padding: 0;
            }
            .sc-product-img-list li{
                list-style: none;
            }
            .sc-product-img-highlighted{
                width: 100%;
                height: 100%;
                min-height: 400px;
                background-size: contain;
                background-position: center;
                background-repeat: no-repeat;
                margin-bottom: 10px;
            }
            .sc-product-img-highlighted:hover{
                transform: scale(1.50);
                cursor: pointer;
                transition: all 0.3s ease;
            }
            a.sc-product-img-list-item-link{
                display: inline-block;
                line-height: 0;
                box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.1);
            }
            a.sc-product-img-list-item-link img{
                width: 50px;
                height: 50px;
            }
        </style>
        <script>
            jQuery(document).ready(function($){
                $('.sc-product-img-list-item-link').click(function(){
                    var url = $(this).data('url');
                    $('.sc-product-img-highlighted').css('background-image', 'url(' + url + ')');
                });
            });
        </script>
        <div class="sc-product-img-gallery">
            <div class="sc-product-img-highlighted" style="background-image: url(<?php echo $img_url; ?>);"></div>
            
            <?php if(count($images) > 1){ ?>
            <ul class="sc-product-img-list">
                <?php foreach($images as $image){ ?>
                    <li class="sc-product-img-list-item">
                        <a href="javasript:void(0);" class="sc-product-img-list-item-link" data-url="<?php echo wp_get_attachment_url($image,$size); ?>" data-fancybox="gallery">
                            <?php echo wp_get_attachment_image($image, 'sc-thumbnail'); ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
            <?php } ?>
        </div>
        <?php 
        return ob_get_clean();
    }

    /**
     * Get SKU 
     * @return string
     * @since 1.0.0
     * @access public
     */
    public function getSku()
    {
        return isset($this->metadata['sku']) ? $this->metadata['sku'][0] : '';
    }


    /**
     * Create Facebook Feed
     * @return void 
     * @since 1.0.0
     * @access public 
     */
    public function renderFacebookFeed()
    {
        header( 'Content-Type: application/xml; charset=' . get_option( 'blog_charset' ), true );

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        ?>
        <rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
            <channel>
                <title><?php bloginfo( 'name' ); ?> - Facebook Product Feed</title>
                <link><?php bloginfo( 'url' ); ?></link>
                <description>Product feed for Facebook Catalog</description>
    
                <?php
                $args = [
                    'post_type'      => 'sc_product',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                ];
                $category_id = isset($_GET['category_id']) ? $_GET['category_id'] : 0;
                if($category_id){
                    $args['tax_query'] = [
                        [
                            'taxonomy' => 'sc_product_category',
                            'field' => 'term_id',
                            'terms' => $category_id,
                        ],
                    ];
                }
                $products = new \WP_Query( $args );

                $currency = 'BDT';

                if ( $products->have_posts() ) :
                    while ( $products->have_posts() ) : $products->the_post();
                        $brand = '';
                        $brand_id = get_post_meta( get_the_ID(), 'brand', true );
                        if($brand_id) $brand = get_the_title($brand_id);
                        if(empty($brand)) $brand = 'N/A';
                        $post_id   = get_the_ID();
                        $title     = get_the_title();
                        $link      = get_permalink();
                        $desc      = wp_strip_all_tags( get_the_excerpt(), true );
                        $price     = get_post_meta( $post_id, 'regular_price', true ); // Adjust meta key to yours
                        $sale_price = get_post_meta( $post_id, 'selling_price', true );
                        $condition = 'new'; // or dynamic if you store it
                        $sku       = get_post_meta( get_the_ID(), 'sku', true );
                        $image     = get_the_post_thumbnail_url( $post_id, 'full' );
                        $google_product_category = get_post_meta( $post_id, 'google_product_category', true );
                        $facebook_product_category = get_post_meta( $post_id, 'facebook_product_category', true );
                        if(empty($google_product_category)) $google_product_category = Settings::get('google_product_category');
                        if(empty($facebook_product_category)) $facebook_product_category = Settings::get('facebook_product_category');
                        ?>
                        <item>
                            <g:id><?php echo $post_id; ?></g:id>
                            <title><![CDATA[<?php echo $title; ?>]]></title>
                            <description><![CDATA[<?php echo $desc; ?>]]></description>
                            <link><?php echo esc_url( $link ); ?></link>
                            <?php if ( $image ) : ?>
                                <g:image_link><?php echo esc_url( $image ); ?></g:image_link>
                            <?php endif; ?>
                            <g:condition><?php echo $condition; ?></g:condition>
                            <g:availability>in stock</g:availability>
                            <?php if ( $price ) : ?>
                                <g:price><?php echo esc_html( $price ); ?> <?php echo $currency; ?></g:price>
                            <?php endif; ?>
                            <?php if ( $sale_price ) : ?>
                                <g:sale_price><?php echo esc_html( $sale_price ); ?> <?php echo $currency; ?></g:sale_price>
                            <?php endif; ?>
                            <?php if ( $brand ) : ?>
                                <g:brand><![CDATA[<?php echo $brand; ?>]]></g:brand>
                            <?php endif; ?>
                            <?php if ( $google_product_category ) : ?>
                                <g:google_product_category><![CDATA[<?php echo $google_product_category; ?>]]></g:google_product_category>
                            <?php endif; ?>
                            <?php if ( $facebook_product_category ) : ?>
                                <g:fb_product_category><![CDATA[<?php echo $facebook_product_category; ?>]]></g:fb_product_category>
                            <?php endif; ?>
                            <?php echo '<g:identifier_exists>false</g:identifier_exists>'; ?>
                        </item>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </channel>
        </rss>
        <?php
    
    }

    /**
     * Get Text To Share on Whatsapp or messenger 
     * 
     * @return string
     * @since 1.0.0
     * @access public
     */
    public static function getTextToShare($id=0)
    {
        $id = $id ? $id : get_the_ID();
        $shop_name = Settings::get('shop_name');
        $product_name = get_the_title($id);
        $current_url = get_permalink($id);
        $text = "হ্যালো {$shop_name},\nআপনাদের ওয়েবসাইটের এই পণ্যটি [ID#{$id}] {$product_name} সম্পর্কে আমি জানতে চাচ্ছি - \n\n $current_url";
        return $text;
    }

    /**
     * 
     * Whatsapp share link 
     * @return string
     * @since 1.0.0
     * @access public
     */
    public static function getWhatsappShareLink($id = 0)
    {
        if(!$id) $id = get_the_ID();
        $whatsapp_number = Settings::get('whatsapp_number');
        if(!str_contains($whatsapp_number, '+88')){
            $whatsapp_number = '+88' . $whatsapp_number;
        }
        $whatsapp_link = '';
        if(!empty($whatsapp_number)){
            $whatsapp_link = 'https://wa.me/' . $whatsapp_number;
            $whatsapp_link .= '?text=' . urlencode(self::getTextToShare($id));
        }
        return $whatsapp_link;
    }

    /**
     * Get Messenger Share Link 
     * @return string
     * @since 1.0.0
     * @access public
     */
    public static function getMessengerShareLink($id = 0)
    {
        $messenger_username = Settings::get('messenger_username');
        $messenger_link = '';
        if(!empty($messenger_username)){
            $messenger_link = 'https://m.me/' . $messenger_username;
            $messenger_link .= '?text=' . urlencode(self::getTextToShare($id));
        }
        return $messenger_link;
    }

    /**
     * GEt Cateogry 
     * 
     * @return object
     * @since 1.0.0
     * @access public
     */
    public function getCategoryList()
    {
        $list = [];
        $feautred_category = get_post_meta($this->id, 'featured_category', true);
        if($feautred_category){
            $list[] = get_term($feautred_category, 'sc_product_category');
        }
        $category = get_the_terms($this->id, 'sc_product_category');
        if(is_array($category) && !empty($category)){
            $list = array_merge($list, $category);
        }
        if(empty($list)) return '';
        ob_start();
        ?>
        <span class="product-cateogry-list">
            <?php foreach($list as $category){ ?>
                <a data-term-id="<?php echo $category->term_id; ?>" class='sc-product-tag' href="<?php echo get_term_link($category); ?>"><?php echo $category->name; ?></a>
            <?php } ?>
        </span>
        <?php
        return ob_get_clean();
    }

    /**
     * Control Page Title 
     * 
     * @return void 
     * @since 1.0.0
     * @access public 
     */
    public function controlPageTitle($title)
    {
        global $post;
        if(!is_singular($this->post_type)) return $title;
        $shop_name = Settings::get('shop_name');
        $meta_title = get_post_meta($post->ID, 'meta_title', true);
        $title['title'] = !empty($meta_title) ? $meta_title : $post->post_title;
        return $title;
    }

    /**
     * Get Similar Products 
     * 
     * @return Object
     * @since 1.0.0
     * @access public
     */
    public static function getSimilarProducts($id=0)
    {
        if(!$id){
            global $post;
            $id = $post->ID;
        }
        $categories = get_the_terms($id, 'sc_product_category');
        if(is_array($categories) && !empty($categories)){
            $term_ids = [];
            foreach($categories as $category){
                $term_ids[] = $category->term_id;
            }
            $qry = new \WP_Query(array(
                'post_type' => 'sc_product',
                'post_status' => 'publish',
                'posts_per_page' => 9,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'sc_product_category',
                        'field' => 'term_id',
                        'terms' => $term_ids,
                    ),
                ),
            ));
            if($qry->have_posts()) return $qry;
        }
        $qry = new \WP_Query(
            array(
                'post_type' => 'sc_product',
                'post_status' => 'publish',
                'posts_per_page' => 9,
                'orderby' => 'ID',
                'order' => 'DESC',
            )
        );
        return $qry;
    }

    /**
     * Show Similar Products
     * 
     * @return void
     * @since 1.0.0
     * @access public
     */
    public static function showSimilarProducts($id = 0, $title = '', $ajax = false)
    {
        ob_start();
        ?>
        <?php if($ajax): ?>
            <script>
                jQuery(document).ready(function($){
                    // response is object, we need to show response.data.data 
                    let data = `<?php echo admin_url('admin-ajax.php'); ?>?action=smartcommerce_ajax&ajax_action=showSimilarProducts&_wpnonce=${smartcommerce._wpnonce}&id=<?php echo $id; ?>`;
                    console.log(data);
                    $('#ajaxSimilarProducts').load(data);
                });
            </script>
            <div id="ajaxSimilarProducts"></div>
            <?php return ob_get_clean(); ?>
        <?php endif; ?>

        <?php 

        $qry = Product::getSimilarProducts($id);
        if(empty($title)) $title = __('Similar Products picked for you', 'smartcommerce');
        if($qry->have_posts()){
            ?>
            <section> 
                <h3 class="section-title"><?php _e($title, 'smartcommerce'); ?></h3>
                <div class="product-loop template-default sc-similar-products">
                    <?php while($qry->have_posts()){
                        $qry->the_post();
                        include SMART_COMMERCE_THEME_DIR . 'templates/product-loop/loop-default.php';
                    } ?>
                </div>
            </section>
            <?php 
            wp_reset_postdata();
        }
        return ob_get_clean();

    }

    /**
     * Get total orders of this product 
     * 
     * @return int
     * @since 1.0.0
     * @access public
     */
    public static function countTotalOrders($id=0, $date='')
    {
        $args = array(
            'post_type' => 'sc_order',
            'post_status' => 'publish',
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'product_id',
                    'value' => $id,
                ),
            ),
        );
        if(!empty($date)){
            $date = new \DateTime($date);
            $args['date_query'] = array(
                array(
                    'year' => $date->format('Y'),
                    'month' => $date->format('m'),
                    'day' => $date->format('d'),
                ),
            );
        }
        $qry = new \WP_Query($args);
        return (int) $qry->found_posts;
    }

    public static function getDeliveryChargeOptions($id=0)
    {
        if(!$id) $id = get_the_ID();
        $delivery_charges = get_post_meta($id, 'delivery_charges', true);
        if(empty($delivery_charges)) $delivery_charges = Settings::get('delivery_charges');
        $delivery_charges = explode("\r\n", $delivery_charges);
        $delivery_charges = array_map('trim', $delivery_charges);
        $choices = [];
        if(!empty($delivery_charges)){
            foreach($delivery_charges as $charge){
                $charge_parts = explode(':', $charge);
                $val = $charge_parts[0] . ' ( '. trim(Settings::get('currency_symbol')) . trim($charge_parts[1]) . ')';
                $choices[$charge] = $val;
            }
        }
        return $choices;
    }
    
}

$product = Product::instance();
$product->wpInit();