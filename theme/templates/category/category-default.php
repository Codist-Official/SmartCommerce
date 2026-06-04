<?php 
defined('ABSPATH') || exit;
use SmartCommerce\SmartCommerce;
use SmartCommerce\ProductCategory;
?>

<div class="container-wrap">
    <div class="container">

        <!-- Slider -->
        <?php
            $images = get_term_meta(get_queried_object_id(), 'images', true);
            $images = explode(',', $images);
            $images = array_map('intval', $images);
            $images = array_filter($images);
        ?>
        <?php if(!empty($images)): ?>
            <ul class="image-slider category-slider">
                <?php foreach($images as $image): ?>
                    <li class="image-slider-item" style="background: url('<?php echo wp_get_attachment_image_url($image, 'full') ?>') no-repeat center center;"></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if(count($images) > 1): ?>
            <script>
                window.addEventListener('load', function() {
                    setTimeout(function() {
                        jQuery('.category-slider').lightSlider({
                            item: 1,
                            loop: true,
                            autoplay: true,
                            slideMove: 1,
                        });
                    }, 1000);
                });
            </script>
        <?php endif; ?>

        <!-- Total Found Posts -->
        <?php 
            $pc = new ProductCategory(get_queried_object_id());
            $count = $pc->countPosts(get_queried_object_id());
        ?>
        <h3 class="category-title"><?php _e('Total products found', 'smartcommerce'); ?>: <?php echo SmartCommerce::convertENToBN($count); ?></h3>

        <!-- Product list --> 
        <?php
            $args = array(
                'post_type' => 'sc_product',
                'post_status' => 'publish',
                'posts_per_page' => 50,
                'paged' => max(1, max(get_query_var('paged'), get_query_var('page'))),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'sc_product_category',
                        'field' => 'term_id',
                        'terms' => get_queried_object_id(),
                    ),
                ),
            );
            $products = new WP_Query($args);
            if($products->have_posts()):
                ?>
                <div class="product-loop template-default">
                <?php 
                    while($products->have_posts()):
                        $products->the_post();
                        include SMART_COMMERCE_THEME_DIR . 'templates/product-loop/loop-default.php';
                    endwhile;
                ?>
                </div>
                <?php
            endif;
            wp_reset_postdata();
        ?>

        <div class="pagination-wrap">
            <?php echo SmartCommerce::getPagination($products->max_num_pages); ?>
        </div>
        <style>
            .pagination-wrap{
                margin: 50px auto;
            }
        </style>
    </div>
</div>