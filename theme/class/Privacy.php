<?php 
defined('ABSPATH') || exit;
use SmartCommerce\Settings;
class ThemePrivacy
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
        add_shortcode('sc_privacy_policy', [$this, 'privacy_policy']);
    }

    public function privacy_policy()
    {
        $date = date('d F, Y', strtotime(get_option('sc_installation_date')));
        $text = file_get_contents(SMART_COMMERCE_TEMPLATE_DIR . 'text/privacy-policy.txt');
        $keywords = array(
            '{company}' => Settings::get('shop_name'),
            '{website}' => home_url(),
            '{email}' => Settings::get('shop_email'),
            '{phone}' => Settings::get('shop_phone'),
            '{address}' => Settings::get('shop_address'),
            '{date}' => $date,
            '{last_updated}' => $date,
        );
        $text = str_replace(array_keys($keywords), array_values($keywords), $text);
        return $text;
    }
}

ThemePrivacy::instance();