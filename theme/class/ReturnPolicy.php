<?php 
defined('ABSPATH') || exit;
use SmartCommerce\Settings;

class ThemeReturnPolicy
{
    private static $_instance; 
    public $template = 'footer-default';

    public static function instance()
    {
        if(self::$_instance) return self::$_instance;
        return self::$_instance = new self();
    }

    public function __construct()
    {
        add_shortcode('sc_return_policy', [$this, 'return_policy']);
    }

    public function return_policy()
    {
        $date = date('d F, Y', strtotime(get_option('sc_installation_date')));
        $text = file_get_contents(SMART_COMMERCE_TEMPLATE_DIR . 'text/return-policy.txt');
        $keywords = array(
            '{company}' => Settings::get('shop_name'),
            '{website}' => home_url(),
            '{email}' => Settings::get('shop_email'),
            '{phone}' => Settings::get('shop_phone'),
            '{address}' => Settings::get('shop_address'),
            '{date}' => $date,
            '{last_updated}' => $date,
            '{minimum_age}' => 14,
            '{currency}' => 'Bangladeshi Taka (BDT)',
            '{payment_methods}' => 'Bank Transfer, Cash on Delivery, Mobile Banking',
            '{country}' => 'Bangladesh',
            '{jurisdiction}' => 'Bangladesh',
            '{arbitration/mediation/court}' => 'Judicial Courts',
            '{return_address}' => Settings::get('shop_address'),
            '{refund_timeframe}' => '7 days',
            '{refundable_or_nonrefundable}' => 'Non-refundable',
        );
        $text = str_replace(array_keys($keywords), array_values($keywords), $text);
        return $text;
    }
}

ThemeReturnPolicy::instance();