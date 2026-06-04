<?php 
namespace SmartCommerce;

class Frontend {

 
    /**
     * Instance 
     */
    private static $_instance;

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
     * Show Dashboard 
     * 
     * @return string 
     * @since 1.0.0
     * @access public 
    */
    public function showDashboard()
    {
        if(!current_user_can('manage_options') && !current_user_can('sc_admin')){
            return User::getLoginForm(true);
        }
        ob_start();
        ?>

        <div class="sc-dashboard sc-flex-wrap">

            <!-- Left bar --> 
            <div class="flex-2 sc-sidebar"> <?php echo $this->getWelcomeMessage() . $this->getMenuHtml(); ?> </div>

            <!-- Right content --> 
            <div class="flex-10 sc-content"> 
                
                <?php echo $this->getContent(); ?> 
            </div>

        </div>
        <?php
        return ob_get_clean();
    }


    /**
     * Get menu 
     * 
     * @return array 
     * @since 1.0.0
     * @access public 
     */
    public function getMenuItems()
    {
        $items = array(
            'dashboard' => array(
                'title' => __('Dashboard', 'smartcommerce'),
                'url' => home_url('/dashboard'),
                'icon' => 'fa-solid fa-house',
            ),
            'sc_order' => array(   
                'title' => __('Orders', 'smartcommerce'),
                'url' => home_url('/dashboard/?submenu=sc_order'),
                'icon' => 'fa-solid fa-shopping-cart',
            ),
            'sc_product' => array(
                'title' => __('Products', 'smartcommerce'),  
                'url' => home_url('/dashboard/?submenu=sc_product'),
                'icon' => 'fa-solid fa-box',
            ),
            'sc_product_category' => array(
                'title' => __('Product Categories', 'smartcommerce'),  
                'url' => home_url('/dashboard/?submenu=sc_product_category'),
                'icon' => 'fa-solid fa-list',
            ),

            'sc_customer' => array(
                'title' => __('Customers', 'smartcommerce'),
                'url' => home_url('/dashboard/?submenu=sc_customer'),
                'icon' => 'fa-solid fa-users',
            ),

            'sc_brand' => array(
                'title' => __('Brands', 'smartcommerce'),
                'url' => home_url('/dashboard/?submenu=sc_brand'),
                'icon' => 'fa-solid fa-circle',
            ),

            'sc_delivery_partner' => array(
                'title' => __('Delivery Partners', 'smartcommerce'),
                'url' => home_url('/dashboard/?submenu=sc_delivery_partner'),
                'icon' => 'fa-solid fa-truck',
            ),
            'sms' => array(
                'title' => __('SMS', 'smartcommerce'),
                'url' => home_url('/dashboard/?submenu=sms'),
                'icon' => 'fa-solid fa-comment',
            ),
            'settings' => array(
                'title' => __('Settings', 'smartcommerce'),  
                'url' => home_url('/dashboard/?submenu=settings'),
                'icon' => 'fa-solid fa-gear',
            ),
            'update_profile' => array(
                'title' => __('Update Profile', 'smartcommerce'),
                'url' => home_url('/dashboard/?submenu=update_profile'),
                'icon' => 'fa-solid fa-user',
            ),
            'logout' => array(
                'title' => __('Logout', 'smartcommerce'),
                'url' => wp_logout_url(home_url('/')),
                'icon' => 'fa-solid fa-right-from-bracket',
                'class' => 'confirmLogout',
            ),
        );
        $items = apply_filters('sc_menu_items', $items);
        return $items;
    }

    /**
     * Get Welcome Message 
     * 
     * @return string 
     * @since 1.0.0
     * @access public 
     */
    public function getWelcomeMessage()
    {
        ob_start();
        $user = get_user_by('id', get_current_user_id());
        $name = !empty($user->first_name) ? $user->first_name : $user->display_name;
        ?>
        <div class="sc-welcome-message">
            <div class="sc-welcome-message-logo">
                <img src="<?php echo Settings::getLogo(); ?>" alt="Welcome" loading="lazy">
            </div>
            <div class="sc-welcome-message-content">
                <?php echo __('Welcome', 'smartcommerce'); ?> <?php echo $name; ?>! <a href="<?php echo Admin::getLogoutUrl(); ?>" class="sc-welcome-message-logout"><?php echo __('Logout', 'smartcommerce'); ?></a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get menu Html 
     * 
     * @return strirng 
     * 
     * @since 1.0.0
     * @access public 
     */
    public function getMenuHtml()
    {
        $items = $this->getMenuItems();
        if(empty($items)) return '';
        $submenu = sanitize_text_field($_GET['submenu'] ?? ''); 
        if(empty($submenu)) $submenu = 'dashboard';
        ob_start();
        ?>
        <ul class="sc-admin-menu desktop-only">
            <?php foreach($items as $key => $item){ ?>
                <?php $activeClass = $key == $submenu ? 'active' : ''; ?>
                <li class="<?php echo $activeClass; ?>">
                    <a href="<?php echo $item['url']; ?>" class="<?php echo isset($item['class']) ? $item['class'] : ''; ?>">
                        <i class="<?php echo $item['icon']; ?>"></i><?php echo $item['title']; ?>
                    </a>
                </li>
            <?php } ?>
        </ul>

        <div class="sc-mobile-admin-menu mobile-only tablet-only">
            <select name="admin-main-nav" id="admin_main_nav">
                <?php 
                    foreach($items as $key=>$item){
                        $selected = $key == $submenu ? 'selected' : '';
                        echo "<option value='{$item['url']}' {$selected}>{$item['title']}</option>";
                    }
                ?>
            </select>
        </div>
        <?php 
        return ob_get_clean();
    }

    /**
     * Get content 
     * 
     * @return string 
     * 
     * @since 1.0.0
     * @access public 
     */
    public function getContent()
    {
        ob_start();
        echo $this->getBreadcrumb();
        $submenu = isset($_GET['submenu']) ? $_GET['submenu'] : '';
        switch($submenu){
            case 'sc_order':
            case 'sc_product':
            case 'sc_brand':
            case 'sc_delivery_partner':
            case 'sc_product_category':
            case 'sms':
                echo $this->getList($submenu);
                break;
            case 'sc_customer':
                $user = new User();
                echo $user->getList();
                break;
            case 'settings':
                echo Settings::getPanel();
                break;
            case 'publish':
                echo $this->publishPost();
                break;
            case 'edit':
                echo $this->editPost();
                break;
            case 'update_profile':
                echo $this->updateProfile();
                break;
            default:
                echo $this->getDashboardItems();
                break;
        }
        return ob_get_clean();
    }

    /**
     * Get dashboard index 
     * 
     * @return string 
     * 
     * @since 1.0.0
     * @access public 
     */
    public function getDashboardItems()
    {
        $items = $this->getMenuItems();
        if(empty($items)) return '';
        ob_start();
        ?>

        <div class="sc-dashboard-items">
            <?php foreach($items as $key => $item){ ?>
                <div class="sc-dashboard-item">
                    <a href="<?php echo $item['url']; ?>">
                        <div class="icon-wrap"><i class="<?php echo $item['icon']; ?>"></i></div>
                        <div class="title-wrap"><?php echo $item['title']; ?></div>
                        <div class="count-wrap">
                            <?php 
                            switch($key){
                                case 'sc_product':
                                    $product = new Product();
                                    echo $product->countTotal();
                                    break;
                                case 'sc_brand':
                                    $brand = new Brand();
                                    echo $brand->countTotal();
                                    break;
                                case 'sc_delivery_partner':
                                    $delivery_partner = new DeliveryPartner();
                                    echo $delivery_partner->countTotal();
                                    break;
                                case 'sc_product_category':
                                    $product_category = new ProductCategory();
                                    echo $product_category->countTotal();
                                    break;
                                case 'sc_order':
                                    $order = new Order();
                                    echo $order->countTotal();
                                    break;
                                case 'sc_customer':
                                    $user = new User();
                                    echo $user->countTotal();
                                    break;
                                case 'settings':
                                default:
                                    echo '';
                                break;
                            }
                            ?>
                        </div>
                    </a>
                </div>
            <?php } ?>
        </div>
        <?php 
        return ob_get_clean();
    }

    /**
     * Get page breadcrumb 
     * 
     * @return string 
     * 
     * @since 1.0.0
     * @access public 
     */
    public function getBreadcrumb()
    {
        $items = $this->getMenuItems();
        if(empty($items)) return '';
        $submenu = sanitize_text_field($_GET['submenu'] ?? '');
        ob_start();
        ?>
        <style>
            .sc-breadcumb-wrap{
                display: flex;
                flex-direction: row;
                flex:1;
                height: 50px;
                overflow: hidden;
                align-items: center;
                margin-bottom: 30px;
                font-size: 14px;
                background-color: var(--sc-secondary-bg-color);
                padding: 10px;
                border-radius: 5px;
                box-sizing: border-box;
                justify-content: space-between;
            }
            .sc-breadcumb-wrap a{
                text-decoration: none;
                color: var(--sc-primary-color);
            }
            .sc-breadcumb-wrap span{
                color: var(--sc-primary-color);
            }
            .sc-breadcumb-wrap .separator{
                margin: 0 10px;
                color: var(--sc-secondary-color);
            }
            .sc-breadcumb-wrap .sc-breadcumb-title{
                color: var(--sc-primary-color);
                text-transform: capitalize;
            }
        </style>
        <div class="sc-breadcumb-wrap">
            <div>
                <a href="<?php echo home_url('/dashboard'); ?>">
                    <i class="fa-solid fa-house"></i> <?php _e('Dashboard', 'smartcommerce'); ?>
                </a>
                <?php if(!empty($submenu)){ ?>
                    <span class="separator"> / </span>
                    <span class="sc-breadcumb-title"><?php echo $items[$submenu]['title'] ?? $submenu; ?></span>
                <?php } ?>

            </div>
            <a class='sc-fright' target='_blank' href='<?php echo home_url(); ?>'><?php _e('View Store', 'smartcommerce'); ?> <i class='fa-solid fa-arrow-up-right-from-square'></i></a>
        </div>
        <?php if(Settings::get('sms_active') == 'yes' && !empty(Settings::get('sms_api_key'))){ ?>
                <div class="sms-balance-wrap" style="margin-bottom: 30px; font-size: 20px;">
                    <span class="sms-balance-title"><?php echo __('SMS Balance', 'smartcommerce'); ?></span>
                    <?php echo Settings::get('currency_symbol'); ?>
                    <span class="sms-balance-content" id="loadSmsBalance">...</span>
                    <script>
                        jQuery(document).ready(function($){
                            $('#loadSmsBalance').load(smartcommerce.ajax_url, {
                                action: 'get_sms_balance',
                            });
                        });
                    </script>
                </div>
        <?php } ?>  
        <?php 
        return ob_get_clean();
    }

    /**
     * Get product list 
     * 
     * @return string 
     * 
     * @since 1.0.0
     * @access public 
     */
    public function getList($post_type)
    {
        $obj = null; 
        switch($post_type){
            case 'sc_product':
                $obj = new Product();
                break;
            case 'sc_brand':
                $obj = new Brand();
                break;
            case 'sc_delivery_partner':
                $obj = new DeliveryPartner();
                break;
            case 'sc_product_category':
                $obj = new ProductCategory();
                break;
            case 'sc_order':
                $obj = new Order();
                break;
            case 'sms':
                $obj = new Sms();
                break;
            default:
                return '';
        }
        if(!$obj) return '';
        return $obj->getList();
    }

    /**
     * Publish a post 
     * 
     * @return string 
     * @since 1.0.0
     * @access public 
     */
    public function publishPost()
    {
        $type = sanitize_text_field($_GET['type'] ?? '');
        if(empty($type)) return '';
        $obj = null; 
        switch($type){
            case 'sc_product':
                $obj = new Product();
                break;
            case 'sc_brand':
                $obj = new Brand();
                break;
            case 'sc_delivery_partner':
                $obj = new DeliveryPartner();
                break;
            case 'sc_product_category':
                $obj = new ProductCategory();
                break;
            case 'sc_order':
                $obj = new Order();
                break;
            case 'sc_user':
                $obj = new User();
                break;
            default:
                return '';
        }
        if(!$obj) return '';
        return $obj->getPublishForm();
    }

    /**
     * Edit a post 
     * 
     * @return string 
     * @since 1.0.0
     * @access public 
     */
    public function editPost()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $type = sanitize_text_field($_GET['type'] ?? '');
        if(!$id) return '';
        $obj = null; 
        switch($type){
            case 'sc_product':
                $obj = new Product($id);
                break;
            case 'sc_brand':
                $obj = new Brand($id);
                break;
            case 'sc_delivery_partner':
                $obj = new DeliveryPartner($id);
                break;
            case 'sc_product_category':
                $obj = new ProductCategory($id);
                break;
            case 'sc_order':
                $obj = new Order($id);
                break;
            case 'sc_user':
                $obj = new User($id);
                break;
            default:
                return '';
        }
        if(!$obj) return '';
        return $obj->getEditForm();
    }

    /**
     * Update Profile 
     * 
     * @return string 
     * @since 1.0.0
     * @access public 
     */
    public function updateProfile()
    {
        $user = new User(get_current_user_id());
        if(!$user) return '';
        return $user->getEditForm();
    }
}

Frontend::instance();