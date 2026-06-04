<?php 
use SmartCommerce\SmartCommerce;
use SmartCommerce\Settings;
defined('ABSPATH') || exit;

$name = Settings::get('shop_name');
$address = Settings::get('shop_address');
$logo = Settings::get('shop_logo');
$whatsapp = Settings::get('whatsapp_number');
$email = Settings::get('shop_email');
$phone = Settings::get('phone_number');
$facebook = Settings::get('facebook_url');
$website = Settings::get('website_url');
$instagram = Settings::get('instagram_url');
$youtube = Settings::get('youtube_url');

$footer_columns = [];
for($i= 1; $i<=5; $i++){
    $title = Settings::get('footer_column_'.$i . '_title');
    $details = Settings::get('footer_column_'.$i . '_details');
    if(!empty($title) && !empty($details)){
        $footer_columns[] = [
            'title' => $title,
            'details' => $details,
        ];
    }
}

$footer_columns_wrap = count($footer_columns) > 0 ? ceil(12/count($footer_columns)) : 0;
?>
<style>
    #site-footer{
        margin-top: 50px;
    }
    .social-urls-wrap{
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 50px;
    }
    .footer-widgets{
        padding: 50px 0;
        border-top: 1px solid var(--sc-primary-bg-color);
        background-color: var(--sc-primary-bg-color);
    }
    .footer-widgets .flex-3{
        display: flex;
        align-items: center;
    }
    .footer-logo img{
        width: 100%;
        max-width: 150px;
        height: auto;
        float: left;
    }
    .logo-text{
        font-size: 24px;
        font-weight: 700;
        color: var(--sc-primary-color);
        text-transform: uppercase;
    }
    .large-icon{
        color: var(--sc-primary-color);
        font-size: 24px;
        margin-right: 10px;
    }
    .copyright{
        background-color: var(--sc-primary-color);
        color: var(--sc-white-color);
        padding: 20px 0;
        font-size: 12px;
        line-height: 1.5;
    }
    .powered-by{
        text-align: right;
    }
    .copyright a{
        color: var(--sc-white-color);
        text-decoration: none;
    }
    .copyright a:hover{
        color: var(--sc-white-color);
    }
    .footer-link{
        text-decoration: none;
        color: var(--sc-primary-color);
    }
    .footer-link:hover{
        color: var(--sc-secondary-color);
    }
    @media (max-width: 768px){
        .powered-by,
        .copyright{
            text-align: center;
        }
        .copyright{
            margin-bottom: 20px;
        }
    }
</style>

<?php if(!empty($footer_columns)){ ?>
    <div class="container-wrap fooeter-features" style='margin-bottom: 50px;'>
        <div class="container">
            <h3 class='section-title'><?php _e('Our Features', 'smartcommerce'); ?></h2>
            <div class="sc-flex-wrap">
                <?php foreach($footer_columns as $column){ ?>
                    <div class="flex-<?php echo $footer_columns_wrap; ?>" style='text-align: center;'>
                        <i class="fa fa-square-check footer-feature-icon" style='font-size: 28px; margin-bottom: 20px; color: var(--sc-primary-color);'></i>
                        <h5><?php echo $column['title']; ?></h5>
                        <p><?php echo $column['details']; ?></p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
<?php } ?>

<div class="container-wrap footer-widgets">
    <div class="container">
        <div class="sc-flex-wrap">
            <div class="flex-3">
                <div class="footer-logo">
                    <?php echo $logo ? "<a href='".home_url()."'>".wp_get_attachment_image($logo, 'full')."</a>" : "<a class='footer-link logo-text' href='".home_url()."'>{$name}</a>"; ?>
                    <div class="social-urls-wrap">
                        <?php if(!empty($facebook)){ ?>
                            <a class='footer-link' href="<?php echo $facebook; ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                        <?php } ?>
                        <?php if(!empty($instagram)){ ?>
                            <a class='footer-link' href="<?php echo $instagram; ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                        <?php } ?>
                        <?php if(!empty($youtube)){ ?>
                            <a class='footer-link' href="<?php echo $youtube; ?>" target="_blank"><i class="fab fa-youtube"></i></a>
                        <?php } ?>
                        <?php if(!empty($website)){ ?>
                            <a class='footer-link' href="<?php echo $website; ?>" target="_blank"><i class="fa fa-globe"></i></a>
                        <?php } ?>
                    </div>
                    
                </div>
            </div>
            <div class="flex-3">
                <?php if(!empty($address)){ ?>
                    <i class="fa fa-map-marker large-icon"></i> <span><?php echo $address; ?></span>
                <?php } ?>
            </div>
            <div class="flex-3">
                <?php if(!empty($whatsapp)){ ?>
                    <i class="fab fa-whatsapp large-icon"></i> <span><a class='footer-link' href="https://wa.me/+88<?php echo $whatsapp; ?>" target="_blank"><?php echo SmartCommerce::convertENtoBN($whatsapp); ?></a></span>
                <?php } ?>
            </div>
            <div class="flex-3">
                <?php if(!empty($phone)){ ?>
                    <i class="fa fa-phone large-icon"></i> <span><a class='footer-link' href="tel:<?php echo $phone; ?>" target="_blank"><?php echo SmartCommerce::convertENtoBN($phone); ?></a></span>
                <?php } ?>
            </div>
        </div>
    </div>
</div>



<div class="container-wrap copyright">
    <div class="container">
        <div class="sc-flex-wrap">
            <div class="flex-8">
                &copy; <?php _e('Copyright', 'smartcommerce'); ?> 
                <?php echo get_bloginfo('name'); ?> | 
                <a href='<?php echo home_url('/privacy-policy'); ?>' itemprop='url'>
                    <?php _e('Privacy Policy', 'smartcommerce'); ?>
                </a> | 
                <a href='<?php echo home_url('/terms-and-conditions'); ?>' itemprop='url'>
                    <?php _e('Terms and Conditions', 'smartcommerce'); ?>
                </a> | 
                <a href='<?php echo home_url('/return-policy'); ?>' itemprop='url'>
                    <?php _e('Return Policy', 'smartcommerce'); ?>
                </a>
            </div>
            <div class="flex-4">
                <div class='powered-by'>
                    <?php _e('Powered by', 'smartcommerce'); ?> <a href='https://smartcommercebd.com' target='_blank'>SmartCommerce</a>
                </div>
            </div>
        </div>
    </div>
</div>


