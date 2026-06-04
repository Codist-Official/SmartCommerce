<?php 
defined('ABSPATH') || exit;

class ThemeCategory
{
    private static $_instance; 
    public $template = 'category-default';

    public static function instance()
    {
        if(self::$_instance) return self::$_instance;
        return self::$_instance = new self();
    }

    public function __construct()
    {
        add_shortcode('sc_category', [$this, 'category']);
    }

    public function category()
    {
        ob_start();
        include SMART_COMMERCE_THEME_DIR . 'templates/category/' . $this->template . '.php';
        return ob_get_clean();
    }
}

ThemeCategory::instance();