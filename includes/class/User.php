<?php 
namespace SmartCommerce;

class User {

    /**
     * Instance 
     */
    private static $_instance;

    /**
     * Id 
     */
    public $id;
    public $user;
    public $userdata;
    public $roles = ['sc_admin', 'sc_customer'];

    /**
     * Constructor 
     * 
     * @return void 
     * @since 1.0.0
     */
    public function __construct( $id = 0 ) 
    {
        $this->id = $id;
        if(is_numeric($this->id) && $this->id > 0){
            $user = get_user_by('id', $this->id);
            if($user){
                $this->user = $user;
                $this->userdata = get_metadata('user', $this->id);
            }
        } else if( $this->id instanceof \WP_User ){
            $this->user = $this->id;
            $this->id = $this->id->ID;
            $this->userdata = get_metadata('user', $this->id);
        }

        // Add Custom Roles 
        add_action('init', array($this, 'addCustomRoles'));
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
     * Add custom roles 
     * 
     * @return void 
     * @since 1.0.0
     */
    public function addCustomRoles()
    {
        $roles = array(
            'sc_admin' => __('Admin', 'smartcommerce'),
            'sc_customer' => __('Customer', 'smartcommerce'),
        );
        foreach($roles as $role => $label){
            add_role($role, $label);
        }
    }

    /**
     * Do user login 
     * 
     * @param string $username 
     * @param string $password 
     * @return bool 
     * @since 1.0.0
     */
    public static function doLogin( $username, $password, $redirect_to = '' )
    {
        $user = get_user_by('login', $username);
        if(!$user) return array(
            'status' => false,
            'message' => __('Mobile number not found', 'smartcommerce'),
        );
        $signon = wp_signon( array(
            'user_login' => $username,
            'user_password' => $password,
        ) );
        if( is_wp_error( $signon ) ) return array(
            'status' => false,
            'message' => 'Mobile and password do not match',
        );
        if(!empty($redirect_to)) return array(
            'status' => true,
            'message' => __('Login successful', 'smartcommerce'),
            'redirect_to' => $redirect_to,
        );
        return array(
            'status' => true,
            'message' => __('Login successful', 'smartcommerce'),
            'redirect_to' => $redirect_to,
            'payload' => array(
                'username' => $username,
                'password' => $password,
                'redirect_to' => $redirect_to,
            ),
        );
    }

    /**
     * Get publish fields 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function getPublishFields()
    {
        $rand_pass = $this->user ? '' : wp_generate_password(8, false);
        $user_roles = array(
            'sc_admin' => __('Admin', 'smartcommerce'),
            'sc_customer' => __('Customer', 'smartcommerce'),
        );
        if(current_user_can('administrator')){
            $user_roles['administrator'] = __('Master Admin', 'smartcommerce');
        }
        return array(
            'display_name' => [
                'type' => 'text',
                'name' => 'display_name',
                'settings' => [
                    'label' => __('Name', 'smartcommerce'),
                    'required' => true,
                    'placeholder' => __('Enter your name', 'smartcommerce'),
                    'value' => $this->user ? $this->user->display_name : '',
                ],
            ],
            'mobile' => [
                'type' => 'number',
                'name' => 'mobile',
                'settings' => [
                    'label' => __('Mobile', 'smartcommerce'),
                    'required' => true,
                    'placeholder' => __('Enter your mobile number', 'smartcommerce'),
                    'value' => isset($this->userdata['mobile']) ? $this->userdata['mobile'][0] : '',
                ],
            ],
            'user_email' => [
                'type' => 'text',
                'name' => 'user_email',
                'settings' => [
                    'label' => __('Email', 'smartcommerce'),
                    'required' => true,
                    'placeholder' => __('Enter your email', 'smartcommerce'),
                    'value' => $this->user ? $this->user->user_email : self::generateUniqueEmail(),
                ],
            ],
            'password' => [
                'type' => 'password',
                'name' => 'user_pass',
                'settings' => [
                    'label' => __( $this->user ? 'Password (Leave blank to keep current password)' : 'Password', 'smartcommerce'),
                    'required' => $this->user ? false : true,
                    'placeholder' => __('Enter your password', 'smartcommerce'),
                    'value' => $rand_pass,
                    'after_html' => $this->user ? '' : '<span class="sc-pass-show" data-pass="'.$rand_pass.'"><br><br>Password: '.$rand_pass.'</span>',
                ],
            ],
            'role' => [
                'type' => 'select',
                'name' => 'role',
                'settings' => [
                    'label' => __('Role', 'smartcommerce'),
                    'options' => $user_roles,
                    'value' => $this->user ? reset($this->user->roles) : 'sc_customer',
                    'placeholder' => __('Select role', 'smartcommerce'),
                    'required' => true,
                ],
            ],
            'address' => [
                'type' => 'textarea',
                'name' => 'address',
                'settings' => [
                    'label' => __('Address', 'smartcommerce'),
                    'placeholder' => __('Enter your address', 'smartcommerce'),
                    'value' => isset($this->userdata['address']) ? $this->userdata['address'][0] : '',

                ],
            ],
        );
    }


    /**
     * Get publish form 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function getPublishForm()
    {
        $fields = $this->getPublishFields();
        if(empty($fields)) return '';
        if(current_user_can('sc_customer')) unset($fields['role']);
        ob_start();
        ?>
<form class="sc-form sc-ajax-form" data-post_type='sc_user'>
    <?php foreach($fields as $field){ ?>
    <div class="sc-form-row">
        <?php $label = $field['settings']['label'] ?? ''; ?>
        <?php if(!empty($label)): ?>
        <label for="<?php echo $field['name']; ?>"><?php echo $label; ?></label>
        <?php endif; ?>
        <div class="sc-field-wrap">
            <?php echo Form::generateElement($field['type'], $field['name'], $field['settings']); ?>
            <?php echo $field['settings']['after_html'] ?? ''; ?>
        </div>
    </div>
    <?php } ?>
    <div class="sc-form-row">
        <div class="sc-field-wrap">
            <?php 
                        echo Form::generateElement('button', 'sc_user_publish', array(
                            'value' => __( $this->user? 'Update' : 'Add', 'smartcommerce'),
                            'class' => 'sc-button sc-btn-primary',
                            'type' => 'submit',
                        ));
                        echo Form::generateElement('hidden', 'action', array(
                            'value' => 'smartcommerce_ajax'
                        ));
                        echo Form::generateElement('hidden', 'ajax_action', array(
                            'value' => $this->user? 'editPost' : 'publishPost',
                        ));
                        echo Form::generateElement('hidden', 'post_type', array(
                            'value' => 'sc_user',
                        ));
                        echo Form::generateElement('hidden', 'id', array(
                            'value' => $this->user? $this->user->ID : 0,
                        ));
                        echo Form::generateElement('hidden', 'post_id', array(
                            'value' => $this->user? $this->user->ID : 0,
                        ));
                        echo Form::generateElement('hidden', '_wpnonce', array(
                            'value' => wp_create_nonce('smartcommerce'),
                        ));
                        echo Form::generateElement('hidden', 'before_send_callback', array(
                            'value' => 'addUserBeforeSendCallback',
                        ));
                        echo Form::generateElement('hidden', 'success_callback', array(
                            'value' => 'addUserSuccessCallback',
                        ));
                    ?>
        </div>
    </div>
</form>
<?php
        return ob_get_clean();
    }

    /**
     * Get Publish Button 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function getPublishButton()
    {
        ob_start();
        ?>
<a href="<?php echo home_url('/dashboard/?submenu=publish&type=sc_user'); ?>"
    class="sc-button sc-btn-primary sc-fright sc-mb-20"
    data-post_type='sc_user'><?php _e('Add New User', 'smartcommerce'); ?></a>
<?php
        return ob_get_clean();
    }

    /**
     * 
     * Get action bar 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function getActionBar()
    {
        ob_start();
        ?>
<div class="sc-action-bar" data-post_type='sc_user'>

    <!-- Bulk Actions -->
    <?php 
                echo Form::generateElement('select', 'bulk_action', array(
                    'options' => array(
                        '' => __('Select Action', 'smartcommerce'),
                        'bulkDelete' => __('Delete', 'smartcommerce'),
                    ),
                    'class' => 'sc-form-control',
                    'style' => 'width: 150px; margin-right: 10px;',
                ));
                echo Form::generateElement('button', 'bulkActionApply', array(
                    'value' => __('Apply', 'smartcommerce'),
                    'class' => 'sc-button sc-button-primary sc-mb-20 bulkActionApply',
                    'data-action' => 'bulk_action',
                    'data-target' => 'id[]',
                    'type' => 'button',
                    'data'=>array(
                        'data-post_type' => 'sc_user',
                    )
                ));
            ?>

    <?php echo $this->getPublishButton(); ?>
</div>
<?php
        return ob_get_clean(); 
    }

    /**
     * Get user list 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function getList()
    {
        $users = get_users(array(
            'role__in' => $this->roles,
            'orderby' => 'ID',
            'order' => 'DESC',
        ));
        ob_start();
        echo $this->getActionBar();
        if(empty($users))  {
            _e('No user found', 'smartcommerce');
            echo '</div>';
            return ob_get_clean();
        }
        ?>
<div class="sc-table-wrap">
    <table class="sc-table">
        <thead>
            <tr>
                <th>
                    <input name='bulk_select' data-target='id[]' type="checkbox" id="select-all" class="sc-select-all">
                    <label for="select-all"><?php _e('Select All', 'smartcommerce'); ?></label>
                </th>
                <th><?php _e('Name', 'smartcommerce'); ?></th>
                <th><?php _e('Email', 'smartcommerce'); ?></th>
                <th><?php _e('Mobile', 'smartcommerce'); ?></th>
                <th><?php _e('Role', 'smartcommerce'); ?></th>
                <th><?php _e('Total<br>Orders', 'smartcommerce'); ?></th>
                <th><?php _e('Action', 'smartcommerce'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($users as $user){ ?>
            <?php 
                        $mobile = get_user_meta($user->ID, 'mobile', true);
                        $role = $user->roles ? $user->roles[0] : 'sc_customer';
                        $role_name = $role == 'sc_admin' ? __('Admin', 'smartcommerce') : __('Customer', 'smartcommerce');
                        $sc_user = new User($user->ID);
                        ?>
            <tr data-id="<?php echo $user->ID; ?>">
                <td>
                    <input type="checkbox" id="id_<?php echo $user->ID; ?>" name="id[]"
                        value="<?php echo $user->ID; ?>">
                    <label for="id_<?php echo $user->ID; ?>">#<?php echo $user->ID; ?></label>
                </td>
                <td><?php echo $user->display_name; ?></td>
                <td><?php echo $user->user_email; ?></td>
                <td><?php echo $mobile; ?></td>
                <td><?php echo $role_name; ?></td>
                <td><?php echo $sc_user->countOrders($user->ID); ?></td>
                <td>
                    <a href="<?php echo home_url('/dashboard/?submenu=edit&type=sc_user&id='.$user->ID); ?>"
                        class="sc-icon-link"><?php echo SmartCommerce::getIcon('edit'); ?></a>
                    <?php if($user->ID != get_current_user_id()): ?>
                    <a href="javascript:void(0)" class="sc-icon-link sc-delete-user sc-ajax-link"
                        data-before_send_callback="scConfirm" data-success_callback="deletePostSuccessCallback"
                        data-ajax_action="deletePost" data-post_type='sc_user'
                        data-id="<?php echo $user->ID; ?>"><?php echo SmartCommerce::getIcon('delete'); ?></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php } ?>
            <?php if(empty($users)) { ?>
            <tr>
                <td colspan="5" class="sc-text-center"><?php _e('No user found', 'smartcommerce'); ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<?php
        $html = ob_get_clean();
        return $html;
        foreach($users as $user){
            
        }   
        return array(
            'status' => 1,
            'message' => __('User list fetched successfully', 'smartcommerce'),
            'payload' => $data,
        );
    }

    /**
     * Generate unique email 
     * 
     * @param string $email 
     * @return string 
     * @since 1.0.0
     */
    public static function generateUniqueEmail()
    {
        $uniqid = uniqid();
        $domain = $_SERVER['HTTP_HOST'];
        return "{$uniqid}@{$domain}";
    }

    /**
     * Count total users
     * 
     * @return int 
     * @since 1.0.0
     */
    public function countTotal($roles='')
    {
        if(empty($roles)) $roles = $this->roles;
        $args = array(
            'role__in' => $roles,
            'count' => true,
        );
        $qry = new \WP_User_Query($args);
        return $qry->get_total();
    }

    /**
     * Publish 
     * 
     * @param array $data 
     * @return array 
     * @since 1.0.0
     */
    public function publish($data = array())
    {
        $args = array(
            'user_login' => sanitize_text_field($data['mobile'] ?? ''),
            'user_email' => sanitize_email($data['user_email'] ?? ''),
            'user_pass' => sanitize_text_field($data['password'] ?? ''),
            'role' => sanitize_text_field($data['role'] ?? ''),
            'first_name' => sanitize_text_field($data['display_name'] ?? ''),
            'display_name' => sanitize_text_field($data['display_name'] ?? ''),
        );
        $user_id = wp_insert_user($args);
        if(is_wp_error($user_id)) return array(
            'status' => false,
            'message' => $user_id->get_error_message(),
        );
        update_user_meta($user_id, 'mobile', sanitize_text_field($data['mobile'] ?? ''));
        update_user_meta($user_id, 'address', sanitize_text_field($data['address'] ?? ''));

        return array(
            'status' => true,
            'message' => __('User added successfully', 'smartcommerce'),
            'payload' => $data,
            'id' => $user_id,
        );
    }

    /**
     * Edit a user 
     * 
     * @return array 
     * 
     * @params array $data 
     */
    public function edit($data = array())
    {
        $user_id = $this->id;
        $args = array(
            'ID' => $user_id,
            'user_email' => sanitize_email($data['user_email'] ?? ''),
            'display_name' => sanitize_text_field($data['display_name'] ?? ''),
            'first_name' => sanitize_text_field($data['display_name'] ?? ''),
            'role' => sanitize_text_field($data['role'] ?? ''),
        );
        if(!empty($data['password'])){
            $args['user_pass'] = $data['password'];
        }

        $user_id = wp_update_user($args);
        if(is_wp_error($user_id)) return array(
            'status' => false,
            'message' => $user_id->get_error_message(),
        );
        update_user_meta($user_id, 'mobile', sanitize_text_field($data['mobile'] ?? ''));
        update_user_meta($user_id, 'address', sanitize_text_field($data['address'] ?? ''));
        return array(
            'status' => true,
            'message' => __('User updated successfully', 'smartcommerce'),
            'payload' => $data,
            'id' => $user_id,
        );
    }

    /**
     * Delete a user 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function delete($id)
    {
        $res = wp_delete_user($id);
        if(is_wp_error($res)) return array(
            'status' => false,
            'payload' => array('id' => $id),
            'id' => $id,
        );
        return array(
            'status' => true,
            'message' => __('User deleted successfully', 'smartcommerce'),
            'payload' => array('id' => $id),
            'id' => $id,
        );
    }

    /**
     * Bulk Delete 
     * 
     * @return array 
     * @since 1.0.0
     */
    public function bulkDelete($ids = array())
    {
        if(!is_array($ids)) $ids = explode(',', $ids);
        if(empty($ids)) return array(
            'status' => false,
            'message' => __('No user selected', 'smartcommerce'),
        );
        foreach($ids as $id){
            $res = $this->delete($id);
        }
        return array(
            'status' => true,
            'message' => __('Users deleted successfully', 'smartcommerce'),
            'ids' => $ids,
        );
    }

    /**
     * Count orders 
     * 
     * @return int 
     * 
     * @since 1.0
     * @access public 
     */
    public function countOrders($user_id)
    {
        return 0;
        $orders = get_posts(array(
            'post_type' => 'sc_order',
            'post_status' => 'publish',
            'author' => $user_id,
        ));
    }

    /**
     * Get edit form 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function getEditForm()
    {
        return $this->getPublishForm();
    }

    /**
     * Get All Users 
     * 
     * @return array 
     * @since 1.0.0
     */
    public static function getAll( $conds = [], $only_id_title = false )
    {
        $args = [];
        if(isset($conds['role'])){
            $args['role__in'] = is_array($conds['role']) ? $conds['role'] : array($conds['role']);
        }
        $qry = new \WP_User_Query($args);
        if($only_id_title){
            $res = [];
            foreach($qry->get_results() as $user){
                $res[$user->ID] = $user->display_name;
            }
            return $res;
        }
        return $qry->get_results();
    }

    /**
     * Get All Admins
     * @param bool $only_id_title
     * @return array 
     * @since 1.0.0
     */
    public static function getAllAdmins($only_id_title=true)
    {
        return self::getAll(array('role' => 'sc_admin'), $only_id_title);
    }

    /**
     * Get user name 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function getName()
    {
        return $this->user ? $this->user->display_name : '';
    }

    /**
     * Get mobile number 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function getMobile()
    {
        return isset($this->userdata['mobile']) ? $this->userdata['mobile'][0] : '';
    }

    /**
     * Get address 
     * 
     * @return string 
     * @since 1.0.0
     */
    public function getAddress()
    {
        return isset($this->userdata['address']) ? $this->userdata['address'][0] : '';
    }

    /**
     * Get Register Form 
     * 
     * @return string 
     * @since 1.0.0
     */
    public static function getRegisterForm($full_screen = false)
    {
        if(is_user_logged_in()) return '';
        $fields = array(
            'mobile' => array(
                'type' => 'text',
                'name' => 'mobile',
                'settings' => array(
                    'required' => true,
                    'placeholder' => __('01xxxxxxxxx', 'smartcommerce'),
                    'label' => __('Mobile', 'smartcommerce'),
                ),
            ),
            'otp_button'=>array(
                'type' => 'button',
                'name' => 'otp_button',
                'settings' => array(
                    'value' => __('Get OTP', 'smartcommerce'),
                    'class' => 'sc-button sc-button-primary',
                    'id' => 'sc-otp-btn',
                ),
                'before_send_callback' => 'otpBeforeSendCallback',
                'success_callback' => 'otpSuccessCallback',
                'error_callback' => 'otpErrorCallback',
                'complete_callback' => 'otpCompleteCallback',
            ),
            'otp' => array(
                'type' => 'text',
                'name' => 'otp',
                'settings' => array(
                    'required' => true,
                    'placeholder' => __('Enter OTP', 'smartcommerce'),
                    'label' => __('OTP', 'smartcommerce'),
                ),
            ),
            'password' => array(
                'type' => 'password',
                'name' => 'password',
                'settings' => array(
                    'required' => true,
                    'placeholder' => __('Enter password', 'smartcommerce'),
                    'label' => __('Password', 'smartcommerce'),
                ),
            ),
            'ajax_action' => array(
                'type' => 'hidden',
                'name' => 'ajax_action',
                'settings' => array(
                    'value' => 'userRegister',
                ),
            ),
            'action' => array(
                'type' => 'hidden',
                'name' => 'action',
                'settings' => array(
                    'value' => 'smartcommerce_ajax',
                ),
            ),
            'before_send_callback' => array(
                'type' => 'hidden',
                'name' => 'before_send_callback',
                'settings' => array(
                    'value' => 'registerBeforeSendCallback',
                ),
            ),
            'success_callback' => array(
                'type' => 'hidden',
                'name' => 'success_callback',
                'settings' => array(
                    'value' => 'registerSuccessCallback',
                ),
            ),
            'error_callback' => array(
                'type' => 'hidden',
                'name' => 'error_callback',
                'settings' => array(
                    'value' => 'registerErrorCallback',
                ),
            ),
            'complete_callback' => array(
                'type' => 'hidden',
                'name' => 'complete_callback',
                'settings' => array(
                    'value' => 'registerCompleteCallback',
                ),
            ),
            'redirect_to' => array(
                'type' => 'hidden',
                'name' => 'redirect_to',
                'settings' => array(
                    'value' => sanitize_text_field($_REQUEST['redirect_to'] ?? ''),
                    'class' => 'sc-hidden',
                ),
            ),
            '_wpnonce' => array(
                'type' => 'hidden',
                'name' => '_wpnonce',
                'settings' => array(
                    'value' => wp_create_nonce('smartcommerce'),
                ),
            ),
        );
        $full_screen_class = $full_screen ? 'sc-full-screen' : '';
        ob_start();
        $hidden_fields = [];
        ?>

        <div class="sc-register-form <?php echo $full_screen_class; ?>">
            <form class="sc-form sc-ajax-form" data-post_type='sc_user'>
                <div class="sc-form-group">
                    <?php foreach($fields as $field){
                        if($field['type'] == 'hidden'){
                            $hidden_fields[] = $field; 
                            continue;
                        }
                        $label = $field['settings']['label'] ?? ''; 
                        ?>
                        <div class="sc-form-row" data-field="<?php echo $field['name']; ?>">
                            <?php if(!empty($label)): ?>
                                <div class="sc-label-wrap">
                                    <label for="<?php echo $field['name']; ?>"><?php echo $label; ?></label>
                                </div>
                            <?php endif; ?>
                            <div class="sc-field-wrap">
                                <?php echo Form::generateElement($field['type'], $field['name'], $field['settings']); ?>
                            </div>
                        </div>
                        <?php 
                    } 
                    foreach($hidden_fields as $field){
                        echo Form::generateElement($field['type'], $field['name'], $field['settings']);
                    } ?>
                </div>
            </form>
        </div>
    <?php
    return ob_get_clean();
    }


    /**
     * Show Login Form 
     * 
     * @return string 
     * 
     * @since 1.0.0
     * @access public 
     * @static 
     */
    public static function getLoginForm( $full_screen = false, $redirect_to = '' )
    {
        $fields = array(
            'username' => array(
                'type' => 'text',
                'name' => 'username',
                'settings' => array(
                    'required' => true,
                    'placeholder' => __('01xxxxxxxxx', 'smartcommerce'),
                    'label' => __('Mobile', 'smartcommerce'),
                ),
            ),
            'password' => array(
                'type' => 'password',
                'name' => 'password',
                'settings' => array(
                    'required' => true,
                    'placeholder' => __('Enter password', 'smartcommerce'),
                    'label' => __('Password', 'smartcommerce'),
                ),
            ),
            'login' => array(
                'type' => 'button',
                'name' => 'login',
                'settings' => array(
                    'value' => __('Login', 'smartcommerce'),
                    'class' => 'sc-button sc-button-primary',
                    'id' => 'sc-login-btn',
                    'type' => 'submit',
                ),
            ),
            'action' => array(
                'type' => 'hidden',
                'name' => 'action',
                'settings' => array(
                    'value' => 'smartcommerce_ajax',
                ),
            ),
            'ajax_action' => array(
                'type' => 'hidden',
                'name' => 'ajax_action',
                'settings' => array(
                    'value' => 'userLogin',
                ),
            ),
            'redirect_to' => array(
                'type' => 'hidden',
                'name' => 'redirect_to',
                'settings' => array(
                    'value' => sanitize_text_field(!empty($redirect_to) ? $redirect_to : site_url()),
                ),
            ),
            '_wpnonce' => array(
                'type' => 'hidden',
                'name' => '_wpnonce',
                'settings' => array(
                    'value' => wp_create_nonce('smartcommerce'),
                ),
            ),
            'before_send_callback' => array(
                'type' => 'hidden',
                'name' => 'before_send_callback',
                'settings' => array(
                    'value' => 'loginBeforeSendCallback',
                ),
            ),
            'success_callback' => array(
                'type' => 'hidden',
                'name' => 'success_callback',
                'settings' => array(
                    'value' => 'loginSuccessCallback',
                ),
            ),
            'error_callback' => array(
                'type' => 'hidden',
                'name' => 'error_callback',
                'settings' => array(
                    'value' => 'loginErrorCallback',
                ),
            ),
            'complete_callback' => array(
                'type' => 'hidden',
                'name' => 'complete_callback',
                'settings' => array(
                    'value' => 'loginCompleteCallback',
                ),
            ),
            'redirect_to' => array(
                'type' => 'hidden',
                'name' => 'redirect_to',
                'settings' => array(
                    'value' => sanitize_text_field($_REQUEST['redirect_to'] ?? ''),
                    'class' => 'sc-hidden',
                ),
            ),
        );
        $hidden_fields = [];
        $logo = Settings::getLogo();
        ob_start();
        
        $full_screen_class = $full_screen ? 'sc-full-screen' : '';
        ?>

        <div class="sc-login-form <?php echo $full_screen_class; ?>">
            <a class="logo-wrap" href="<?php echo home_url(); ?>">
                <img loading="lazy" src="<?php echo $logo; ?>" alt="<?php _e('Logo', 'smartcommerce'); ?>" class="sc-login-form-logo">
            </a>
            <form action="" method="post" class="sc-form sc-ajax-form">
                <div class="sc-form-group">
                    <?php 
                        foreach($fields as $field){
                            if($field['type'] == 'hidden'){
                                $hidden_fields[] = $field; 
                                continue;
                            }
                            $label = $field['settings']['label'] ?? ''; 
                            ?>
                            <div class="sc-form-row">
                                <?php if(!empty($label)): ?>
                                    <div class="sc-label-wrap">
                                        <label for="<?php echo $field['name']; ?>"><?php echo $label; ?></label>
                                    </div>
                                <?php endif; ?>
                                <div class="sc-field-wrap">
                                    <?php echo Form::generateElement($field['type'], $field['name'], $field['settings']); ?>
                                </div>
                            </div>
                            <?php 
                            } 
                            foreach($hidden_fields as $field){
                                echo Form::generateElement($field['type'], $field['name'], $field['settings']);
                            }
                        ?>
                </div>
            </form>
            <div class="sc-form-group">
                <a href='javascript:void(0)' class='sc-ajax-link sc-register-link' data-ajax_action='showUserRegisterForm'
                    data-success_callback='popupData'><?php _e('Register', 'smartcommerce'); ?></a>
                |
                <a href='javascript:void(0)' class='sc-ajax-link sc-reset-link' data-ajax_action='showResetForm'
                    data-success_callback='popupData'><?php _e('Reset Password', 'smartcommerce'); ?></a>
            </div>
        </div>

    <?php
        return ob_get_clean();
    }
}

User::instance();