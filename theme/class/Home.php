<?php 
defined('ABSPATH') || exit;
use SmartCommerce\Product;

class ThemeHome
{
    public $id;
    private static $_instance; 
    public $template = 'home-default';

    public static function instance()
    {
        if(self::$_instance) return self::$_instance;
        return self::$_instance = new self();
    }

    public function __construct($id=0)
    {
        $this->id = $id;
        add_shortcode('sc_home', [$this, 'home']);
    }

    public function wpInit()
    {
        add_filter('sctheme_filter_show_page_title', [$this, 'showPageTitle'], 10);
    }

    public function home()
    {
        ob_start();
        include SMART_COMMERCE_THEME_DIR . 'templates/home/' . $this->template . '.php';
        return ob_get_clean();
    }

    public function showPageTitle($post)
    {
        return false;
    }
}

$th = ThemeHome::instance();
$th->wpInit();