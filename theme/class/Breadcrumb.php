<?php 
defined('ABSPATH') || exit;

class ThemeBreadcrumb
{
    private static $_instance; 
    public $template = 'breadcrumb-default';

    public static function instance()
    {
        if(self::$_instance) return self::$_instance;
        return self::$_instance = new self();
    }

    public function __construct()
    {
        add_shortcode('sc_breadcrumb', [$this, 'breadcrumb']);
    }

    public function breadcrumb()
    {
        ob_start();
        include SMART_COMMERCE_THEME_DIR . 'templates/breadcrumb/' . $this->template . '.php';
        return ob_get_clean();
    }

    public static function getItems()
    {
        
        global $post;

        $list = array();
        $list['home'] = array(
            'url' => home_url(),
            'title' => "<i class='fa fa-home'></i>",
        );
        if(is_singular()){
            if($post->post_type == 'sc_product'){
                $main_category = get_post_meta($post->ID, 'main_category', true);
                if($main_category){
                    $term = get_term_by('id', $main_category, 'sc_product_category');
                    if($term){
                        $list['category'] = array(
                            'url' => get_term_link($term),
                            'title' => __($term->name, 'smartcommerce'),
                        );
                    }
                } else {
                    // Get all categories
                    $categories = get_the_terms($post->ID, 'sc_product_category');
                    if($categories){
                        $list['category'] = array(
                            'url' => get_term_link($categories[0]),
                            'title' => __($categories[0]->name, 'smartcommerce'),
                        );
                    }
                }
            }
        } else if(is_archive()){
            $term = get_queried_object();
            if($term->parent != 0) {
                $parent = get_term_by('id', $term->parent, $term->taxonomy);
                if($parent){
                    $list['category'] = array(
                        'url' => get_term_link($parent),
                        'title' => __($parent->name, 'smartcommerce'),
                    );
                }
            }
            
        }

        $current_title = '';
        if(is_singular()){
            $current_title = get_the_title();
        } else if(is_archive()){
            $current_title = get_the_archive_title();
        } else if(is_search()){
            $current_title = __('Search Results', 'smartcommerce');
        } else {
            $current_title = __('404 Not Found', 'smartcommerce');
        }

        $list['current'] = array(
            'url' => '',
            'title' => __($current_title, 'smartcommerce'),
        );

        $breadcrumb = array_map(function($list, $key){
            return $key == 'current' ? "<span class='current'>{$list['title']}</span>" : '<a href="' . $list['url'] . '">' . $list['title'] . '</a>';
        }, $list, array_keys($list));
        return $breadcrumb;
    }
}

ThemeBreadcrumb::instance();