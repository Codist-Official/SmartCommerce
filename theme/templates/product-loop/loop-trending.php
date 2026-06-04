<?php
use SmartCommerce\SmartCommerce;
use SmartCommerce\Settings;
defined('ABSPATH') || exit;

$thumb_url = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'medium') : '';
$regular_price = (float) get_post_meta(get_the_ID(), 'regular_price', true);
$sale_price = (float) get_post_meta(get_the_ID(), 'selling_price', true);
$symbol = Settings::get('currency_symbol');
?>
<div class="product-loop-item template-default" data-id="<?php the_ID(); ?>" data-post-type="<?php echo get_post_type(); ?>">
    <a itemprop="url" href="<?php the_permalink(); ?>" class="featured-image" style="background-image: url('<?php echo $thumb_url; ?>');"></a>
    <div class="product-loop-content">
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
            <button data-success_callback="showPopupOnCallback" data-ajax_action="showOrderFormOnClick" class='sc-button sc-ajax-link' data-id="<?php the_ID(); ?>" data-post-type="<?php echo get_post_type(); ?>">
                <i class='fa fa-shopping-cart'></i>
                <?php _e('Order Now', 'smartcommerce'); ?>
            </button>
        </div>
    </div>
</div>