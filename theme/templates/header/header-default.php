<?php
use SmartCommerce\Settings;
use SmartCommerce\SmartCommerce;
use SmartCommerce\Form;
defined('ABSPATH') || exit;

?>

<?php $logo = wp_get_attachment_image(Settings::get('shop_logo'), 'full'); ?>

<div class="container-wrap">
    <div class="container">
        <div class="sc-flex-wrap">
            <div class="flex-12">

                <!-- Heaader Container -->
                <div class="header-container">
                    <!-- Logo Wrap -->
                    <div class="logo-wrap">
                        <a href='<?php echo home_url(); ?>'>
                            <?php if(!empty($logo)) : ?>
                                <div class="logo-img"><?php echo $logo; ?></div>
                            <?php else : ?>
                                <div class="logo-text"><?php echo Settings::get('shop_name'); ?></div>
                            <?php endif; ?>
                        </a>
                    </div>

                    <!-- Search Wrap -->
                    <div class="search-wrap desktop-only">
                        <form role="search" method="get" class="search-form header-search-form" action="<?php echo home_url('/products/'); ?>">
                            <input required="required" type="search" class="search-field" placeholder="<?php _e('Search...', 'smartcommerce'); ?>" value="<?php echo sanitize_text_field($_REQUEST['search'] ?? ''); ?>" name="search">
                            <button type="submit" class="search-submit"><i class="fa fa-search"></i></button>
                        </form>
                    </div>

                    <!-- Header Right -->
                    <div class="header-action-wrap">

                        <!-- Dashboard --> 
                        <?php if(current_user_can('manage_options') || current_user_can('sc_admin')) : ?>
                            <a href='<?php echo home_url('/dashboard'); ?>'><?php echo SmartCommerce::getIcon('dashboard'); ?></a>
                        <?php endif; ?>

                        <!-- Cart -->
                        <!-- <a href='javscript:void(0);'><?php echo SmartCommerce::getIcon('cart'); ?></a> -->

                        <!-- Login -->
                        <?php if(!is_user_logged_in()) : ?>
                            <a href='javscript:void(0);' class='sc-ajax-link' data-redirect_to='<?php echo SmartCommerce::getCurrentUrl(); ?>?uid=<?php echo uniqid(); ?>' data-success_callback='showPopupOnCallback' data-ajax_action='showLoginForm'><?php echo SmartCommerce::getIcon('lock'); ?></a>
                        <?php else : ?>
                            <a href='<?php echo site_url('/profile'); ?>'><?php echo SmartCommerce::getIcon('user'); ?></a>
                            <a href='<?php echo wp_logout_url(home_url()); ?>' class='confirmLogout'><?php echo SmartCommerce::getIcon('sign-out'); ?></a>
                        <?php endif; ?>
                        
                        <span class="mobile-onlly">
                            <a href='javscript:void(0);'><?php echo SmartCommerce::getIcon('bars'); ?></a>
                        </span>
                    </div>
                </div>

                <?php $welcome_message = trim(Settings::get('welcome_message')); ?>
                <?php if(!empty($welcome_message)) : ?>
                    <!-- Welcome Message -->
                    <div class="welcome-message" style="width: 100%; max-width: <?php echo Settings::get('container_width', 1024); ?>px;">
                        <p class="welcome-message-text"><?php echo $welcome_message; ?></p>
                        <?php echo SmartCommerce::embedScript(SMART_COMMERCE_THEME_JS_URL . 'jquery.marquee.min.js'); ?>
                        <script type="text/javascript">
                            jQuery(window).on('load', function(){
                                setTimeout(function(){
                                    jQuery('.welcome-message-text').marquee({
                                        speed: 75,
                                    });
                                }, 100);
                            });
                        </script>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>