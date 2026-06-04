<?php 
defined('ABSPATH') || exit;

class ThemeFooter
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
        add_shortcode('sc_footer', [$this, 'footer']);
    }

    public function footer()
    {
        ob_start();
        include SMART_COMMERCE_THEME_DIR . 'templates/footer/' . $this->template . '.php';
        return ob_get_clean();
    }
}

ThemeFooter::instance();