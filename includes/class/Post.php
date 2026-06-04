<?php 
namespace SmartCommerce;

class Post {

    /**
     * Instance 
     */
    private static $_instance;

    public $post_type;
    public $post_slug;
    public $post_name;
    public $id; 
    public $post;
    public $metadata;
    public $posts_per_page = 50;

    /**
     * Constructor 
     * 
     * @return void 
     * @since 1.0.0
     */
    public function __construct( $id = 0) 
    {
        // register post type 
        add_action('init', [$this, 'registerPostType']);

        if(is_numeric($id) && $id > 0){
            $this->id = $id;
            $this->post = get_post($id);
            if($this->post){
                $this->metadata = get_metadata('post', $this->id);
            }
        } else if ($id instanceof \WP_Post){
            $this->id = $id->ID;
            $this->post = $id;
            $this->metadata = get_metadata('post', $this->id);
        }
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
     * WP Init 
     * @return void
     * @since 1.0.0
     */
    public function wpInit()
    {

        // increment post view 
        add_action('wp_head', [$this, 'incrementPostView']);

        // register custom post status 
        add_action('init', [$this, 'registerCustomPostStatus']);
    }

    /**
     * Register post type 
     * 
     * @return void 
     * @since 1.0.0
     */
    public function registerPostType()
    {
        if(empty($this->post_type)) return;
        $args = [
            'labels' => [
                'name' => __($this->post_name, 'smartcommerce'),
                'singular_name' => __($this->post_name, 'smartcommerce'),
                'menu_name' => __($this->post_name, 'smartcommerce'),
                'add_new' => __('Add New', 'smartcommerce'),
                'add_new_item' => __('Add New', 'smartcommerce'),
                'edit_item' => __('Edit', 'smartcommerce'),
                'new_item' => __('New', 'smartcommerce'),
                'view_item' => __('View', 'smartcommerce'),
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => $this->post_slug],
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments', 'revisions', 'custom-fields', 'page-attributes', 'post-formats'],
            'menu_icon' => 'dashicons-cart',
            'menu_position' => 5,
        ];
        $args = apply_filters('smartcommerce_filter_' . $this->post_type . '_register_args', $args);
        register_post_type($this->post_type, $args);
    }

    /**
     * Register custom post status 
     * 
     * @return void 
     * @since 1.0.0
     */
    public function registerCustomPostStatus()
    {
        register_post_status('unlisted', [
            'label' => __('Unlisted', 'smartcommerce'),
            'public' => false,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
        ]);
    }

    /**
     * Get post type 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function getPublishButton()
    {
        $html = "<a data-action='publish' data-post_type='{$this->post_type}' href='". site_url("/dashboard/?submenu=publish&type={$this->post_type}") ."' class='sc-button sc-button-primary sc-fright sc-mb-20'>". __("Add New", 'smartcommerce') ."</a>";
        return apply_filters('smartcommerce_filter_' . $this->post_type . '_publish_button_html', $html);
    }

    /**
     * Get Action Bar 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function getActionBar()
    {
        ob_start();
        ?>
        <div class="sc-action-bar" data-post_type='<?php echo $this->post_type; ?>'>
            <?php echo $this->getBulkActions(); ?>
            <!-- Publish Button -->
            <?php echo $this->getPublishButton(); ?>
        </div>
        <?php
        $html = ob_get_clean();
        return apply_filters('smartcommerce_filter_' . $this->post_type . '_action_bar_html', $html);
    }

    /**
     * Get Bulk Actions 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function getBulkActions()
    {
        ob_start();
        ?>
        <div class="sc-bulk-actions" data-post_type='<?php echo $this->post_type; ?>'>
            <?php
                echo Form::generateElement('select', 'bulk_action', array(
                    'options' => array(
                        '' => __('Select Bulk Action', 'smartcommerce'),
                        'bulkDelete' => __('Delete', 'smartcommerce'),
                    ),
                    'class' => 'sc-form-control',
                    'style' => 'width: 150px; margin-right: 10px;',
                ));
                echo Form::generateElement('button', 'bulkActionApply', array(
                    'value' => __('Apply', 'smartcommerce'),
                    'class' => 'sc-button sc-button-primary sc-mb-20 bulkActionApply',
                    'data-action' => 'bulk_action',
                    'data-target' => 'id[]',
                    'type' => 'button',
                    'data'=>array(
                        'data-post_type' => $this->post_type,
                    )
                ));
                $url = site_url('/?feed=facebook-sc-products');
                echo "<a href='{$url}' target='_blank' class='sc-button sc-button-primary'>". __('Feed', 'smartcommerce') ."</a>";
            ?>
        </div>
        <?php 
        $html = ob_get_clean();
        return apply_filters('smartcommerce_filter_' . $this->post_type . '_bulk_actions_html', $html);
    }

    /**
     * Get post title 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function getListFields()
    {
        $fields = array(
            'post_id'   => __('ID', 'smartcommerce'),
            'post_thumbnail' => __('Image', 'smartcommerce'),
            'post_title' => __('Title', 'smartcommerce'),
            'post_status' => __('Status', 'smartcommerce'),
            'actions' => __('Actions', 'smartcommerce'),
        );
        $fields = apply_filters('smartcommerce_filter_' . $this->post_type . '_list_fields', $fields);
        return $fields;
    }

    /**
     * Get posts 
     * 
     * @param arary $conds 
     * @return array 
     * @since 1.0.0
     */
    public function getPosts($conds = array())
    {
        $cat = isset($_REQUEST['search_cat']) ? intval($_REQUEST['search_cat']) : 0;
        $search = isset($_REQUEST['search']) ? sanitize_text_field($_REQUEST['search']) : '';
        $mobile = (string) isset($_REQUEST['mobile']) ? $_REQUEST['mobile'] : 0;
        $args =  array();
        $args['post_type'] = $this->post_type;
        if(!empty($search)){
            if(is_numeric($search) || str_contains($search, ',')){
                $args['post__in'] = explode(',', $search);
            } else {
                if($this->post_type == 'sc_order'){
                    $args['meta_query'] = array(
                        array(
                            'key' => 'delivery_name',
                            'value' => $search,
                            'compare' => 'LIKE',
                        ),
                    );
                } else {
                    $args['s'] = $search;
                }
            }
        }
        
        if($cat){
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'sc_product_category',
                    'field' => 'term_id',
                    'terms' => $cat,
                ),
            );
        }
        if($mobile){
            $args['meta_query'] = array(
                array(
                    'key' => 'delivery_mobile',
                    'value' => $mobile,
                    'compare' => '=',
                ),
            );
        }
        $args['post_status'] = isset($conds['post_status']) ? $conds['post_status'] : sanitize_text_field($_GET['post_status'] ?? 'any');
        $args['posts_per_page'] = isset($_REQUEST['posts_per_page']) ? intval($_REQUEST['posts_per_page']) : $this->posts_per_page;
        $args['orderby'] = isset($conds['orderby']) ? $conds['orderby'] : 'ID';
        $args['order'] = isset($conds['order']) ? $conds['order'] : 'DESC';
        $args['paged'] = isset($conds['paged']) ? intval($conds['paged']) : max(get_query_var('paged'), get_query_var('page'));
        $args = apply_filters('smartcommerce_filter_' . $this->post_type . '_list_query_args', $args);
        $qry = new \WP_Query($args);
        if(!$qry->have_posts()) return array(
            'posts' => [],
            'total_posts' => 0,
            'total_pages' => 0,
        );
        $posts = [];
        while($qry->have_posts()){
            $qry->the_post();
            $posts[$qry->post->ID] = $qry->post;
        }
        wp_reset_postdata();
        return array(
            'posts' => $posts,
            'total_posts' => $qry->found_posts,
            'total_pages' => $qry->max_num_pages,
        );
    }

    /**
     * Get post list 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function getList()
    {
        $fields = $this->getListFields();
        $postData = $this->getPosts();
        $posts = $postData['posts'];
        $total_pages = $postData['total_pages'];
        
        $before_html = $this->getBeforeListHtml();
        $before_html = apply_filters('smartcommerce_filter_' . $this->post_type . '_list_before_html', $before_html);
        $after_html = apply_filters('smartcommerce_filter_' . $this->post_type . '_list_after_html', '');

        ob_start();
        echo $this->getActionBar();
        echo $before_html;
        if(empty($posts)) {
            echo '<div class="sc-no-posts-found">'. __('No '. $this->post_name .' found', 'smartcommerce') .'</div>';
            return ob_get_clean();
        }
        $symbol = Settings::get('currency_symbol');
        ?>
        <div class="sc-table-wrap">
            <table class="sc-table sc-post-list sc-post-list-<?php echo $this->post_type; ?>">
                <thead>
                    <tr>
                        <?php foreach($fields as $key => $field){ ?>
                            <th <?php if($key == 'post_id' && $this->post_type == 'sc_order') {echo " width='225' ";} ?>>
                                <?php if($key == 'post_id'){ ?>
                                    <input name='bulk_select' data-target='id[]' type="checkbox" id="select-all" class="sc-select-all">
                                <?php } else {
                                    echo $field;
                                } ?>
                            </th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($posts as $post){ ?>
                        <?php $metadata = get_metadata('post', $post->ID); ?>
                        <?php
                            $order_status = $post->post_type == 'sc_order' && isset($metadata['order_status']) ? $metadata['order_status'][0] : '';
                            $order_status_attr = !empty($order_status) ? "data-order-status='{$order_status}'" : '';
                        ?>
                        <tr data-id="<?php echo $post->ID; ?>" data-post-status="<?php echo $post->post_status; ?>" <?php echo $order_status_attr; ?>>
                            <?php foreach($fields as $key => $field){ ?>
                                <td>
                                    <?php 
                                    $value = '';
                                    switch($key){
                                        case 'post_id':
                                            $value = "<input name='id[]' type='checkbox' value='{$post->ID}' id='id_{$post->ID}'><label for='id_{$post->ID}'>#{$post->ID}</label>";
                                            break;
                                        case 'post_thumbnail':
                                            $value = has_post_thumbnail($post->ID) ? wp_get_attachment_image(get_post_thumbnail_id($post->ID), 'sc-thumbnail') : '';
                                            break;
                                        case 'stock_quantity':
                                            $stock_type = isset($metadata['stock_type']) ? $metadata['stock_type'][0] : 'unlimited';
                                            $value = $stock_type;
                                            if($stock_type == 'limited'){
                                                $value = isset($metadata['stock_quantity']) ? $metadata['stock_quantity'][0] : '';
                                            }
                                            if(empty($value)) $value = 'unlimited';
                                            break;
                                        case 'price':
                                            $value = number_format((float) isset($metadata['selling_price']) ? $metadata['selling_price'][0] : 0, 2);
                                            $value = SmartCommerce::convertENtoBN($symbol . $value);
                                            break ;
                                        case 'post_date':
                                            $value = date('d M, y', strtotime($post->post_date));
                                            break;
                                        case 'count_views':
                                            $value = (int) isset($metadata['views']) ? $metadata['views'][0] : 0;
                                            $value = SmartCommerce::convertENtoBN($value);
                                            $value = "<a href='javascript:void(0)' class='sc-ajax-link' data-post_type='{$this->post_type}' data-success_callback='popupData' data-ajax_action='popupProductViews' data-id='{$post->ID}'>$value</a>";
                                            break;
                                        case 'count_orders':
                                            $value = (int) Product::countTotalOrders($post->ID);
                                            $value = SmartCommerce::convertENtoBN($value);
                                            $value = "<a href='javascript:void(0)' class='sc-ajax-link' data-post_type='{$this->post_type}' data-success_callback='popupData' data-ajax_action='popupProductOrders' data-id='{$post->ID}'>$value</a>";
                                            break;
                                        case 'actions':
                                            $value = '<a title="View" target="_blank" href="'. get_permalink($post->ID) .'" class="sc-icon-link">'.SmartCommerce::getIcon('view').'</a>';
                                            $value .= '<a title="Edit" href="'. site_url("/dashboard/?submenu=edit&id={$post->ID}&type={$post->post_type}") .'" class="sc-icon-link">'.SmartCommerce::getIcon('edit').'</a>';
                                            $value .= '<a title="Duplicate" href="javascript:void(0)" class="sc-icon-link sc-ajax-link" data-post_type="'.$post->post_type.'" data-success_callback="duplicatePostSuccessCallback" data-before_send_callback="" data-ajax_action="duplicatePost" data-id="'.$post->ID.'">'.SmartCommerce::getIcon('copy').'</a>';
                                            $value .= '<a title="Delete" href="javascript:void(0)" class="sc-icon-link sc-ajax-link" data-post_type="'.$post->post_type.'" data-success_callback="deletePostSuccessCallback" data-before_send_callback="scConfirm" data-ajax_action="deletePost" data-id="'.$post->ID.'">'.SmartCommerce::getIcon('delete').'</a>';
                                            break;
                                        default:
                                            if(str_contains($key, 'post_')){
                                                $value =  $post->$key ;
                                            } else {
                                                $value = isset($metadata[$key]) ? $metadata[$key][0] : '';
                                            }
                                            break;
                                    }
                                    $value = apply_filters('smartcommerce_filter_' . $this->post_type . '_list_meta_value', $value, $key, $post, $metadata);
                                    $value_cleaned = esc_attr($value);
                                    if($key == 'actions' || $key == 'post_id') $value_cleaned = '';
                                    echo "<span data-meta-key='{$key}' data-post-id='{$post->ID}' data-meta-value='{$value_cleaned}' class='sc-post-meta-value'>". $value ."</span>";
                                    ?>                                    
                                </td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php 
        echo $after_html;
        echo SmartCommerce::getPagination($total_pages);
        $html = ob_get_clean();
        return apply_filters('smartcommerce_filter_' . $this->post_type . '_list_html', $html);
    }

    /**`
     * Get post list html 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function getMeta($key = '', $single = true)
    {
        if(empty($key)) return null;
        if(isset($this->metadata[$key])){
            return $single ? $this->metadata[$key][0] : $this->metadata[$key];
        }
        return null;
    }

    /**
     * Get All Posts 
     * 
     * @param array $conditions 
     * @return array 
     * @since 1.0.0
     */
    public function getAllPosts($conditions = array(), $only_id_title = false)
    {
        if(!isset($conditions['post_type'])) $conditions['post_type'] = $this->post_type;
        if(!isset($conditions['post_status'])) $conditions['post_status'] = 'publish';
        if(!isset($conditions['posts_per_page'])) $conditions['posts_per_page'] = -1;
        if(!isset($conditions['orderby'])) $conditions['orderby'] = 'ID';
        if(!isset($conditions['order'])) $conditions['order'] = 'DESC';
        $qry = new \WP_Query($conditions);
        if(!$only_id_title) return $qry->posts;
        $res = [];
        if($only_id_title){
            while($qry->have_posts()){
                $qry->the_post();
                $res[$qry->post->ID] = $qry->post->post_title;
            }
        }
        return $res;
    }

    /**
     * Increment post view 
     * 
     * @return void 
     * @since 1.0.0
     */
    public function incrementPostView()
    {
        if(is_singular($this->post_type) && !current_user_can('manage_options')) {
            $post_id = get_the_ID();
            $views = (int) get_post_meta($post_id, 'views', true);
            if(!$views) $views = 0;
            update_post_meta($post_id, 'views', $views + 1);

            // details Log 
            $key = 'views_details';
            $details = get_post_meta($post_id, $key, true);
            if(empty($details)) $details = array();
            $date = current_time('Y-m-d');

            $details[$date] = isset($details[$date]) ? $details[$date] + 1 : 1;
            update_post_meta($post_id, $key, $details);
        }
    }

    /**
     * Get Publish Fields 
     * 
     * @return array 
     * @since 1.0
     * @access public 
     */
    public function getPublishFields()
    {
        $fields = array(
            'post_title' => array(
                'type' => 'text',
                'name' => 'post_title',
                'settings' => array(
                    'label' => __('Title', 'smartcommerce'),
                    'required' => true,
                    'placeholder' => __('Enter title', 'smartcommerce'),
                    'value' => $this->post ? $this->post->post_title : '',
                )
            ),
            'post_thumbnail' => array(
                'type' => 'ajax_file',
                'name' => 'post_thumbnail',
                'settings' => array(
                    'label' => __('Main Image', 'smartcommerce'),
                    'preview_content' => array(
                        has_post_thumbnail($this->id) ? wp_get_attachment_image(get_post_thumbnail_id($this->id), 'sc-thumbnail') : '',
                    ),
                    'preview' => true,
                )
            ),
            'post_content' => array(
                'type' => 'textarea',
                'name' => 'post_content',
                'settings' => array(
                    'label' => __('Description', 'smartcommerce'),
                    'required' => false,
                    'placeholder' => __('Enter description', 'smartcommerce'),
                    'value' => $this->post ? $this->post->post_content : '',
                )
            ),
            'post_status' => array(
                'type' => 'select',
                'name' => 'post_status',
                'settings' => array(
                    'label' => __('Status', 'smartcommerce'),
                    'required' => true,
                    'options' => $this->getStatusList(),
                    'value' => $this->post ? $this->post->post_status : 'publish',
                )
            ),
            'post_type' => array(
                'type' => 'hidden',
                'name' => 'post_type',
                'settings' => array(
                    'value' => $this->post_type,
                )
            ),
            'post_id' => array(
                'type' => 'hidden',
                'name' => 'post_id',
                'settings' => array(
                    'value' => $this->id,
                )
            ),
            'post_author' => array(
                'type' => 'hidden',
                'name' => 'post_author',
                'settings' => array(
                    'value' => $this->post ? $this->post->post_author : get_current_user_id(),
                )
            ),
            'action' => array(
                'type' => 'hidden',
                'name' => 'action',
                'settings' => array(
                    'value' => 'smartcommerce_ajax'
                )
            ),
            'ajax_action' => array(
                'type' => 'hidden',
                'name' => 'ajax_action',
                'settings' => array(
                    'value' => $this->post ? 'editPost' : 'publishPost',
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
                    'value' => $this->post ? 'editPostBeforeSendCallback' : 'publishPostBeforeSendCallback',
                )
            ),
            'success_callback' => array(
                'type' => 'hidden',
                'name' => 'success_callback',
                'settings' => array(
                    'value' => $this->post ? 'editPostSuccessCallback' : 'publishPostSuccessCallback',
                )
            ),
            'error_callback' => array(
                'type' => 'hidden',
                'name' => 'error_callback',
                'settings' => array(
                    'value' => $this->post ? 'editPostErrorCallback' : 'publishPostErrorCallback',
                )
            ),
            'complete_callback' => array(
                'type' => 'hidden',
                'name' => 'complete_callback',
                'settings' => array(
                    'value' => $this->post ? 'editPostCompleteCallback' : 'publishPostCompleteCallback',
                )
            ),
            'submit' => array(
                'type' => 'button',
                'name' => 'submit',
                'settings' => array(
                    'class' => 'sc-button sc-button-primary',
                    'type' => 'submit',
                    'value' => $this->post ? __('Update', 'smartcommerce') : __('Publish', 'smartcommerce'),
                )
            )
        );
        return apply_filters('smartcommerce_filter_' . $this->post_type . '_publish_fields', $fields);
    }

    /**
     * Get Edit Fields 
     * 
     * @return array 
     * @since 1.0
     * @access public 
     */
    public function getEditFields()
    {
        $fields = $this->getPublishFields();
        return apply_filters('smartcommerce_filter_' . $this->post_type . '_edit_fields', $fields);
    }

    /**
     * Get Edit Form 
     * 
     * @return string 
     * 
     * @since 1.0
     * @access public 
     */
    public function getPublishForm()
    {
        $fields = $this->getPublishFields();
        if(empty($fields)) return __('No fields found for this post type ' . $this->post_name, 'smartcommerce');
        ob_start();
        ?>
        <div class="sc-page-title-wrap">
            <h1 class='sc-page-title'><?php echo $this->post ? 'Edit ' . $this->post_name : 'Add New ' . $this->post_name; ?></h1>
            <?php if($this->post) : ?>
                <a href="<?php echo site_url('/dashboard/?submenu=publish&type=' . $this->post_type); ?>" class="sc-button sc-button-fadeout sc-fright"><?php _e('Add New ' . $this->post_name, 'smartcommerce'); ?></a>
            <?php endif; ?>
        </div>
        <form action="" class="sc-form sc-ajax-form">
            <?php 
                $hidden_fields = [];
                foreach($fields as $field){
                    if($field['type'] == 'hidden'){
                        $hidden_fields[] = $field;
                        continue;
                    }
                    $label = $field['settings']['label'] ?? '';
                    ?>
                    <div class="sc-form-row">
                        <?php if(!empty($label)): ?>
                            <label for="<?php echo $field['name'] ?? ''; ?>"><?php echo $label; ?></label>
                        <?php endif; ?>
                        <?php echo Form::generateElement($field['type'], $field['name'], $field['settings']); ?>
                    </div>
                    <?php 
                    foreach($hidden_fields as $field){
                        echo Form::generateElement( $field['type'], $field['name'], $field['settings']);
                    }
                }
            ?>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Get Edit Form 
     * 
     * @return string 
     * 
     * @since 1.0
     * @access public 
     */
    public function getEditForm()
    {
        return $this->getPublishForm();
    }

    /**
     * Count total posts 
     * 
     * @param string $status 
     * @return int 
     */
    public function countTotal($status = 'publish')
    {
        $args = array(
            'post_type' => $this->post_type,
            'post_status' => $status,
            'fields' => 'ids',
            'posts_per_page' => -1,
        );
        $qry = new \WP_Query($args);
        return $qry->found_posts;
    }

    /**
     * Get Status List 
     * 
     * @return array 
     * 
     * @since 1.0
     * @access public 
     */
    public function getStatusList()
    {
        return array(
            'publish' => __('Publish', 'smartcommerce'),
            'draft' => __('Draft', 'smartcommerce'),
            'pending' => __('Pending', 'smartcommerce'),
            'trash' => __('Trash', 'smartcommerce'),
            'private' => __('Private', 'smartcommerce'),
            'unlisted' => __('Unlisted', 'smartcommerce'),
        );
    }

    /**
     * Get Skip Fields 
     * 
     * @return array 
     * 
     * @since 1.0
     * @access public 
     */
    public function getSkipFields()
    {
        $fields = array(
            'action',
            'ajax_action',
            '_wpnonce',
            'before_send_callback',
            'success_callback',
            'error_callback',
            'complete_callback',
        );
        return apply_filters('smartcommerce_filter_' . $this->post_type . '_skip_fields', $fields);
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
     * Edit Post 
     * 
     * @param array $data 
     * @return array 
     */
    public function edit($data = array())
    {
        return $this->publish($data);
    }

    /**
     * Duplicate Post 
     * 
     * @param int $id 
     * @return array 
     */
    public function duplicate()
    {
        $parent_id = $this->post->ID;
        $post = $this->post;
        $post->ID = 0;
        $post->post_type = $this->post_type;
        $post->post_status = 'publish';
        $post->post_name = $post->post_name;
        $post->post_date = current_time('Y-m-d H:i:s');
        $post->post_date_gmt = current_time('Y-m-d H:i:s', true);
        $post->post_modified = current_time('Y-m-d H:i:s');
        $post->post_modified_gmt = current_time('Y-m-d H:i:s', true);
        $post->post_author = get_current_user_id();
        $post_id = wp_insert_post($post);
        if(!is_wp_error($post_id)){
            $metadata = $this->metadata;
            foreach($metadata as $key => $value){
                if(str_contains($key, 'variation_data')) {
                    $value = maybe_unserialize($value[0]);
                    update_post_meta($post_id, $key, $value);
                } else {
                    update_post_meta($post_id, $key, $value[0]);
                }
            }
            update_post_meta($post_id, 'views', 0);
            update_post_meta($post_id, 'views_details', array());

            // Getting product terms / cats 
            $terms = wp_get_post_terms($parent_id, 'sc_product_category');
            if(!empty($terms)){
                foreach($terms as $term){
                    wp_set_post_terms($post_id, $term->term_id, 'sc_product_category', true);
                }
            }

            $res = [];
            $res['status'] = true;
            $res['message'] = "{$this->post_name} duplicated successfully";
            $res['post_id'] = $post_id;
            $res['post_type'] = $this->post_type;
            return $res;
        }
        $res = [];
        $res['status'] = false;
        $res['message'] = "Failed to duplicate {$this->post_name}";
        $res['post_id'] = 0;
        $res['post_type'] = $this->post_type;
        return $res;
    }

    /**
     * Delete Post 
     * 
     * @param int $id 
     * @return array 
     */
    public function delete($id=0)
    {
        if(!$id) return ['status' => false, 'message' => 'Invalid post id'];
        $delete = wp_delete_post($id);
        if($delete){
            $res = [];
            $res['status'] = true;
            $res['message'] = "{$this->post_name} deleted successfully";
            $res['post_id'] = $id;
            $res['id'] = $id;
            $res['post_type'] = $this->post_type;
            return $res;
        }
        $res = [];
        $res['status'] = false;
        $res['message'] = "Failed to delete {$this->post_name}";
        $res['id'] = $id;
        $res['post_id'] = $id;
        $res['post_type'] = $this->post_type;
        return $res;
    }

    /**
     * Bulk Delete Post 
     * 
     * @param array $ids 
     * @return array 
     */
    public function bulkDelete($ids = array())
    {
        if(!is_array($ids) || empty($ids)) return ['status' => false, 'message' => 'No items to delete'];
        foreach($ids as $id){
            wp_delete_post($id);
        }
        $res = [];
        $res['status'] = true;
        $res['message'] = "{$this->post_name} deleted successfully";
        $res['ids'] = $ids;
        $res['post_type'] = $this->post_type;
        return $res;
    }

        /**
     * Show Filter List Before HTml 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function getBeforeListHtml()
    {
        $post_status = sanitize_text_field($_GET['post_status'] ?? '');
        $status_list = $this->getStatusListToShowCounts();
        ob_start();
        ?>
        <div class="sc-filter-wrap">
            <ul class="sc-order-status-filter desktop-only">
                <?php foreach($status_list as $status => $data){ ?>
                    <li data-status="<?php echo $status; ?>">
                        <a href="<?php echo site_url('/dashboard/?submenu='.$this->post_type.'&post_status='.$status); ?>" class="<?php echo $status == $post_status ? 'active' : ''; ?>">
                            <?php echo $data['label']; ?>(<?php echo $data['count']; ?>)
                        </a>
                    </li>
                <?php } ?>
            </ul>
            <div class="mobile-only">
                <select name='filter_post_status' class="filter-post-status sc-mb-20">
                    <?php foreach($status_list as $status => $data){ ?>
                        <option data-url="<?php echo site_url('/dashboard/?submenu='.$this->post_type.'&post_status='.$status); ?>" value="<?php echo $status; ?>" <?php echo $status == $post_status ? 'selected' : ''; ?>>
                            <?php echo $data['label']; ?> (<?php echo $data['count']; ?>)
                        </option>
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
        include_once SMART_COMMERCE_CLASS_DIR . '/ProductCategory.php';
        ob_start();
        ?>
        <form class='sc-search-form' action="<?php echo site_url('/dashboard/?submenu='.$this->post_type); ?>" method="get">
            <?php 
                echo Form::generateElement('text', 'search', array(
                    'placeholder' => __('Search ID, Product Name', 'smartcommerce'),
                    'value' => sanitize_text_field($_GET['search'] ?? ''),
                    'name' => 'search',
                ));
                $product_cats = ProductCategory::getAll(0);
                $product_cats_options = array();
                foreach($product_cats as $cat){
                    $product_cats_options[$cat->term_id] = $cat->name;
                }
                echo Form::generateElement('select', 'search_cat', array(
                    'options' => $product_cats_options,
                    'name' => 'search_cat',
                    'value' => sanitize_text_field($_GET['search_cat'] ?? ''),
                    'placeholder' => __('Select Category', 'smartcommerce'),
                ));
                echo Form::generateElement('select', 'posts_per_page', array(
                    'options' => array(
                        25 => __('25', 'smartcommerce'),
                        50 => __('50', 'smartcommerce'),
                        100 => __('100', 'smartcommerce'),
                        200 => __('200', 'smartcommerce'),
                        500 => __('500', 'smartcommerce'),
                    ),
                    'name' => 'posts_per_page',
                    'value' => sanitize_text_field($_GET['posts_per_page'] ?? $this->posts_per_page),
                    'placeholder' => __('Posts Per Page', 'smartcommerce'),
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
            'publish' => array(
                'count' => $this->countTotal('publish'),
                'label' => __('Publish', 'smartcommerce'),
            ),
            'pending' => array(
                'count' => $this->countTotal('pending'),
                'label' => __('Pending', 'smartcommerce'),
            ),
            'unlisted' => array(
                'count' => $this->countTotal('unlisted'),
                'label' => __('Unlisted', 'smartcommerce'),
            ),
            'private' => array(
                'count' => $this->countTotal('private'),
                'label' => __('Private', 'smartcommerce'),
            ),
            'trash' => array(
                'count' => $this->countTotal('trash'),
                'label' => __('Trash', 'smartcommerce'),
            ),
        );
    }



    public static function getColorShade($value, $maxValue = 1000, $baseColor = "#00cc66") {
        if ($maxValue <= 0) $maxValue = 1;
    
        // Normalize between 0 and 1
        $normalized = min($value / $maxValue, 1.0);
    
        // Convert HEX → HSL
        list($h, $s, $l) = self::hexToHsl($baseColor);
    
        // Adjust lightness: make it lighter for low values, darker for high
        $lightness = 99 - (60 * $normalized); // range 90% → 30%
        $l = $lightness;
    
        return self::hslToHex($h, $s, $l);
    }
    
    /**
     * Convert HEX → HSL
     */
    public static function hexToHsl($hex) {
        $hex = ltrim($hex, '#');
    
        if (strlen($hex) === 3) {
            $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
    
        $r /= 255; $g /= 255; $b /= 255;
    
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $h = $s = $l = ($max + $min) / 2;
    
        if ($max == $min) {
            $h = $s = 0; // gray
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            switch ($max) {
                case $r: $h = ($g - $b) / $d + ($g < $b ? 6 : 0); break;
                case $g: $h = ($b - $r) / $d + 2; break;
                case $b: $h = ($r - $g) / $d + 4; break;
            }
            $h /= 6;
        }
    
        return [round($h * 360), round($s * 100), round($l * 100)];
    }
    
    /**
     * Convert HSL → HEX
     */
    public static function hslToHex($h, $s, $l) {
        $h /= 360;
        $s /= 100;
        $l /= 100;
    
        if ($s == 0) {
            $r = $g = $b = $l; // achromatic
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = self::hueToRgb($p, $q, $h + 1/3);
            $g = self::hueToRgb($p, $q, $h);
            $b = self::hueToRgb($p, $q, $h - 1/3);
        }
    
        return sprintf("#%02x%02x%02x", round($r * 255), round($g * 255), round($b * 255));
    }
    
    public static function hueToRgb($p, $q, $t) {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        return $p;
    }

    /**
     * Show Page Views Details 
     * 
     * @return string 
     * @param int $id 
     * 
     * @since 1.0.0
     * @access public 
     */
    public static function getStatistics($id=0, $type='views')
    {
        $post = get_post($id);
        if(!$post) return __('Post not found', 'smartcommerce');
        
        $week_days = array('Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday');
        
        $start = new \DateTime(date('Y-m-d', strtotime($post->post_date)));
        $start->modify('saturday last week');
        $end = new \DateTime(current_time('Y-m-d'));
        $end->modify('friday next week');

        $start_date = new \DateTime($start->format('Y-m-d'));
        $end_date = new \DateTime($end->format('Y-m-d'));

        $display_data = [];

        $metadata = [];
        if($type == 'views'){
            $metadata = maybe_unserialize(get_post_meta($id, 'views_details', true));
        } 

        // make an array of week days with week number 
        $week_days_with_week_number = array();

        $min_val = 0;
        $max_val = 0;
        $day_wise_count = [];
        $week_wise_count = [];


        for($i = $start_date; $i <= $end_date; $i->modify('+1 day')){

            $week_number = (int) $i->format('W');
            $days_of_week = array();
            for($j = 0; $j < 7; $j++){

                if($type == 'views'){
                    $display_data[$i->format('Y-m-d')] = $metadata[$i->format('Y-m-d')] ?? 0;
                } else {
                    $display_data[$i->format('Y-m-d')] = (int) Product::countTotalOrders($id, $i->format('Y-m-d'));
                }

                $day = $i->format('l');
                if(!isset($day_wise_count[$day])) $day_wise_count[$day] = 0;
                $day_wise_count[$day] += $display_data[$i->format('Y-m-d')] ?? 0;

                if(!isset($week_wise_count[$week_number])) $week_wise_count[$week_number] = 0;
                $week_wise_count[$week_number] += $display_data[$i->format('Y-m-d')] ?? 0;

                $count_actual = $display_data[$i->format('Y-m-d')] ?? 0;
                if($count_actual < $min_val){
                    $min_val = $count_actual;
                }
                if($count_actual > $max_val){
                    $max_val = $count_actual;
                }
                $days_of_week[] = array(
                    'date' => $i->format('Y-m-d'),
                    'action' => $display_data[$i->format('Y-m-d')] ?? 0,
                    'day' => $i->format('l'),
                );
                if($i->format('l') != 'Friday'){
                    $i->modify('+1 day');
                }
            }
            $week_days_with_week_number[$week_number] = $days_of_week;
        }


        ob_start();
        ?>
        <style>
            .sc-action-wrap {
                display: flex;
                flex-direction: column;
                gap: 0;
                flex: 1;
                flex-wrap: nowrap;
                border: 1px solid var(--sc-border-color);
                font-size: 12px;
            }
            .sc-action-row{
                display: flex;
                flex-direction: row;
                gap: 0;
                border-bottom: 1px solid var(--sc-border-color);
            }
            .sc-action-row:last-child{
                border-bottom: none;
            }
            .action-col{
                flex: 1;
                text-align: center;
                border-right: 1px solid var(--sc-border-color);
                display: flex;
                justify-content: center;
                align-items: center;
                flex-direction: column;
                padding: 3px;
                font-size:12px;
            }
            .action-col:last-child{
                border-right: none;
            }
            .action-count{
                font-size: 12px;
                font-weight: 600;
            }
            .action-date{
                font-size: 10px;
            }
        </style>

        <h6 style='text-align: center;'><?php _e( ucwords(strtolower($type)) . ' Details for ', 'smartcommerce'); ?> <?php echo get_the_title($id); ?></h6>
        <div class="sc-action-wrap">

            <div class="sc-action-row">
                <div class="action-col"><?php _e('Week', 'smartcommerce'); ?></div>
                <?php foreach($week_days as $day){ ?>
                    <div class='action-col'><?php _e($day, 'smartcommerce'); ?></div>
                <?php }?>
                <div class='action-col'><span class="action-count"><?php _e('Total', 'smartcommerce'); ?></span></div>
            </div>

            <?php foreach($week_days_with_week_number as $week_number => $w_days){ ?>
                <div class="sc-action-row">
                    <div class="action-col"><?php echo SmartCommerce::convertENtoBN($week_number); ?></div>
                    <?php foreach($w_days as $day){ ?>
                        <div class='action-col' style="background-color: <?php echo self::getColorShade($day['action'], $max_val, '#00cc66'); ?>">
                            <span class='action-count'><?php echo SmartCommerce::convertENtoBN($display_data[$day['date']] ?? 0) . ' '; _e($type, 'smartcommerce'); ?></span>
                            <span class='action-date'><?php echo SmartCommerce::convertENtoBN(date('d/m/y', strtotime($day['date']))); ?></span>
                        </div>
                    <?php }?>
                    <div class='action-col'><span class="action-count"><?php echo SmartCommerce::convertENtoBN($week_wise_count[$week_number] ?? 0); ?></span></div>
                </div>
            <?php }?>

            <!-- Show Day wise total count -->
            <div class="sc-action-row">
                <div class="action-col"><?php _e('Total', 'smartcommerce'); ?></div>
                <?php foreach($week_days as $day){ ?>
                    <div class='action-col'><span class="action-count"><?php echo SmartCommerce::convertENtoBN($day_wise_count[$day] ?? 0); ?></span></div>
                <?php }?>
                <div class='action-col'><span class="action-count"><?php echo SmartCommerce::convertENtoBN(array_sum($week_wise_count)); ?></span></div>
            </div>

        </div>

        <?php 
        return ob_get_clean();
    }

    /**
     * Show Category wise Posts
     * 
     * @return string 
     * @param int $id 
     * 
     * @since 1.0.0
     * @access public 
     */
    public function showCategoryWisePosts( $configs = [] )
    {
        $configs = wp_parse_args($configs, array(
            'tax_id' => 0,
            'title' => '',
            'limit' => 10,
        ));
        if(!$configs['tax_id']) return __('Taxonomy ID is required', 'smartcommerce');
        $args = array(
            'post_type' => $this->post_type,
            'post_status' => 'publish',
            'posts_per_page' => $configs['limit'],
            'orderby' => 'date',
            'order' => 'DESC',
            'tax_query' => array(
                array(
                    'taxonomy' => 'sc_product_category',
                    'field' => 'term_id',
                    'terms' => $configs['tax_id'],
                ),
            ),
        );
        $qry = new \WP_Query($args);
        if(!$qry->have_posts()) return __('No posts found', 'smartcommerce');
        ob_start();
        if(empty($configs['title'])){
            $term = get_term($configs['tax_id']);
            $configs['title'] = $term->name;
        }
        ?>
        <h3 class='section-title'><?php _e($configs['title'], 'smartcommerce'); ?>
            <a class='section-view-all' href='<?php echo get_term_link($configs['tax_id']); ?>'><?php _e('View All', 'smartcommerce'); ?><i class='fas fa-arrow-right'></i></a>
        </h3>
        <div class="product-loop template-default">
            <?php while($qry->have_posts()){ $qry->the_post(); ?>
                <?php include SMART_COMMERCE_THEME_DIR . 'templates/product-loop/loop-default.php'; ?>
            <?php }?>
        </div>
        <?php
        return ob_get_clean();
        
    }
}

$sc_post = Post::instance();
$sc_post->wpInit();