<?php
namespace SmartCommerce;
// use Picqer\Barcode\BarcodeGeneratorPNG;

/**
 * Plugin Name: SmartCommerce 
 * Author: Nur Hossain
 * Version: 1.0.1
 * Description: A smart commerce plugin for WordPress.
 * Text Domain: smartcommerce
 * Domain Path: /languages
 */

 defined('ABSPATH') || die();

 if(!defined('SMART_COMMERCE_DIR')) define( 'SMART_COMMERCE_DIR', plugin_dir_path(__FILE__));
 if(!defined('SMART_COMMERCE_LIB_DIR')) define( 'SMART_COMMERCE_LIB_DIR', SMART_COMMERCE_DIR . 'includes/libs/');
 if(!defined('SMART_COMMERCE_INCLUDES_DIR')) define( 'SMART_COMMERCE_INCLUDES_DIR', SMART_COMMERCE_DIR . 'includes/');
 if(!defined('SMART_COMMERCE_CLASS_DIR')) define( 'SMART_COMMERCE_CLASS_DIR', SMART_COMMERCE_INCLUDES_DIR . 'class/');
 if(!defined('SMART_COMMERCE_ADMIN_DIR')) define( 'SMART_COMMERCE_ADMIN_DIR', SMART_COMMERCE_INCLUDES_DIR . 'admin/');
 if(!defined('SMART_COMMERCE_TEMPLATE_DIR')) define( 'SMART_COMMERCE_TEMPLATE_DIR', SMART_COMMERCE_DIR . 'templates/');
 if(!defined('SMART_COMMERCE_LANG_DIR')) define( 'SMART_COMMERCE_LANG_DIR', SMART_COMMERCE_DIR . 'languages/');
 if(!defined('SMART_COMMERCE_THEME_DIR')) define( 'SMART_COMMERCE_THEME_DIR', SMART_COMMERCE_DIR . 'theme/');
 if(!defined('SMART_COMMERCE_THEME_CLASS_DIR')) define( 'SMART_COMMERCE_THEME_CLASS_DIR', SMART_COMMERCE_THEME_DIR . 'class/');


 if(!defined('SMART_COMMERCE_URL')) define( 'SMART_COMMERCE_URL', plugin_dir_url(__FILE__));
 if(!defined('SMART_COMMERCE_ASSETS_URL')) define( 'SMART_COMMERCE_ASSETS_URL', SMART_COMMERCE_URL . 'assets/');
 if(!defined('SMART_COMMERCE_CSS_URL')) define( 'SMART_COMMERCE_CSS_URL', SMART_COMMERCE_ASSETS_URL . 'css/');
 if(!defined('SMART_COMMERCE_JS_URL')) define( 'SMART_COMMERCE_JS_URL', SMART_COMMERCE_ASSETS_URL . 'js/');
 if(!defined('SMART_COMMERCE_IMG_URL')) define( 'SMART_COMMERCE_IMG_URL', SMART_COMMERCE_ASSETS_URL . 'img/');
 if(!defined('SMART_COMMERCE_JSON_URL')) define( 'SMART_COMMERCE_JSON_URL', SMART_COMMERCE_ASSETS_URL . 'json/');

 if(!defined('SMART_COMMERCE_THEME_URL')) define( 'SMART_COMMERCE_THEME_URL', SMART_COMMERCE_URL . 'theme/');
 if(!defined('SMART_COMMERCE_THEME_ASSETS_URL')) define( 'SMART_COMMERCE_THEME_ASSETS_URL', SMART_COMMERCE_THEME_URL . 'assets/');
 if(!defined('SMART_COMMERCE_THEME_CSS_URL')) define( 'SMART_COMMERCE_THEME_CSS_URL', SMART_COMMERCE_THEME_ASSETS_URL . 'css/');
 if(!defined('SMART_COMMERCE_THEME_JS_URL')) define( 'SMART_COMMERCE_THEME_JS_URL', SMART_COMMERCE_THEME_ASSETS_URL . 'js/');
 
 if(!defined('SMART_COMMERCE_TEXT_DOMAIN')) define( 'SMART_COMMERCE_TEXT_DOMAIN', 'smartcommerce');
 if(!defined('SMART_COMMERCE_VERSION')) define( 'SMART_COMMERCE_VERSION', '1.0.0');
 if(!defined('SMART_COMMERCE_MIN_PHP_VERSION')) define( 'SMART_COMMERCE_MIN_PHP_VERSION', '7.0');

 if(!defined('SMART_COMMERCE_PRODUCT_THUMBNAIL')) define( 'SMART_COMMERCE_PRODUCT_THUMBNAIL', SMART_COMMERCE_IMG_URL . 'image-placeholder.png');


require SMART_COMMERCE_LIB_DIR . 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/Codist-Official/smartcommerce/',
	__FILE__,
	'smartcommerce'
); 

class SmartCommerce { 

    /**
     * Instance 
     */
    private static $_instance; 


    /**
     * Initialize instance 
     * 
     * @return self 
     */
    public static function instance()
    {
        if( self::$_instance == null ) self::$_instance = new self();
        return self::$_instance;
    }


    /**
     * Constructor 
     * 
     * @return void 
     * @since 1.0.0
     */
    public function __construct() 
    {
       
    }

    /**
     * WP init 
     * 
     * @return void 
     * 
     * @since 1.0
     * @access public 
     */
    public function wpInit()
    {
        $this->includeClassFiles();

        // disable top admin bar 
        add_filter('show_admin_bar', function(){
            return current_user_can('administrator');
        });

        // Close comments on the front-end
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);
        
        // Disable xmlrpc 
        add_filter('xmlrpc_enabled', '__return_false');
        add_filter('xmlrpc_methods', function($methods) {
            unset($methods['pingback.ping']);
            return $methods;
        });

        // disable wp-admin access for all but admin 
        add_action('admin_init', [$this, 'disableAdminAccess']);

        // register css & js 
        add_action('wp_enqueue_scripts', [$this, 'registerCssJs']);

        // load textdomain 
        add_action('plugins_loaded', [$this, 'loadTextDomain']);

        // wp head 
        add_action('wp_head', [$this, 'wpHead'], 2);

        // Footer Content 
        add_action('wp_footer', [$this, 'wpFooter']);

        // register custom image sizes 
        add_action('init', [$this, 'registerImageSizes']);

        // create pages after plugin activate for the first time  
        register_activation_hook(__FILE__, [$this, 'actionsOnActivation']);

        // Custom rewrite rules for custom permalink
        add_filter( 'init', [ $this, 'rewriteRules'] );

        // Change product structure
        add_filter( 'post_type_link', [ $this, 'modifyPermalink' ], 10, 4 );

        // Limit max image width
        add_filter( 'big_image_size_threshold', function( $threshold ) {
            return 1920;
        });

        // Set JPEG quality
        add_filter( 'jpeg_quality', function() { return Settings::get('image_quality', 60); });
        add_filter( 'wp_editor_set_quality', function( $quality ) { return Settings::get('image_quality', 60); });
    }


    /**
     * Include class files 
     * 
     * @return void 
     * 
     * @since 1.0.0
     * @access private
     */
    private function includeClassFiles() 
    {
        $first_loaded = ['Post.php'];
        foreach($first_loaded as $file){
            require_once SMART_COMMERCE_CLASS_DIR . $file;
        }

        // include all class files 
        foreach( glob(SMART_COMMERCE_CLASS_DIR . '*.php') as $file ){
            foreach($first_loaded as $first_file){
                if(str_contains($file, $first_file)) continue;
            }
            require_once $file;
        }

        // include admin files 
        foreach( glob(SMART_COMMERCE_ADMIN_DIR . '*.php') as $file ){
            require_once $file;
        }

        // include theme files 
        foreach( glob(SMART_COMMERCE_THEME_CLASS_DIR . '*.php') as $file ){
            require_once $file;
        }
    }

    /**
     * Disable Admin Access 
     * 
     * @return void 
     * @since 1.0.0
     */
    public function disableAdminAccess()
    {
        if ( is_admin() && !current_user_can( 'administrator' ) && !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            wp_redirect( home_url() );
            exit;
        }
    }

        /**
     * Custom rewrite rules for custom permalink and others
     *
     * @return void
     *
     * @since 1.0
     * @access public
     */
    public function rewriteRules()
    {
        // For product/book
        add_rewrite_rule( '^product/([0-9]+)/?', 'index.php?post_type=sc_product&p=$matches[1]', 'top');

    }

    /**
     * Modify post link
     *
     * @return string
     *
     * @since 1.0
     * @access public
     */
    public function modifyPermalink( $permalink, $post, $leavename, $sample )
    {

        if ( $post->post_type === 'sc_product' ){ 
            return home_url('/product/' . $post->ID ); 
        }
        return $permalink;

    }

    /**
     * Register CSS & JS 
     * 
     * @return void 
     * 
     * @since 1.0.0
     * @access public 
     */
    public function registerCssJs()
    {
        wp_enqueue_script('jquery');
        wp_localize_script('jquery', 'smartcommerce', [
            'ajax_url' => admin_url('admin-ajax.php'),
            '_wpnonce' => wp_create_nonce('smartcommerce'),
            'site_url' => home_url(),
            'page_id' => get_the_ID(),
            'sms_rate' => Settings::get('sms_rate', 0.4),
        ]);

        wp_enqueue_style('smartcommerce-admin', SMART_COMMERCE_CSS_URL . 'style.css', [], rand(1,100000));

        // register fontawesome 
        wp_register_style('sc-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css', [], rand(1,10000));
        wp_register_style('sc-google-fonts', str_replace(' ', '+', 'https://fonts.googleapis.com/css2?family='. Settings::get('font_family').'&display=swap'), [], rand(1,10000));

        // register js files 
        wp_register_script('sc-script', SMART_COMMERCE_JS_URL . 'script.min.js', ['jquery'], rand(1,1000000), true);
 
        
        // Lightslider
        wp_register_style('sc-lightslider', 'https://cdnjs.cloudflare.com/ajax/libs/lightslider/1.1.6/css/lightslider.min.css', [], rand(1,10000));
        wp_register_script('sc-lightslider', 'https://cdnjs.cloudflare.com/ajax/libs/lightslider/1.1.6/js/lightslider.min.js', ['jquery'], rand(1,1000000), true);

        // for admins only 
        if(str_contains(self::getCurrentUrl(), 'dashboard') || is_home() || is_front_page()){
            if(str_contains(self::getCurrentUrl(), 'submenu=publish') || str_contains(self::getCurrentUrl(), 'submenu=edit')){
                wp_enqueue_editor();
            }
        }
    }

    /**
     * Register Image Sizes 
     * 
     * @return void 
     * @since 1.0.0
     * @access public 
     */
    public function registerImageSizes()
    {
        add_image_size('sc-thumbnail', 75, 75, true);
    }

    /**
     * Load textdomain 
     * 
     * @return void 
     * @since 1.0.0
     */
    public function loadTextDomain()
    {

        // Load Text Domain 
        load_plugin_textdomain( 'smartcommerce', false, basename(dirname(__FILE__)) . '/languages');

        if ( is_admin() ) {
            add_action( 'admin_notices', function() {
                if ( is_textdomain_loaded( 'smartcommerce' ) ) {
                    echo '<div class="notice notice-success"><p>✅ Text domain loaded!</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>❌ Text domain NOT loaded!</p></div>';
                }
            });
        }
        
    }

    /**
     * Get icon 
     * 
     * @param string name 
     * @pararm int size 
     * @return string 
     * 
     * @since 1.0.0
     * @access public 
     */
    public static function getIcon($name, $size = 12)
    {
        $icons = array(
            'product' => 'fa-box',
            'category' => 'fa-folder',
            'tag' => 'fa-tag',
            'attribute' => 'fa-cog',
            'order' => 'fa-shopping-cart',
            'edit' => 'fa-pencil',
            'delete' => 'fa-trash',
            'view' => 'fa-eye',
            'add' => 'fa-plus',
            'save' => 'fa-save',
            'cancel' => 'fa-times',
            'search' => 'fa-search',
            'filter' => 'fa-filter',
            'sort' => 'fa-sort',
            'asc' => 'fa-sort-up',
            'desc' => 'fa-sort-down',
            'up' => 'fa-arrow-up',
            'down' => 'fa-arrow-down',
            'left' => 'fa-arrow-left',
            'right' => 'fa-arrow-right',
            'up-right' => 'fa-arrow-up-right',
            'down-right' => 'fa-arrow-down-right',
            'up-left' => 'fa-arrow-up-left',
            'down-left' => 'fa-arrow-down-left',
            'up-right' => 'fa-arrow-up-right',
            'down-right' => 'fa-arrow-down-right',
            'up-left' => 'fa-arrow-up-left',
            'down-left' => 'fa-arrow-down-left',
            'up-right' => 'fa-arrow-up-right',
            'down-right' => 'fa-arrow-down-right',
            'up-left' => 'fa-arrow-up-left',
            'down-left' => 'fa-arrow-down-left',
            'up-right' => 'fa-arrow-up-right',
            'down-right' => 'fa-arrow-down-right',
            'up-left' => 'fa-arrow-up-left',
            'down-left' => 'fa-arrow-down-left',
            'up-right' => 'fa-arrow-up-right',
            'down-right' => 'fa-arrow-down-right',
            'up-left' => 'fa-arrow-up-left',
            'down-left' => 'fa-arrow-down-left',
            'up-right' => 'fa-arrow-up-right',
            'down-right' => 'fa-arrow-down-right',
            'up-left' => 'fa-arrow-up-left',
            'down-left' => 'fa-arrow-down-left',
            'up-right' => 'fa-arrow-up-right',
            'down-right' => 'fa-arrow-down-right',
            'up-left' => 'fa-arrow-up-left',
            'down-left' => 'fa-arrow-down-left',
            'cart' => 'fa-shopping-cart',
            'lock' => 'fa-lock',
            'unlock' => 'fa-unlock',
            'user' => 'fa-user',
            'heart' => 'fa-heart',
            'star' => 'fa-star',
            'star-half' => 'fa-star-half',
            'star-half-alt' => 'fa-star-half-alt',
        );
        $icon = isset($icons[$name]) ? $icons[$name] : 'fa-'.$name;
        $padding = 5;
        $width = $size + ($padding * 2);
        ob_start();
        ?>
        <style>
            .sc-icon-wrap{
                width: <?php echo $width; ?>px;
                height: <?php echo $width; ?>px;
                line-height: <?php echo $width; ?>px;
            }
            .sc-icon-wrap i{
                font-size: <?php echo $size; ?>px;
            }
        </style>
        <span class="sc-icon-wrap">
            <i class="fa <?php echo $icon; ?>" style="font-size: <?php echo $size; ?>px;"></i>
        </span>
        <?php
        $html = ob_get_clean();
        return $html;
    }

    /**
     * GET CURRENT URL 
     * 
     * @return string 
     * @since 1.0.0
     * @access public 
     * @static 
     */
    public static function getCurrentUrl()
    {
        
        $protocol = is_ssl() ? 'https://' : 'http://';
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    

    /**
     * Display head html 
     * 
     * @return string 
     * 
     * @since 1.0.0
     * @access public 
     * @static 
     */
    public function wpHead()
    {
        ob_start();
        ?>
        <style>
            :root{
                --sc-primary-color: <?php echo Settings::get('primary_color', '#4C5C9E', true ); ?> !important;
                --sc-secondary-color: <?php echo Settings::get('secondary_color', '#B48EAD', true ); ?> !important;
                --sc-accent-color: <?php echo Settings::get('accent_color', '#EBCB8B', true ); ?> !important;
                --sc-white-color: white;
                --sc-primary-bg-color: #F5F5F2;
                --sc-secondary-bg-color: <?php echo Settings::get('secondary_color', '#B48EAD', true ); ?>1A;
                --sc-accent-color: #EBCB8B;
                --sc-text-color: <?php echo Settings::get('text_color', '#2E2E38', true ); ?> !important;
                --sc-flex-gap: 20px;
                --sc-base-col: calc(100% / 12);
                --sc-border-color: #333;
                --sc-img-holder-size: 50px;
                --sc-danger-color: #ff0000;
                --sc-danger-bg-color: #ffdcdc;
                --sc-success-color: #008000;
                --sc-success-bg-color: #cbfacb;
                --sc-warning-color: #ffa500;
                --sc-warning-bg-color: #fff2e3;
                --sc-info-color: #0000ff;
                --sc-info-bg-color: #e3e3ff;
                --sc-question-color: #808080;
                --sc-default-color: #000000;
                --sc-base-font-size: <?php echo Settings::get('font_size', 14); ?>px;
                --sc-mobile-base-font-size: 12px;
                --sc-small-font-size: 12px;
                --sc-medium-font-size: 16px;
                --sc-big-font-size: 20px;
                --sc-h3-font-size: 22px;
                --sc-h2-font-size: 30px;
                --sc-h1-font-size: 40px;
                --sc-grey-color: #dfdfdf;
                --sc-border-radius: <?php echo Settings::get('border_radius', 4); ?>px;
            }
            a,div,span,p,table,form,input,textarea,select,button,h1,h2,h3,h4,h5,h6{
                font-family: <?php echo Settings::get('font_family', 'Tiro Bangla', true ); ?> !important;
            }

            h1{ font-size: <?php echo Settings::get('h1_font_size', 48); ?>px; }
            h2{ font-size: <?php echo Settings::get('h2_font_size', 36); ?>px; }
            h3{ font-size: <?php echo Settings::get('h3_font_size', 24); ?>px; }
            h4{ font-size: <?php echo Settings::get('h4_font_size', 20); ?>px; }
            h5{ font-size: <?php echo Settings::get('h5_font_size', 18); ?>px; }
            h6{ font-size: <?php echo Settings::get('h6_font_size', 14); ?>px; }

            h1,h2,h3,h4,h5,h6{
                font-weight: bold;
                line-height: 1.25em;
            }
            .container-wrap{
                width: 100%;
                margin: 0 auto;
            }
            .container-wrap .container{
                width: 100%;
                max-width: <?php echo Settings::get('container_width', 1024); ?>px;
                margin: 0 auto;
            }
            @media screen and (max-width: 768px){
                <?php $mobile_scale = Settings::get('mobile_heading_scale', 0.65); ?>
                h1{ font-size: <?php echo Settings::get('h1_font_size', 48) * $mobile_scale; ?>px; }
                h2{ font-size: <?php echo Settings::get('h2_font_size', 36) * $mobile_scale; ?>px; }
                h3{ font-size: <?php echo Settings::get('h3_font_size', 24) * $mobile_scale; ?>px; }
                h4{ font-size: <?php echo Settings::get('h4_font_size', 20) * $mobile_scale; ?>px; }
                h5{ font-size: <?php echo Settings::get('h5_font_size', 18) * $mobile_scale; ?>px; }
                h6{ font-size: <?php echo Settings::get('h6_font_size', 14) * $mobile_scale; ?>px; }
            }

            <?php echo stripcslashes(Settings::get('custom_css', '')); ?>
        </style>
        <script>
            <?php echo stripcslashes(Settings::get('custom_js', '')); ?>
        </script>
        <?php echo Settings::get('custom_html', ''); ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        <!-- Google Analytics --> 
        <?php $google_analytics = trim(Settings::get('google_analytics_id')); ?>
        <?php if(!empty($google_analytics)): ?>
            <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $google_analytics; ?>"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());

                gtag('config', '<?php echo $google_analytics; ?>');
            </script>
        <?php endif; ?>


        <!-- Google Tag Manager -->
        <?php $google_tag_manager = trim(Settings::get('google_tag_id')); ?>
        <?php if(!empty($google_tag_manager)): ?>
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window, document, 'script', 'dataLayer', '<?php echo $google_tag_manager; ?>');</script>
        <?php endif; ?>
        <!-- End Google Tag Manager -->

        <?php
        echo $this->generateMeta();
        echo $this->generateSchema();
        echo ob_get_clean();
    }

    public static function getYoutubeVideoPlayer($url)
    {
        if(empty($url)) return '';
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        $video_id = $params['v'] ?? '';
        $thumb = !empty($video_id) ? 'https://img.youtube.com/vi/' . $video_id . '/0.jpg' : '';
        if(empty($thumb)) return '';
        ob_start();
        ?>
        <a class="sc-youtube-thumbnail" href="javascript:void(0)" data-video-id="<?php echo $video_id; ?>" data-video-url="<?php echo $url; ?>" >
            <img src="<?php echo $thumb; ?>" alt="YouTube Thumbnail">
        </a>
        <?php
        return ob_get_clean();
    }

    /**
     * Generate meta tags 
     * 
     * @return string 
     * 
     * @since 1.0.0
     * @access public 
     */
    public function generateMeta($id=null)
    {
        global $post;
        if(is_numeric($id)) $post = get_post($id);
        elseif(is_object($id) && $id instanceof \WP_Post) $post = $id;
        elseif(is_null($id)) $post = get_post();

        if(!$post || $post->post_type != 'sc_product') return '';

        $title = get_post_meta($post->ID, 'meta_title', true);
        if(empty($title)) $title = $post->post_title . ' - ' . Settings::get('shop_name');
        $description = get_post_meta($post->ID, 'meta_description', true);
        if(empty($description)) $description = $post->post_excerpt;
        $keywords = get_post_meta($post->ID, 'meta_keywords', true);
        $image = has_post_thumbnail($post->ID) ? get_the_post_thumbnail_url($post->ID, 'full') : '';
        $canonical = get_post_meta($post->ID, 'canonical_url', true);
        if(empty($canonical)) $canonical = self::getCurrentUrl();

        $data = array(
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'image' => $image,
            'canonical' => $canonical,
            'type' => 'product',
            'site_name' => get_bloginfo('name'),
            'twitter' => '@' . get_bloginfo('name'),
        );

        ob_start();
        ?>

        <!-- Basic Meta -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta name="description" content="<?php echo esc_attr($data['description']); ?>">

        <!-- Canonical -->
        <link rel="canonical" href="<?php echo esc_url($data['canonical']); ?>" />

        <!-- Open Graph / Facebook / LinkedIn -->
        <meta property="og:type" content="<?php echo esc_attr($data['type']); ?>">
        <meta property="og:title" content="<?php echo esc_attr($data['title']); ?>">
        <meta property="og:description" content="<?php echo esc_attr($data['description']); ?>">
        <meta property="og:url" content="<?php echo esc_url($data['canonical']); ?>">
        <meta property="og:image" content="<?php echo esc_url($data['image']); ?>">
        <meta property="og:site_name" content="<?php echo esc_attr($data['site_name']); ?>">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?php echo esc_attr($data['title']); ?>">
        <meta name="twitter:description" content="<?php echo esc_attr($data['description']); ?>">
        <meta name="twitter:image" content="<?php echo esc_url($data['image']); ?>">
        <meta name="twitter:site" content="<?php echo esc_attr($data['twitter']); ?>">

        <!-- Instagram (uses Open Graph tags mostly) -->
        <!-- No unique meta tags needed, but OG tags cover Instagram previews -->

        <!-- Google / Schema.org -->
        <meta itemprop="name" content="<?php echo esc_attr($data['title']); ?>">
        <meta itemprop="description" content="<?php echo esc_attr($data['description']); ?>">
        <meta itemprop="image" content="<?php echo esc_url($data['image']); ?>">

        <?php
        return ob_get_clean();
    }

        /**
     * Generate schema for post type 
     * 
     * @return string 
     * 
     * @since 1.0.0
     * @access public 
     */
    public function generateSchema($post=null)
    {
        if(is_null($post)) $post = get_post();
        elseif(is_numeric($post)) $post = get_post($post); 

        if(!$post || $post->post_type != 'sc_product') return '';
        $schema = array();
        $schema['@context'] = 'https://schema.org';
        $schema['@type'] = 'Product';
        $schema['name'] = $post->post_title;
        $schema['description'] = $post->post_excerpt;
        $schema['image'] = get_the_post_thumbnail_url($post->ID, 'full');
        $schema['url'] = get_permalink($post->ID);
        $schema['offers'] = array();
        $schema['offers']['@type'] = 'Offer';
        $schema['offers']['price'] = get_post_meta($post->ID, 'selling_price', true);
        $schema['offers']['priceCurrency'] = 'BDT';
        $schema['offers']['availability'] = 'InStock';
        $schema['offers']['url'] = get_permalink($post->ID);
        $schema['offers']['priceValidUntil'] = date('Y-m-d', strtotime('+1 year'));
        $schema['offers']['itemCondition'] = 'NewCondition';
        $schema['offers']['url'] = get_permalink($post->ID);
        $schema['offers']['priceValidUntil'] = date('Y-m-d', strtotime('+1 year'));
        ob_start();
        ?>
        <script type="application/ld+json">
            <?php echo json_encode($schema); ?>
        </script>
        <?php
        return ob_get_clean();
    }

    public static function getDistrict()
    {
        return [
            "Dhaka"=>"ঢাকা", "Chattogram"=>"চট্টগ্রাম",
            "Bagerhat"=>"বাগেরহাট", "Bandarban"=>"বান্দরবান", "Barguna"=>"বরগুনা", "Barishal"=>"বরিশাল",
            "Bhola"=>"ভোলা", "Bogura"=>"বগুড়া", "Brahmanbaria"=>"ব্রাহ্মণবাড়িয়া", "Chandpur"=>"চাঁদপুর",
            "Chapainawabganj"=>"চাঁপাইনবাবগঞ্জ",  "Chuadanga"=>"চুয়াডাঙ্গা",
            "Cox's Bazar"=>"কক্সবাজার", "Cumilla"=>"কুমিল্লা", "Dinajpur"=>"দিনাজপুর",
            "Faridpur"=>"ফরিদপুর", "Feni"=>"ফেনী", "Gaibandha"=>"গাইবান্ধা", "Gazipur"=>"গাজীপুর",
            "Gopalganj"=>"গোপালগঞ্জ", "Habiganj"=>"হবিগঞ্জ", "Jamalpur"=>"জামালপুর", "Jashore"=>"যশোর",
            "Jhalokati"=>"ঝালকাঠি", "Jhenaidah"=>"ঝিনাইদহ", "Joypurhat"=>"জয়পুরহাট",
            "Khagrachhari"=>"খাগড়াছড়ি", "Khulna"=>"খুলনা", "Kishoreganj"=>"কিশোরগঞ্জ",
            "Kurigram"=>"কুড়িগ্রাম", "Kushtia"=>"কুষ্টিয়া", "Lakshmipur"=>"লক্ষ্মীপুর",
            "Lalmonirhat"=>"লালমনিরহাট", "Madaripur"=>"মাদারীপুর", "Magura"=>"মাগুরা",
            "Manikganj"=>"মানিকগঞ্জ", "Meherpur"=>"মেহেরপুর", "Moulvibazar"=>"মৌলভীবাজার",
            "Munshiganj"=>"মুন্সিগঞ্জ", "Mymensingh"=>"ময়মনসিংহ", "Naogaon"=>"নওগাঁ",
            "Narail"=>"নড়াইল", "Narayanganj"=>"নারায়ণগঞ্জ", "Narsingdi"=>"নরসিংদী",
            "Natore"=>"নাটোর", "Netrokona"=>"নেত্রকোনা", "Nilphamari"=>"নীলফামারী",
            "Noakhali"=>"নোয়াখালী", "Pabna"=>"পাবনা", "Panchagarh"=>"পঞ্চগড়",
            "Patuakhali"=>"পটুয়াখালী", "Pirojpur"=>"পিরোজপুর", "Rajbari"=>"রাজবাড়ী",
            "Rajshahi"=>"রাজশাহী", "Rangamati"=>"রাঙামাটি", "Rangpur"=>"রংপুর",
            "Satkhira"=>"সাতক্ষীরা", "Shariatpur"=>"শরীয়তপুর", "Sherpur"=>"শেরপুর",
            "Sirajganj"=>"সিরাজগঞ্জ", "Sunamganj"=>"সুনামগঞ্জ", "Sylhet"=>"সিলেট",
            "Tangail"=>"টাঙ্গাইল", "Thakurgaon"=>"ঠাকুরগাঁও"
          ];
          
    }

    /**
     * Get User IP 
     * 
     * @return string 
     * 
     * @since 1.0.0
     * @access public 
     */
    public static function getUserIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Handle multiple forwarded IPs
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return trim($ip);
    }

    /**
     * Get Device Type 
     * 
     * @return string 
     * 
     * @since 1.0.0
     * @access public 
     */
    public static function getDeviceType() {
        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);

        if (preg_match('/mobile|android|iphone|ipod|blackberry|iemobile|kindle/', $userAgent)) {
            return 'Mobile';
        } elseif (preg_match('/ipad|tablet|playbook/', $userAgent)) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }

    /**
     * Redirect wp-login.php to homepage if token not found 
     * 
     * @return void 
     * @since 1.0.0
     * @access public 
     */
    public function secureLogin()
    {
        // Set your secret token here
        $required_token = 'scommerce_admin';

        // Check if token is present in the URL
        if (!isset($_GET['token']) || $_GET['token'] !== $required_token) {
            wp_die(
                __('Access denied. Missing or invalid token.'),
                __('Forbidden'),
                ['response' => 403]
            );
        }
    }

    /**
     * Footer Content 
     * 
     * @return void 
     * @since 1.0.0
     * @access public 
     */
    public function wpFooter()
    {
        ob_start();
        ?>
        <script>
            // Adding css files when window is loaded
            window.addEventListener('load', function(){
                <?php
                    global $wp_styles, $wp_scripts;

                    // get all registered css files 
                    foreach($wp_styles->registered as $handle => $style){
                        if(!str_contains($handle, 'sc-')) continue;
                        ?>
                        var style = document.createElement('link');
                        style.rel = 'stylesheet';
                        style.href = '<?php echo $style->src; ?>?ver=<?php echo rand(1,1000000); ?>';
                        document.head.appendChild(style);
                        <?php 
                    }
                    // get all registered js files 
                    foreach($wp_scripts->registered as $handle => $script){
                        if(!str_contains($handle, 'sc-')) continue;
                        ?>
                        var script = document.createElement('script');
                        script.src = '<?php echo $script->src; ?>?ver=<?php echo rand(1,1000000); ?>';
                        document.head.appendChild(script);
                        <?php 
                    }
                ?>
            });
        </script>
        <style>
            <?php
                $desktop_logo_height = Settings::get('desktop_logo_height');
                $mobile_logo_height = Settings::get('mobile_logo_height');
            ?>
            <?php if($desktop_logo_height > 0) : ?>
                header#site-header .header-container .logo-wrap .logo-img { height: <?php echo $desktop_logo_height; ?>px !important; }
                .sc-dashboard .sc-sidebar .sc-welcome-message .sc-welcome-message-logo img{height: <?php echo $desktop_logo_height; ?>px !important;}
            <?php endif; ?>

            <?php if($mobile_logo_height > 0): ?>
                @media screen and (max-width: 768px){
                    header#site-header .header-container .logo-wrap .logo-img { height: <?php echo $mobile_logo_height; ?>px !important; }
                    .sc-dashboard .sc-sidebar .sc-welcome-message .sc-welcome-message-logo img{height: <?php echo $mobile_logo_height; ?>px !important;}
                }
            <?php endif; ?>

        </style>
        <?php
        echo ob_get_clean();
    }

    /**
     * Show Pagination 
     * 
     * @return string 
     * @since 1.0.0
     * @access public 
     */
    public static function getPagination($total_pages=10)
    {
        if( $total_pages == 0 ) return '';

        $big = 999999999; // need an unlikely integer

        $paged = max(get_query_var('paged'), 1);
        $page = max(get_query_var('page'), 1);
        $cur_page = max( $paged, $page );

        ob_start();

        echo "<div class='sc-pagination'>";
        echo paginate_links(
            array(
                'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                'format' => '?paged=%#%',
                'current' => $cur_page,
                'total' => $total_pages,
                'mid_size' => 1,
                'prev_text' => __('«'),
                'next_text' => __('»'),
                'type' => 'list'
            )
        );
        echo "</div>";
        return ob_get_clean();
    }

    /**
     * Generate Bar code 
     * 
     * @return string 
     * @since 1.0.0
     * @access public 
     */
    public static function generateBarcodeRaw($code, $height = 60, $scale = 2) {
        $map = [
            '0'=>'101001101101', '1'=>'110100101011', '2'=>'101100101011',
            '3'=>'110110010101', '4'=>'101001101011', '5'=>'110100110101',
            '6'=>'101100110101', '7'=>'101001011011', '8'=>'110100101101',
            '9'=>'101100101101', 'A'=>'110101001011', 'B'=>'101101001011',
            'C'=>'110110100101', 'D'=>'101011001011', 'E'=>'110101100101',
            'F'=>'101101100101', 'G'=>'101010011011', 'H'=>'110101001101',
            'I'=>'101101001101', 'J'=>'101011001101', 'K'=>'110101010011',
            'L'=>'101101010011', 'M'=>'110110101001', 'N'=>'101011010011',
            'O'=>'110101101001', 'P'=>'101101101001', 'Q'=>'101010110011',
            'R'=>'110101011001', 'S'=>'101101011001', 'T'=>'101011011001',
            'U'=>'110010101011', 'V'=>'100110101011', 'W'=>'110011010101',
            'X'=>'100101101011', 'Y'=>'110010110101', 'Z'=>'100110110101',
            '-'=>'100101011011', '.'=>'110010101101', ' '=>'100110101101',
            '*'=>'100101101101', '$'=>'100100100101', '/'=>'100100101001',
            '+'=>'100101001001', '%'=>'101001001001'
        ];
    
        $code = '*' . strtoupper($code) . '*'; // Start/stop chars
        $pattern = '';
        foreach (str_split($code) as $char) {
            $pattern .= $map[$char] . '0'; // narrow space
        }
    
        $width = strlen($pattern) * $scale;
        $im = imagecreate($width, $height);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
    
        $x = 0;
        foreach (str_split($pattern) as $bar) {
            if ($bar === '1') {
                imagefilledrectangle($im, $x, 0, $x + $scale - 1, $height, $black);
            }
            $x += $scale;
        }
    
        imagepng($im);
        imagedestroy($im);
    }

    /**
     * Generate Bar Code 
     * 
     * @return string 
     * @since 1.0.0
     * @access public 
     */
    public static function generateBarcode($code, $height = 40, $scale = 2, $class='') 
    {
        ob_start();
        self::generateBarcodeRaw($code, $height, $scale);
        $png = ob_get_clean();
        $base64 = base64_encode($png);
        return '<div style="text-align: center;font-size:12px;line-height:1em;"><img class="'.$class.'" src="data:image/png;base64,' . $base64 . '"><br>'.$code.'</div>';
    }

    /**
     * Convert EN digit to bn digit
     * 
     * @return string 
     * @since 1.0.0
     * @access public 
     */
    public static function convertENToBN($number)
    {
        $locale = get_locale();
        if($locale != 'bn_BD') return $number;
        $bn_numbers = array(
            '0' => '০',
            '1' => '১',
            '2' => '২',
            '3' => '৩',
            '4' => '৪',
            '5' => '৫',
            '6' => '৬',
            '7' => '৭',
            '8' => '৮',
            '9' => '৯'
        );
        return strtr($number, $bn_numbers);
    }

    /**
     * Show digit based on language
     * 
     * @return string 
     * @since 1.0.0
     * @access public 
     */
    public static function showDigit($number)
    {
        $language = Settings::get('site_language');
        if($language == 'bn_BD') return self::convertENToBN($number);
        return $number;
    }

    /**
     * Install Translation 
     * 
     * @return void 
     * @since 1.0.0
     * @access public 
     */
    public function installTranslation($value='')
    {
        $locale = $value == 'bn_BD' ? 'bn_BD' : get_locale();

        if($locale == 'bn_BD'){

            // Load Text Domain 
            load_plugin_textdomain( 'smartcommerce', false, basename(dirname(__FILE__)) . '/languages');

            // Load WP_Upgrader classes if not already loaded
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/misc.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once ABSPATH . 'wp-admin/includes/translation-install.php';

            $upgrader = new \Language_Pack_Upgrader( new \Automatic_Upgrader_Skin() );

            // Remove the core language pack
            if ( method_exists( $upgrader, 'delete_old_files' ) ) {
                $upgrader->delete_old_files( array( 'core' => array( $locale ) ) );
            }
    
            // Install the language pack
            $upgrader->bulk_upgrade( array( $locale ) );
    
            // Set site language
            update_option( 'WPLANG', $locale );
                        
        }
        
    }



    /**
     * Create Pages After Plugin Activate
     * 
     * @return void 
     * @since 1.0.0
     * @access public 
     */
    public function actionsOnActivation()
    {
        // Create Installation date 
        update_option('sc_installation_date', current_time('mysql'), 'no');
        $pages = array(
            'dashboard' => array(
                'title' => 'Dashboard',
                'content' => '[sc_dashboard]',
                'page_id' => 0,
            ),
            'home' => array(
                'title' => 'Home',
                'content' => '[sc_home]',
                'page_id' => 0,
            ),
            'tracking' => array(
                'title' => 'Tracking',
                'content' => '[sc_tracking]',
                'page_id' => 0
            ),
            'cart' => array(
                'title' => 'Cart',
                'content' => '[sc_cart]',
                'page_id' => 0
            ),
            'checkout' => array(
                'title' => 'Checkout',
                'content' => '[sc_checkout]',
                'page_id' => 0
            ),
            'products' => array(
                'title' => 'Products',
                'content' => '[sc_products]',
                'page_id' => 0
            ),
            'categories' => array(
                'title' => 'Categories',
                'content' => '[sc_categories]',
                'page_id' => 0
            ),
            'profile' => array(
                'title' => 'Profile',
                'content' => '[sc_profile]',
                'page_id' => 0
            ),
            'login' => array(
                'title' => 'Login',
                'content' => '[sc_login]',
                'page_id' => 0
            ),
            'privacy_policy' => array(
                'title' => 'Privacy Policy',
                'content' => '[sc_privacy_policy]',
                'page_id' => 0
            ),
            'terms_and_conditions' => array(
                'title' => 'Terms and Conditions',
                'content' => '[sc_terms_and_conditions]',
                'page_id' => 0
            ),
            'return_policy' => array(
                'title' => 'Return Policy',
                'content' => '[sc_return_policy]',
                'page_id' => 0
            ),
        );

        $key = 'sc_pages';
        $current_pages = get_option($key, []);
        $current_pages = maybe_unserialize($current_pages);
        if(!empty($current_pages)){
            foreach($current_pages as $key => $page){
                $page_id = $page['page_id'];
                if($page_id){
                    // Updating Page Title and Content 
                    $update = wp_update_post(array(
                        'ID' => $page_id,
                        'post_title' => $pages[$key]['title'],
                        'post_content' => $pages[$key]['content'],
                    ));
                    if($update) $pages[$key]['page_id'] = $page_id;
                }
            }
        }

        // Creating new pages 
        foreach($pages as $key => $page){
            $page_id = $page['page_id'];
            $post = get_post($page_id);
            if(!$page_id || !$post){

                // find page by slug or name 
                $page_id = get_page_by_path(strtolower(trim($page['title'])), OBJECT, 'page');
                if($page_id){
                    $page_id = $page_id->ID;
                    $update = wp_update_post(array(
                        'ID' => $page_id,
                        'post_title' => $page['title'],
                        'post_content' => $page['content'],
                    ));
                    if($update) $pages[$key]['page_id'] = $page_id;
                    continue;
                }

                // creating new page 
                $page_id = wp_insert_post(array(
                    'post_title' => $page['title'],
                    'post_content' => $page['content'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                ));
                if($page_id) $pages[$key]['page_id'] = $page_id;
            }
        }

        update_option($key, maybe_serialize($pages), 'no');

        // Setting Front Page
        $front_page = $pages['home']['page_id'];
        if($front_page){
            update_option('page_on_front', $front_page);
            update_option('show_on_front', 'page');
        }

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        global $wpdb;
        $table_name = $wpdb->prefix . 'sms';
        $charset_collate = $wpdb->get_charset_collate();
        $create_table = "CREATE TABLE {$table_name} (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `mobile` VARCHAR(15) NOT NULL,
                `message` VARCHAR(1024) NOT NULL,
                `sms_count` INT NOT NULL,
                `sender` BIGINT NOT NULL,
                `rate` float NOT NULL,
                `status` VARCHAR(10) NOT NULL,
                `record_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) {$charset_collate};";
            dbDelta($create_table);

    }

    /**
     * Embed JS script when window loaded 
     * 
     * @return string 
     * @since 1.0.0
     * @access public 
     */
    public static function embedScript($script)
    {
        ob_start();
        $type = str_contains($script, '.css') ? 'css' : 'js';
        ?>
        <script type="text/javascript">
            window.addEventListener('load', function(){
                <?php if($type == 'css') : ?>
                    var link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = '<?php echo $script; ?>';
                    document.head.appendChild(link);
                <?php else : ?>
                    var script = document.createElement('script');
                    script.src = '<?php echo $script; ?>';
                    document.head.appendChild(script);
                <?php endif; ?>
            });
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Return html to share the page with social medias 
     * 
     * @param string $url
     * @return string
     * @since 1.0.0
     * @access public
     */
    public static function sharePage($url='')
    {
        $protocol = is_ssl() ? 'https' : 'http';
        if(empty(trim($url))) $url = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $fb_app_id = Settings::get('facebook_app_id', '942342894766699');
        ob_start();
        ?>
        <style>
            .sc-social-share{
                display: flex;
                align-items: center;
                gap: 5px;
            }
            .sc-social-share-title{
                font-size: 14px;
                font-weight: 600;
                margin-right: 10px;
            }
            .sc-social-share a{
                font-size: 18px;
                color: var(--sc-accent-color);
                cursor: pointer;
            }
            .sc-social-share a:hover{
                color: var(--sc-secondary-color);
            }
        </style>
        <div class="sc-social-share sc-mb-20">
            <span class="sc-social-share-title"><?php _e('Share', 'smartcommerce'); ?></span>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($url); ?>" target="_blank"><i class="fab fa-facebook"></i></a>
            <a href="https://www.facebook.com/dialog/send?app_id=<?php echo $fb_app_id; ?>&link=<?php echo urlencode($url); ?>&redirect_uri=<?php echo urlencode($url); ?>" target="_blank"><i class="fab fa-facebook-messenger"></i></a>
            <a href="https://wa.me/?text=<?php echo urlencode($url); ?>" target="_blank"><i class="fab fa-whatsapp"></i></a>
            <a href="https://www.linkedin.com/shareArticle?url=<?php echo urlencode($url); ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
        </div>
        <?php
        return ob_get_clean();
    }
}

$smartcommerce = SmartCommerce::instance();
$smartcommerce->wpInit();