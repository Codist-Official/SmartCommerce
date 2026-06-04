<?php 
namespace SmartCommerce;

class ProductCategory {

     /**
     * Instance 
     */
    private static $_instance;

    /**
     * $id 
     */
    public $id;

    /**
     * Tax Type 
     */
    public $taxonomy = 'sc_product_category';

    public $taxonomy_name = 'Product Category';

    public $taxonomy_slug = 'sc-product-category';

    public $term;

    /**
     * Constructor 
     * 
     * @return void 
     * @since 1.0.0
     */
    public function __construct( $id = 0 ) 
    {
        $this->id = $id;
        if($this->id) $this->term = get_term($this->id, $this->taxonomy);
        add_action('init', [$this, 'registerTaxonomy']);
    }

    /**
     * Register Taxonomy
     * 
     * @return void 
     * @since 1.0.0
     */
    public function registerTaxonomy()
    {
        register_taxonomy( $this->taxonomy, 'sc_product', [
            'labels' => [
                'name' => 'Product Categories',
                'singular_name' => 'Product Category',
            ],
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'sc-product-category'],
        ]);
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
     * Get All Categories in an arary 
     * 
     * @return array 
     * @since 1.0.0
     */
    public static function getAll( $parent = 0 )
    {
        $args = [
            'taxonomy' => self::$_instance->taxonomy,
            'hide_empty' => false,
        ];
        if($parent != 'all') $args['parent'] = $parent;
        return get_terms($args);
    }

    /**
     * Count Total Terms 
     * 
     * @return int 
     * @since 1.0.0
     */
    public static function countTotal()
    {
        return count(self::getAll());
    }

    /**
     * Get Publish Fields 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function getPublishFields()
    {
        $img = get_term_meta($this->term->term_id, 'images', true);
        $featured_image = get_term_meta($this->term->term_id, 'featured_image', true);
        $all_cats = self::getAll();
        $cats = [];
        if(!empty($all_cats)){
            foreach($all_cats as $cat){
                $cats[$cat->term_id] = $cat->name;
            }
        }
        $fields = [
            'name' => [
                'type' => 'text',
                'name' => 'name',
                'settings' => [
                    'label' => __('Name', 'smartcommerce'),
                    'required' => true,
                    'placeholder' => __('Enter ' . $this->taxonomy_name . ' name', 'smartcommerce'),
                    'value' => $this->term->name ?? '',
                ],
            ],
            
            'featured_image' => [
                'type' => 'ajax_file',
                'name' => 'featured_image',
                'settings' => [
                    'label' => __('Featured Image', 'smartcommerce'),
                    'required' => false,
                    'value' => $featured_image,
                    'accept' => 'image/*',
                    'preview' => true,
                ],
            ],
            'images' => [
                'type' => 'ajax_file',
                'name' => 'images',
                'settings' => [
                    'label' => __('Slider Images', 'smartcommerce'),
                    'required' => false,
                    'value' => $img,
                    'accept' => 'image/*',
                    'preview' => true,
                    'multiple' => true,
                ],
            ],
            'description' => [
                'type' => 'textarea',
                'name' => 'description',
                'settings' => [
                    'label' => __('Description', 'smartcommerce'),
                    'required' => false,
                    'placeholder' => __('Enter ' . $this->taxonomy_name . ' description', 'smartcommerce'),
                    'value' => $this->term->description ?? '',
                ],
            ],
            'featured' => [
                'type' => 'radio',
                'name' => 'featured',
                'settings' => [
                    'label' => __('Featured', 'smartcommerce'),
                    'options' => [
                        'yes' => __('Yes', 'smartcommerce'),
                        'no' => __('No', 'smartcommerce'),
                    ],
                    'value' => get_term_meta($this->term->term_id, 'featured', true),
                ],
            ],
            'parent' => [
                'type'  => 'select',
                'name' => 'parent',
                'settings' => [
                    'options' => $cats,
                    'label' => __('Parent', 'smartcommerce'),
                    'placeholder' => __('Select Parent ' . $this->taxonomy_name, 'smartcommerce'),
                    'value' => $this->term->parent ?? 0,
                ]
            ],
            
            'term_id' => [
                'type' => 'hidden',
                'name' => 'term_id',
                'settings' => [
                    'required' => false,
                    'value' => $this->id,
                ],
            ],
            'taxonomy' => [
                'type' => 'hidden',
                'name' => 'taxonomy',
                'settings' => [
                    'required' => false,
                    'value' => $this->taxonomy,
                ],
            ],
            'submit' => [
                'type' => 'button',
                'name' => 'save',
                'settings' => [
                    'class' => 'sc-button sc-button-primary',
                    'value' => __( $this->term ? 'Update' : 'Publish', 'smartcommerce'),
                    'type' => 'submit',
                ],
            ],
            'action' => [
                'type' => 'hidden',
                'name' => 'action',
                'settings' => [
                    'required' => false,
                    'value' => 'smartcommerce_ajax',
                ],
            ],
            '_wpnonce' => [
                'type' => 'hidden',
                'name' => '_wpnonce',
                'settings' => [
                    'required' => false,
                    'value' => wp_create_nonce('smartcommerce'),
                ],
            ],
            'ajax_action' => [
                'type' => 'hidden',
                'name' => 'ajax_action',
                'settings' => [
                    'required' => false,
                    'value' => $this->term->term_id ? 'editTaxonomy' : 'publishTaxonomy',
                ],
            ]
        ];
        return $fields;
    }

    /**
     * Save Product Category 
     * 
     * @return void 
     * @since 1.0.0
     */
    public function getPublishForm()
    {
        $fields = $this->getPublishFields();
        if(empty($fields)) return;
        $hidden_fields = [];
        ob_start();
        ?>
        <form action="" method="post" class="sc-form sc-ajax-form">
            <?php foreach($fields as $field): ?>
                <?php 
                    if($field['type'] == 'hidden'):
                        $hidden_fields[] = $field;
                        continue;
                    endif;
                ?>
                <div class="sc-form-row">
                    <?php $label = $field['settings']['label'] ?? ''; ?>
                    <?php if(!empty($label)): ?>
                        <label for="<?php echo esc_attr($field['name']); ?>"><?php echo esc_html($label); ?></label>
                    <?php endif; ?>
                    <?php echo Form::generateElement($field['type'], $field['name'], $field['settings']); ?>
                </div>
            <?php endforeach; ?>
            <?php foreach($hidden_fields as $field): ?>
                <?php echo Form::generateElement($field['type'], $field['name'], $field['settings']); ?>
            <?php endforeach; ?>
        </form>
        <?php
        return ob_get_clean();
    }


    /**
     * Get Edit Form 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function getEditForm()
    {
        return $this->getPublishForm();
    }

    /**
     * Get publish button 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function getPublishButton()
    {
        $url = site_url('/dashboard/?submenu=publish&type=sc_product_category');
        return '<a href="'.$url.'" class="sc-button sc-button-primary sc-fright sc-mb-20">' . __('Add New ' . $this->taxonomy_name, 'smartcommerce') . '</a>';
    }

    /**
     * Get List of Product Category 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function getList()
    {
        $categories = self::getAll();
        if(is_wp_error($categories)) return $this->getPublishButton();
        ob_start();
        echo $this->getPublishButton();
        ?>
        <div class="sc-table-wrap">
            <table class="sc-table sc-tax-list" data-tax-type="<?php echo $this->taxonomy; ?>">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'smartcommerce'); ?></th>
                        <th><?php _e('Images', 'smartcommerce'); ?></th>
                        <th><?php _e('Name', 'smartcommerce'); ?></th>
                        <th><?php _e('Products', 'smartcommerce'); ?></th>
                        <th><?php _e('Featured', 'smartcommerce'); ?></th>
                        <th><?php _e('Feed', 'smartcommerce'); ?></th>
                        <th><?php _e('Actions', 'smartcommerce'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($categories as $cat): ?>
                        <?php 
                            $term_url = get_term_link($cat->term_id, $this->taxonomy); 
                            $image_id = get_term_meta($cat->term_id, 'images', true);
                            $image = $image_id ? wp_get_attachment_image($image_id, [75,75]) : "<img src='". SMART_COMMERCE_IMG_URL . "/image-placeholder.png' alt='". $cat->name ."' width='auto' height='50' style='width: auto; height: 50px;'>";
                        ?>
                        <tr data-id="<?php echo $cat->term_id; ?>">
                            <td>#<?php echo $cat->term_id; ?></td>
                            <td><?php echo $image; ?></td>
                            <td><?php echo $cat->name; ?></td>
                            <td><?php echo $this->countPosts($cat->term_id); ?></td>
                            <td><?php echo get_term_meta($cat->term_id, 'featured', true) == 'yes' ? __('Yes', 'smartcommerce') : __('No', 'smartcommerce'); ?></td>
                            <td><a href="<?php echo site_url('/?feed=facebook-sc-products&category_id='.$cat->term_id); ?>" target="_blank" class="sc-button sc-button-primary"><?php _e('Feed', 'smartcommerce'); ?></a></td>
                            <td>
                                <a target="_blank" href="<?php echo $term_url; ?>" class="sc-icon-link" data-tax-type="<?php echo $this->taxonomy; ?>" data-success_callback="editTaxonomySuccessCallback" data-before_send_callback="scConfirm" data-ajax_action="editTaxonomy" data-term_id="<?php echo $cat->term_id; ?>">
                                    <?php echo SmartCommerce::getIcon('view'); ?>
                                </a>
                                <a href="<?php echo site_url('/dashboard/?submenu=edit&type=sc_product_category&id='.$cat->term_id); ?>" class="sc-icon-link" data-tax-type="<?php echo $this->taxonomy; ?>" data-success_callback="editTaxonomySuccessCallback" data-before_send_callback="scConfirm" data-ajax_action="editTaxonomy" data-term_id="<?php echo $cat->term_id; ?>">
                                    <?php echo SmartCommerce::getIcon('edit'); ?>
                                </a>
                                <a href="javascript:void(0)" class="sc-icon-link sc-ajax-link" data-taxonomy="<?php echo $this->taxonomy; ?>" data-success_callback="deleteTaxonomySuccessCallback" data-before_send_callback="scConfirm" data-ajax_action="deleteTaxonomy" data-term_id="<?php echo $cat->term_id; ?>">
                                    <?php echo SmartCommerce::getIcon('delete'); ?>
                                </a>
                            </td>
                        </tr>
                        <?php 
                            $children = self::getAll($cat->term_id);
                            if(empty($children)) continue;
                            foreach($children as $cat){
                                $term_url = get_term_link($cat->term_id, $this->taxonomy); 
                                $image_id = get_term_meta($cat->term_id, 'image', true);
                                $image = $image_id ? wp_get_attachment_image($image_id, [75,75]) : "<img src='". SMART_COMMERCE_IMG_URL . "/image-placeholder.png' alt='". $cat->name ."' width='auto' height='50' style='width: auto; height: 50px;'>";
                            ?>
                            <tr data-id="<?php echo $cat->term_id; ?>">
                                <td>#<?php echo $cat->term_id; ?></td>
                                <td><?php echo $image; ?></td>
                                <td>&nbsp; &nbsp; &nbsp; &nbsp; --- <?php echo $cat->name; ?></td>
                                <td><?php echo $this->countPosts($cat->term_id); ?></td>
                                <td><?php echo get_term_meta($cat->term_id, 'featured', true) == 'yes' ? __('Yes', 'smartcommerce') : __('No', 'smartcommerce'); ?></td>
                                <td><a href="<?php echo site_url('/?feed=facebook-sc-products&category_id='.$cat->term_id); ?>" target="_blank" class="sc-button sc-button-primary"><?php _e('Feed', 'smartcommerce'); ?></a></td>
                                <td>
                                    <a target="_blank" href="<?php echo $term_url; ?>" class="sc-icon-link" data-tax-type="<?php echo $this->taxonomy; ?>" data-success_callback="editTaxonomySuccessCallback" data-before_send_callback="scConfirm" data-ajax_action="editTaxonomy" data-term_id="<?php echo $cat->term_id; ?>">
                                        <?php echo SmartCommerce::getIcon('view'); ?>
                                    </a>
                                    <a href="<?php echo site_url('/dashboard/?submenu=edit&type=sc_product_category&id='.$cat->term_id); ?>" class="sc-icon-link" data-tax-type="<?php echo $this->taxonomy; ?>" data-success_callback="editTaxonomySuccessCallback" data-before_send_callback="scConfirm" data-ajax_action="editTaxonomy" data-term_id="<?php echo $cat->term_id; ?>">
                                        <?php echo SmartCommerce::getIcon('edit'); ?>
                                    </a>
                                    <a href="javascript:void(0)" class="sc-icon-link sc-ajax-link" data-taxonomy="<?php echo $this->taxonomy; ?>" data-success_callback="deleteTaxonomySuccessCallback" data-before_send_callback="scConfirm" data-ajax_action="deleteTaxonomy" data-term_id="<?php echo $cat->term_id; ?>">
                                        <?php echo SmartCommerce::getIcon('delete'); ?>
                                    </a>
                                </td>
                            </tr>
                            <?php
                            }
                        ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php return ob_get_clean();
    }

    /**
     * Publish Product Category 
     * 
     * @return void 
     * @since 1.0.0
     */
    public function publish( $data = [] )
    {
        $args = array(
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? '',
            'parent' => $data['parent'] ?? 0,
        );
        $result = wp_insert_term($args['name'], $this->taxonomy, $args);
        if(is_wp_error($result)) return array(
            'status' => 0,
            'message' => $result->get_error_message(),
            'payload' => $data,
        );
        if(isset($data['images'])){
            $image_id = $data['images'] ?? 0;
            if($image_id){
                update_term_meta($result['term_id'], 'images', $image_id);
            }else{
                delete_term_meta($result['term_id'], 'images');
            }
        }
        if(isset($data['featured'])){
            update_term_meta($result['term_id'], 'featured', $data['featured']);
        }
        if(isset($data['featured_image'])){
            $image_id = (int) $data['featured_image'] ?? 0;
            if($image_id){
                update_term_meta($result['term_id'], 'featured_image', $image_id);
            }else{
                delete_term_meta($result['term_id'], 'featured_image');
            }
        }
        return array(
            'status' => 1,
            'message' => __( $this->taxonomy_name . ' updated successfully', 'smartcommerce' ),
            'payload' => $result,
        );
    }

    /**
     * Edit Taxonomy 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function edit( $data = [] )
    {
        $args = array(
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? '',
            'parent' => $data['parent'] ?? 0,
        );
        $result = wp_update_term($this->id, $this->taxonomy, $args);
        if(is_wp_error($result)) return array(
            'status' => 0,
            'message' => $result->get_error_message(),
            'payload' => $data,
        );
        if(isset($data['images'])){
            $image_id = $data['images'] ?? 0;
            if($image_id){
                update_term_meta($this->id, 'images', $image_id);
            }else{
                delete_term_meta($this->id, 'images');
            }
        }
        if(isset($data['featured'])){
            update_term_meta($this->id, 'featured', $data['featured']);
        }
        if(isset($data['featured_image'])){
            $image_id = (int) $data['featured_image'] ?? 0;
            if($image_id){
                update_term_meta($this->id, 'featured_image', $image_id);
            }else{
                delete_term_meta($this->id, 'featured_image');
            }
        }
        return array(
            'status' => 1,
            'message' => __( $this->taxonomy_name . ' updated successfully', 'smartcommerce' ),
            'payload' => $result,
        );
    }

    /**
     * Delete Product Category 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function delete()
    {
        $result = wp_delete_term($this->id, $this->taxonomy);
        if(is_wp_error($result)) return array(
            'status' => 0,
            'message' => $result->get_error_message(),
            'payload' => $this->id,
        );
        return array(
            'status' => 1,
            'message' => __( $this->taxonomy_name . ' deleted successfully', 'smartcommerce' ),
            'payload' => $this->id,
        );
    }

    /**
     * Count posts under a term 
     * 
     * @return int 
     * @since 1.0.0
     */
    public function countPosts($term_id = 0, $post_type = 'sc_product')
    {
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'fields' => 'ids',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => $this->taxonomy,
                    'field' => 'term_id',
                    'terms' => $term_id,
                )
            )
        );
        $query = new \WP_Query($args);
        return $query->found_posts;
    }
}

ProductCategory::instance();