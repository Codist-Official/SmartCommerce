<?php 
use SmartCommerce\Product;

defined('ABSPATH') || exit;

class ThemeProduct
{
    public $id;
    public $product;
    private static $_instance; 
    public $template = 'product-default';

    public static function instance()
    {
        if(self::$_instance) return self::$_instance;
        return self::$_instance = new self();
    }

    public function __construct($id=0)
    {
        $this->id = $id;
        if($this->id) $this->product = new Product($this->id);
    }

    public function wpInit()
    {
        add_shortcode('sc_product', [$this, 'product']);
        add_filter('sctheme_filter_show_page_title', [$this, 'showPageTitle'], 10);
    }

    public function product($atts=[])
    {
        $params = shortcode_atts(array(
            'id' => 0,
        ), $atts);
        $this->id = $params['id'];
        if($this->id) $this->product = new Product($this->id);
        ob_start();
        include SMART_COMMERCE_THEME_DIR . 'templates/product/' . $this->template . '.php';
        return ob_get_clean();
    }

    public function showPageTitle()
    {
        global $post;
        if( !is_object($post) || !isset($post->post_type) ) return true;
        return $post->post_type != 'sc_product';
    }
}

$tp = ThemeProduct::instance();
$tp->wpInit();