<?php 
namespace SmartCommerce;

class Admin 
{
    /**
     * Instance
     */
    private static $_instance;

    public $id;
    public $user;
    public $usermeta;

    /**
     * Constructor 
     * 
     * @return void 
     * @since 1.0.0
     */
    public function __construct( $id = 0 ) 
    {
        // set id 
        $this->id = $id; 
        if($this->id){
            $user = get_user_by('ID', $this->id);
            $this->user = $user;
            $this->usermeta = get_user_meta($this->id);
        }

        // add user register user type 
        add_action( 'init', [$this, 'registerRole']);
    }

    /**
     * Initialize instance 
     * 
     * @return self 
     * @since 1.0.0
     */
    public static function instance()
    {
        if( self::$_instance == null ) self::$_instance = new self();
        return self::$_instance;
    }

    /**
     * Register user role 
     * 
     * @return void 
     * @since 1.0.0
     */
    public function registerRole()
    {
        // create user role 
        $role = get_role('sc_admin');
        if(!$role){ 
            $role = add_role('sc_admin', 'SmartCommerce Admin', ['read' => true]);
        }
    }

    /**
     * Show top bar 
     * 
     * @return void 
     * 
     * @since 1.0.0
     * @access public 
     */
    public function showTopBar()
    {
        if(current_user_can('sc_admin') || current_user_can('administrator')){
            $user = new Admin(get_current_user_id());
            $dash_page_id = Admin::getSetting('dashboard_page_id');
            if(!$dash_page_id) Admin::generatePages();
            $dash_page_id = Admin::getSetting('dashboard_page_id');
            $dash_page_url = get_permalink($dash_page_id);
            ob_start();
            ?>
            <style>

                .sc-top-bar-wrap{
                    background-color: var(--sc-secondary-color);
                    color: #fff;
                    padding: 0 20px;
                    text-align: center;
                    font-size: 14px;
                    font-weight: 600;
                    border-bottom: 1px solid var(--sc-white-color);
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    z-index: 9999;
                    width: 100%;
                    text-align: right;
                    box-sizing: border-box;
                    display: flex;
                    height: 30px;
                    line-height: 30px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
                }
                .sc-top-bar-content{
                    width: 100%;
                    height: auto;
                    margin: 0;
                    padding: 0;
                    display: flex;
                    flex: 1;
                    justify-content: flex-end;
                }
                .sc-top-bar-content p{
                    margin: 0;
                    padding: 0;
                    color: #fff;
                    font-size: 13px;
                    font-weight: 400;
                }
                .sc-top-bar-content p a{
                    color: #fff !important;
                    text-decoration: none;
                }
            </style>
            <div class="sc-top-bar-wrap">
                <div class="sc-top-bar-content">
                    <p>
                        Welcome <?php echo $user->user->display_name; ?>, 
                        <a href="<?php echo site_url('/dashboard'); ?>" class="sc-top-bar-link"><?php _e('View Dashboard', 'smartcommerce'); ?></a> | 
                        <a href="<?php echo self::getLogoutUrl(); ?>" class="sc-top-bar-link"><?php _e('Logout', 'smartcommerce'); ?></a>
                    </p>
                </div>
            </div>
            <?php 
            echo ob_get_clean();
        }
    }

    /**
     * Get Logout url 
     * 
     * @return string 
     * @since 1.0
     * @access public 
     * @static 
     */
    public static function getLogoutUrl()
    {
        return wp_logout_url(home_url('/'));
    }

    /**
     * Get Setting
     * 
     * @param string setting_name 
     * @return mixed 
     * 
     * @since 1.0.0
     * @access public 
     */
    public static function getSetting( $setting_name )
    {
        $key = 'sc_master_settings';
        $settings = maybe_unserialize(get_option($key));
        if(isset($settings[$setting_name])){
            return $settings[$setting_name];
        }
        return null;
    }

    /**
     * Update Settings
     * 
     * @return boolean 
     * 
     * @since 1.0.0
     * @access public 
     */
    public static function updateSetting( $setting_name, $value )
    {
        $key = 'sc_master_settings';
        $settings = maybe_unserialize(get_option($key));
        if(empty($settings)) $settings = [];
        if(isset($settings[$setting_name])){
            $settings[$setting_name] = $value;
        }else{
            $settings[$setting_name] = $value;
        }
        return update_option($key, maybe_serialize($settings), 'no');
    }

    /**
     * Delete setting 
     * 
     * @param string setting_name 
     * @return boolean 
     * 
     * @since 1.0.0
     * @access public 
     */
    public static function deleteSetting( $setting_name )
    {
        $key = 'sc_master_settings';
        $settings = maybe_unserialize(get_option($key));
        if(isset($settings[$setting_name])){
            unset($settings[$setting_name]);
        }
        return update_option($key, maybe_serialize($settings), 'no');
    }

    /**
     * Generate Pages for dashbaord and checkout page
     * 
     * @return void 
     * 
     * @since 1.0.0
     * @access public 
     */
    public function generatePages()
    {
        // create dashboard page
        $dashboard_page_id = self::getSetting('dashboard_page_id');
        if(!$dashboard_page_id){
            $dashboard_page_id = wp_insert_post([
                'post_title' => 'Dashboard',
                'post_content' => '[sc_dashboard]',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => 'dashboard',
            ]);
            self::updateSetting('dashboard_page_id', $dashboard_page_id);
        }

        // create checkout page 
        $checkout_page_id = self::getSetting('checkout_page_id');
        if(!$checkout_page_id){
            $checkout_page_id = wp_insert_post([
                'post_title' => 'Checkout',
                'post_content' => '[sc_checkout]',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => 'checkout',
            ]);
            self::updateSetting('checkout_page_id', $checkout_page_id);
        }
    }

    /**
     * Get Google Fonts 
     * 
     * @return array 
     * 
     * @since 1.0.0
     * @access public 
     * @static 
     */
    public static function getGoogleFonts()
    {
        $json_url = SMART_COMMERCE_JSON_URL . 'google-fonts.json';
        $json = file_get_contents($json_url);
        $fonts = json_decode($json, true);
        if(empty($fonts)) return [];
        $fonts = array_unique($fonts);
        return $fonts;
        // api data 
        $fonts = array_map(function($font){
            return $font['family'];
        }, $fonts['items']);
        $bangla_fonts = ['Tiro Bangla','Anek Bangla', 'Hind Siliguri', 'Noto Sans Bengali', 'Noto Serif Bengali', 'Mina', 'Baloo Da 2', 'Atma', 'Galada'];
        return $bangla_fonts + $fonts;
    }
}

Admin::instance();