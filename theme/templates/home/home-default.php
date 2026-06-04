<?php 
defined('ABSPATH') || exit;
use SmartCommerce\SmartCommerce;
use SmartCommerce\Settings;
use SmartCommerce\ProductCategory;

$slider_images = Settings::get('home_slider_images');
$slider_images = explode(',', $slider_images);
$slider_images = array_unique(array_filter($slider_images));
?>


<?php if(!empty($slider_images)): ?>
    <ul class="image-slider home-slider" style="margin-top: 30px;">
        <?php foreach($slider_images as $slider_image): ?>
            <?php $url = wp_get_attachment_image_url($slider_image, 'full'); ?>
            <li class="image-slider-item" style="background: url('<?php echo $url ?>') no-repeat center center;"></li>
        <?php endforeach; ?>
    </ul>
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                jQuery('.home-slider').lightSlider({
                    item: 1,
                    loop: true,
                    autoplay: true,
                    slideMove: 1,
                    speed: 400, //ms'
                    auto: true,
                    slideEndAnimation: true,
                    pause: 2000,
                });
            }, 1000);
        });
    </script>
<?php endif; ?>


<!-- Latest Products --> 
<?php
    $args = array(
        'post_type' => 'sc_product',
        'posts_per_page' => 10,
        'orderby' => 'date',
        'order' => 'DESC',
        'post_status' => 'publish',
    );
    $products = new WP_Query($args);
    if($products->have_posts()): ?>
        <section class='latest_products'>
            <h3 class="section-title">
                <?php _e('Latest Products', 'smartcommerce'); ?>
                <a href="<?php echo home_url('/products'); ?>" class="section-view-all"><?php _e('View All', 'smartcommerce'); ?><i class="fa fa-arrow-right"></i></a>
            </h3>
            <div class="product-loop template-default">
            <?php
                while($products->have_posts()): $products->the_post();
                    include SMART_COMMERCE_THEME_DIR . 'templates/product-loop/loop-default.php';
                endwhile;
                wp_reset_postdata();
            ?>
            </div>
        </section>
    <?php endif;
?>


<!-- Categories -->
<?php
    $cat_args = array(
        'taxonomy' => 'sc_product_category',
        'hide_empty' => true,
        'orderby' => 'name',
        'order' => 'ASC',
    );
    $categories = get_terms($cat_args);
    if($categories):
?>
    <section class='popular_cats'>
        <h3 class="section-title">
            <?php _e('Popular Categories', 'smartcommerce'); ?>
            <a href="<?php echo home_url('/categories'); ?>" class="section-view-all"><?php _e('View All', 'smartcommerce'); ?><i class="fa fa-arrow-right"></i></a>
        </h3>
        <div class="category-loop">
            <?php foreach($categories as $category): ?>
                <?php 
                    $img_id = get_term_meta($category->term_id, 'featured_image', true);
                    $img = $img_id ? wp_get_attachment_image_url($img_id, 'sc-thumbnail') : SMART_COMMERCE_THEME_URL . 'assets/img/image-placeholder.png';
                ?>
                <div class="category-loop-item">
                    <div class="category-loop-item-image" style="background: url('<?php echo $img; ?>') no-repeat center center;"></div>
                    <div class="category-loop-item-title">
                        <a itemprop="url" href="<?php echo get_term_link($category); ?>"><?php echo $category->name; ?></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>


<!-- Trending Products --> 
<?php
    $args = array(
        'post_type' => 'sc_product',
        'posts_per_page' => 10,
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'post_status' => 'publish',
        'meta_key' => 'views',
    );
    $products = new WP_Query($args);
    if($products->have_posts()):
    ?>
    <section class='trending'>
        <h3 class="section-title"><?php _e('Trending Products', 'smartcommerce'); ?></h3>
        <div id="trendingProducts" class="trending-loop template-default">
            <?php 
            while($products->have_posts()): $products->the_post();
                include SMART_COMMERCE_THEME_DIR . 'templates/product-loop/loop-trending.php';
            endwhile;
            wp_reset_postdata();
        ?>
        </div>
    </section>
    <script>
        window.addEventListener('load', function() {
            setTimeout(function(){
                jQuery('#trendingProducts').lightSlider({
                    item: 5,
                    loop: true,
                    autoplay: true,
                });
            }, 1000);
        });
    </script>
<?php endif; ?>

<!-- Load all category wise posts -->
<div id="category-wise-posts"></div>
<script>
    jQuery(document).ready(function($){
        $.get(smartcommerce.ajax_url, {
            action: 'smartcommerce_ajax',
            ajax_action: 'loadAllCategoryWisePosts',
            _wpnonce: smartcommerce._wpnonce,
        }, function(r){
            $j('#category-wise-posts').html(r.data.data);
            
        });
    });
</script>


<style>
    h1#page-title{
        display: none;
    }
</style>