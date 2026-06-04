<?php
namespace SmartCommerce;
use SmartCommerce\Order;
use SmartCommerce\Tracking;

defined('ABSPATH') || exit;

class ThemeTracking
{
    public $id;
    public $order;
    private static $_instance; 
    public $template = 'tracking-default';

    /**
     * 
     * @return ThemeTracking
     */

    public static function instance()
    {
        if(is_null(self::$_instance)) self::$_instance = new self();
        return self::$_instance;
    }

    /**
     * 
     * @param int $id
     */
    public function __construct($id=0)
    {
        $this->id = $id;
        if(!empty($this->id)){
            $order_id = Tracking::searchOrderId($this->id);
            if($order_id){
                $this->order = new Order($order_id);
            }
        }
        add_shortcode('sc_tracking', [$this, 'tracking']);
    }

    public function wpInit()
    {
    }

    public function tracking($atts=[])
    {
        $tid = sanitize_text_field($_REQUEST['tid'] ?? '');
        if(empty($tid)) return __('Tracking ID is required', 'smartcommerce');
        $order_id = Tracking::searchOrderId($tid);
        if($order_id){
            $this->order = new Order($order_id);
        }
        $name = isset($this->order->metadata['delivery_name'][0]) ? $this->order->metadata['delivery_name'][0] : '';

        ob_start();
        ?>
        <style>
            .sc-tracking-text{
                font-size: 24px;
                line-height: 1.5;
                width: 100%;
                text-align: center;
                padding: 50px;
                box-sizing: border-box;
            }
            .sc-tracking-name{
                font-weight: bold;
                color: var(--sc-primary-color);
                text-align: center;
            }
        </style>
        <div class='sc-tracking-text'>
            <?php $locale = get_locale(); ?>
            <?php if($locale == 'en_US'){ ?>
                Dear <span class='sc-tracking-name'><?php echo $name; ?></span>! 
                <br>We got your order! Our team will contact you soon to confirm the order and ship the parcel. 

                <br><br>
                We appreciate your patience and understanding.

                <br><br>
                Thank you for choosing us!

                <br><br>
                Best regards,
                <br>
                <?php echo get_bloginfo('name'); ?> Team

            <?php } else if($locale == 'bn_BD'){ ?>
                প্রিয় <span class='sc-tracking-name'><?php echo $name; ?></span>! 
                <br>আমরা আপনার অর্ডার পেয়েছি! আমাদের টীম শীঘ্রই আপনার অর্ডারটি নিশ্চিত করবে এবং প্যার্সেলটি পাঠাবে। 

                <br><br>
                আমরা আপনার সহয়তা এবং ধৈর্য্যের জন্য ধন্যবাদ জানাচ্ছি।

                <br><br>
                আমাদের সাথে থাকার জন্য ধন্যবাদ!

                <br><br>
                ধন্যবাদান্তে,
                <br>
                <?php echo get_bloginfo('name'); ?> টীম
            <?php } ?>

            <br><br>
            <style>.sc-invoice-address-info{text-align: left !important;}</style>
            <h3><?php _e('Invoice', 'smartcommerce'); ?></h3>
            <?php echo $this->order->getInvoice(); ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

$tp = ThemeTracking::instance();
$tp->wpInit();