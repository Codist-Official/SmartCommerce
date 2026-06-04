<?php 
defined('ABSPATH') || exit;

class ThemeHeader
{
    private static $_instance; 
    public $template = 'header-default';

    public static function instance()
    {
        if(self::$_instance) return self::$_instance;
        return self::$_instance = new self();
    }

    public function __construct()
    {
        add_shortcode('sc_header', [$this, 'header']);
    }

    public function header()
    {
        ob_start();
        include SMART_COMMERCE_THEME_DIR . 'templates/header/' . $this->template . '.php';
        return ob_get_clean();
    }
}

ThemeHeader::instance();