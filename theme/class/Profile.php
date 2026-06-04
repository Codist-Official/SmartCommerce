<?php 
use SmartCommerce\User;

defined('ABSPATH') || exit;

class ThemeProfile
{
    public $id;
    public $product;
    private static $_instance; 
    public $template = 'profile-default';

    public static function instance()
    {
        if(self::$_instance) return self::$_instance;
        return self::$_instance = new self();
    }

    public function __construct($id=0)
    {
        $this->id = $id;
    }

    public function wpInit()
    {
        add_shortcode('sc_profile', [$this, 'profile']);
    }

    public function profile($atts=[])
    {
        $user = new User(get_current_user_id());
        return $user->getEditForm();
    }

    public function showPageTitle()
    {
        global $post;
        if( !is_object($post) || !isset($post->post_type) ) return true;
        return $post->post_type != 'sc_product';
    }
}

$tp = ThemeProfile::instance();
$tp->wpInit();