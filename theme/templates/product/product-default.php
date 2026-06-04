<?php 
defined('ABSPATH') || exit;
use SmartCommerce\Smartcommerce;
use SmartCommerce\Product;
use SmartCommerce\Order;
use SmartCommerce\Settings;

if(!$this->product){
    _e('Product not found', 'smartcommerce');
    return;
}
$images = $this->product->getImages();
?>
<style>
    div#sc-product-wrap{
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
        flex: 1;
        margin: 50px 0;
    }
    .sc-product-title,
    .sc-product-description,
    .sc-order-form{
        margin-bottom: 20px;
    }

</style>
<div id="sc-product-wrap">
    <div class="sc-flex-wrap">

        <!-- Gallery -->
        <div class="flex-4 product-gallery">
               <?php echo $this->product->getImageGallery($size='large'); ?>
               <?php 
               $youtube_url = get_post_meta($this->product->id, 'youtube_video_url', true);
               if(!empty($youtube_url)):
                    echo Smartcommerce::getYoutubeVideoPlayer($youtube_url);
               endif;
               ?>
        </div>

        <!-- Content -->
        <div class="flex-8 product-content">

            <!-- Title -->
            <h1 class='sc-product-title'><?php the_title(); ?></h1>

            <!-- Share Page -->
            <div class="sc-product-share">
                <?php echo SmartCommerce::sharePage(); ?>
            </div>

            <!-- Post Content Meta -->
            <ul class="sc-product-meta">
                <li class="sc-product-meta-item">
                    <span class="sc-product-meta-item-label"><?php _e('ID', 'smartcommerce'); ?></span>
                    <span class="sc-product-meta-item-value">#<?php echo SmartCommerce::convertENtoBN($this->product->id); ?></span>
                </li>
                <?php
                    $views = $this->product->metadata['views'][0] ?? 0;
                    if($views > 0):
                ?>
                <li class="sc-product-meta-item">
                    <span class="sc-product-meta-item-label"><?php _e('Views', 'smartcommerce'); ?></span>
                    <span class="sc-product-meta-item-value"><?php echo SmartCommerce::convertENtoBN($views); ?></span>
                </li>
                <?php endif; ?>
                
                <?php $category_list = $this->product->getCategoryList();
                if(!empty($category_list)): ?>
                <li class="sc-product-meta-item">
                    <span class="sc-product-meta-item-label"><?php _e('Category', 'smartcommerce'); ?></span>
                    <span class="sc-product-meta-item-value"><?php echo $this->product->getCategoryList(); ?></span>
                </li>
                <?php endif; ?> 


                <?php
                    $available_stock = 0;
                    $type = $this->product->getStockType();
                    if($type == 'unlimited') $available_stock = 'Available';
                    else if($type == 'limited') $available_stock = $this->product->getStockQuantity();
                    else $available_stock = 'Available';
                    if(!empty($available_stock)): 
                ?>
                <li class="sc-product-meta-item">
                    <span class="sc-product-meta-item-label"><?php _e('Stock', 'smartcommerce'); ?></span>
                    <span class="sc-product-meta-item-value"><?php _e($available_stock, 'smartcommerce'); ?></span>
                </li>
                <?php endif; ?>

            </ul>

            <!-- Whatsapp Highlight -->
            <?php 
                $whatsapp = Settings::get('whatsapp_number');
                if(!empty($whatsapp)){ 
                ?>
                <style>
                    .sc-whatsapp-highlight{
                        background-color: var(--sc-accent-color);
                        padding: 10px;
                        border-radius: var(--sc-border-radius);
                        margin-bottom: 20px;
                        width: 100%;
                        box-sizing: border-box;
                    }
                    .sc-whatsapp-highlight:hover{
                        background-color: var(--sc-primary-color);
                    }
                    .sc-whatsapp-highlight a{
                        color: var(--sc-white-color);
                        text-decoration: none;
                    }
                </style>
                <div class="sc-whatsapp-highlight">
                    <a href="<?php echo Product::getWhatsappShareLink(get_the_ID()); ?>" target="_blank"><i class='fab fa-whatsapp'></i> <?php _e('Have Questions? Ask us on Whatsapp!', 'smartcommerce'); ?></a>
                </div>
            <?php } ?>

            <!-- Description -->
            <div class="sc-product-description sc-text-editor-content"><?php the_content(); ?></div>

            <!-- Order Form -->
            <div class="sc-order-form">
                <?php echo Order::getOrderForm($this->id); ?>
            </div>


            <!-- Similar Products-->
            <?php echo Product::showSimilarProducts(get_the_ID(), 'Similar Products Picked for You', true); ?>

            <!-- Social Share -->
            <div class="sc-product-social-share" style="margin-top: 50px;">
                <?php echo SmartCommerce::sharePage(); ?>
            </div>

        </div>

    </div>
</div>