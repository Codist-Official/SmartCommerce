<?php
use SmartCommerce\SmartCommerce;

defined('ABSPATH') || exit;

class ThemeProducts
{
    public $id;
    private static $_instance;
    public $posts_per_page = 10;
    public $template = 'products-default';

    /**
     * @return ThemeProducts
     */
    public static function instance()
    {
        if( is_null(self::$_instance) ) self::$_instance = new self();
        return self::$_instance;
    }

    public function __construct()
    {
        add_shortcode('sc_products', [$this, 'products']);
    }

    public function wpInit()
    {
    }

    public function products($atts=[])
    {
        $args = array(
            'post_type' => 'sc_product',
            'posts_per_page' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
            'paged' => max(max(1,get_query_var('page')), get_query_var('paged')),
            'post_status' => 'publish',
            'posts_per_page' => $this->posts_per_page,
        );
        $s = sanitize_text_field($_REQUEST['search'] ?? '');
        if(!empty($s)) $args['s'] = $s;
        $qry = new \WP_Query($args);
        if(!$qry->have_posts()) return __('No products found', 'smartcommerce');
        ob_start();
        ?>
        <div class="sc-count-posts">
            <?php _e('Total products found', 'smartcommerce'); ?>: <?php echo SmartCommerce::convertENToBN($qry->found_posts); ?>
        </div>
        <div class="product-loop template-default" style="margin-bottom: 50px;">
        <?php 
            while($qry->have_posts()){
                $qry->the_post();
                include SMART_COMMERCE_THEME_DIR . 'templates/product-loop/loop-default.php';
            }
        ?>
        </div>
        <?php
        wp_reset_postdata();
        echo SmartCommerce::getPagination($qry->max_num_pages);
        return ob_get_clean();
    }

    public function showPageTitle()
    {
        global $post;
        return $post->post_type != 'sc_product';
    }
}

$tp = ThemeProducts::instance();
$tp->wpInit();