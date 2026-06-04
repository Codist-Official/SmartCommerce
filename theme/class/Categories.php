<?php
use SmartCommerce\SmartCommerce;
use SmartCommerce\ProductCategory;

defined('ABSPATH') || exit;

class ThemeCategories
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
        add_shortcode('sc_categories', [$this, 'categories']);
    }

    public function wpInit()
    {
    }

    public function categories($atts=[])
    {
        $categories = ProductCategory::getAll();
        ob_start();
        ?>
        <style>
            .sc-categories-wrap{
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 20px;
            }
            .sc-category-item{
                text-align: left;
                background-color: var(--sc-primary-bg-color)
            }
            .sc-category-item .featured-imge{
                display: block;
                text-decoration: none;
                width: 100%;
                height: 200px;
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            }
            .sc-category-item .sc-category-content{
                padding: 15px;
            }
            .sc-category-item .count{
                font-size: 12px;
                font-weight: 600;
            }
            .sc-category-item a.title{
                display: block;
                text-decoration: none;
                font-weight: 700;
                text-decoration: none;
                width: 100%;
                box-sizing: border-box;
                margin-top: 10px;
                color: var(--sc-primary-color);
            }
            .sc-category-item a:hover{
                color: var(--sc-secondary-color);
            }
            .sc-category-item a:hover img{
                transform: scale(1.05);
            }
            @media (max-width: 768px){
                .sc-categories-wrap{
                    grid-template-columns: repeat(1, 1fr);
                }
            }
        </style>
        <div class="sc-categories-wrap">
            <?php foreach($categories as $category){ ?>
                <?php 
                    $link = get_term_link($category->term_id, 'sc_product_category'); 
                    $image = get_term_meta($category->term_id, 'featured_image', true);
                    $image = is_numeric($image) ? wp_get_attachment_image_url($image, 'medium') : SMART_COMMERCE_IMG_URL . 'image-placeholder.png';

                    // Count POsts 
                    $pc = new ProductCategory();
                    $count = $pc->countPosts($category->term_id);
                    ?>
                    <div class="sc-category-item">
                        <a href="<?php echo $link; ?>" class="featured-imge" style="background-image: url(<?php echo $image; ?>);"></a>
                        <div class="sc-category-content">
                            <div class="count">[<?php echo SmartCommerce::convertENToBN($count); ?> <?php _e('Products', 'smartcommerce'); ?>]</div>
                            <a href="<?php echo $link; ?>" class="title">
                                <?php echo $category->name; ?>
                            </a>
                        </div>
                    </div>
            <?php } ?>
        </div>
        <?php 
        return ob_get_clean();
    }

    public function showPageTitle()
    {
        global $post;
        return $post->post_type != 'sc_product';
    }
}

$tp = ThemeCategories::instance();
$tp->wpInit();