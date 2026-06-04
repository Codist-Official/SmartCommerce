<?php
use SmartCommerce\SmartCommerce;
use SmartCommerce\Settings;
defined('ABSPATH') || exit;

$thumb_url = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'medium') : '';
$regular_price = (float) get_post_meta(get_the_ID(), 'regular_price', true);
$sale_price = (float) get_post_meta(get_the_ID(), 'selling_price', true);
$discount = $regular_price > 0 && $sale_price > 0 ? round(($regular_price - $sale_price) / $regular_price * 100) : 0;
$symbol = Settings::get('currency_symbol');
?>
<div class="product-loop-item" data-id="<?php the_ID(); ?>" data-post-type="<?php echo get_post_type(); ?>">
    <a itemprop="url" href="<?php the_permalink(); ?>" class="featured-image" style="background-image: url('<?php echo $thumb_url; ?>');">
        <?php 
            if($discount > 0):
                echo "<span class='discount-badge'>" . SmartCommerce::convertENToBN($discount) . "% <br>" . __('Discount', 'smartcommerce') . "</span>";
            endif;
        ?>
    </a>
    <div class="product-loop-content">
        <div class="id-cat-wrap">
        <span class="product-loop-id">[<?php _e('ID', 'smartcommerce'); ?>: #<?php echo SmartCommerce::convertENToBN(get_the_ID()); ?>]</span>
        <?php 
            $cats = get_the_terms(get_the_ID(), 'sc_product_category');
            $cat = !empty($cats) ? $cats[0] : '';
        ?>
        <?php if(!empty($cat)): ?>
            <span class="product-loop-category"><i class="fa fa-tag"></i><a href="<?php echo get_term_link($cat->term_id); ?>"><?php echo $cat->name; ?></a></span>
        <?php endif; ?>
        </div>
        <h4 class="title">
            <a itemprop="url" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h4>
        <div class="price-wrap">
            <?php _e('Price', 'smartcommerce'); ?>: 
            <?php if ($regular_price > $sale_price): ?>
                <span class="regular-price strikethrough"><?php echo $symbol; ?><?php echo SmartCommerce::convertENToBN($regular_price); ?></span>
                <span class="sale-price"><?php echo $symbol; ?><?php echo SmartCommerce::convertENToBN($sale_price); ?></span>
            <?php else: ?>
                 <span class="sale-price"><?php echo $symbol; ?><?php echo SmartCommerce::convertENToBN($sale_price); ?></span>
            <?php endif; ?>
        </div>
        <div class="action">
            <button data-price="<?php echo $sale_price; ?>" data-success_callback="showPopupOnCallback" data-ajax_action="showOrderFormOnClick" class='sc-button sc-ajax-link' data-id="<?php the_ID(); ?>" data-post-type="<?php echo get_post_type(); ?>">
                <i class='fa fa-shopping-cart'></i>
                <?php _e('Order Now', 'smartcommerce'); ?>
            </button>
        </div>
    </div>
</div>