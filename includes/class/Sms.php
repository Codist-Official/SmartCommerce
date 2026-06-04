<?php
namespace SmartCommerce;

defined('ABSPATH') || exit;

class Sms {

    private static $_instance;
    public $table = 'sms';
    public $posts_per_page = 50;

    /**
     * Initialize the class
     * 
     * @return void
     * @since 1.0.0
     * @access public
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
     * 
     * @since 1.0.0
     * @access public
     */
    public function __construct()
    {
        add_action( 'wp_ajax_get_sms_balance', [$this, 'getBalance'] );
        add_action( 'wp_ajax_nopriv_get_sms_balance', [$this, 'getBalance'] );

        global $wpdb;
        $this->table = $wpdb->prefix . $this->table;
    }
    

    /**
     * Send SMS 
     * 
     * @return array 
     * 
     * @since 1.0
     * @access public
     */
    public static function send($mobile, $message='')
    {
        $rate = Settings::get('sms_rate', 0.4);
        $api_key = Settings::get('sms_api_key');
        $sender = Settings::get('sms_sender');
        $footer = Settings::get('sms_footer');
        $mobile = (string) sanitize_text_field($mobile);
        $message = sanitize_text_field($message) . "\n{$footer}";
        $message = trim($message);

        if(!str_contains($mobile, '+880')) $mobile = '+880' . $mobile;
        $mobile = str_replace('+8800', '+880', $mobile);

        $url = "http://bulksmsbd.net/api/smsapi?api_key={$api_key}&type=text&number={$mobile}&senderid={$sender}&message=". urlencode($message);
        $response = wp_remote_get($url);
        if(is_wp_error($response)) return $response;
        $response = json_decode($response['body'], true);

        $status = $response['response_code'];
        $response_text = $response['success_message'];
        $sms_count = self::countLength($message);

        global $wpdb;
        $args = array(
            'mobile' => $mobile,
            'message' => $message,
            'status' => $status,
            'sms_count' => $sms_count,
            'rate' => $rate,
            'sender' => get_current_user_id(),
            'record_time' => current_time('mysql'),
        );

        $insert = $wpdb->insert($wpdb->prefix . 'sms', $args);
        return array(
            'sms_status' => $status,
            'sms_count' => $sms_count,
            'sms_rate' => $rate,
            'message' => $message,
            'mobile' => $mobile,
            'sender' => get_current_user_id(),
            'record_id' => is_wp_error($insert) ? $wpdb->last_error : $wpdb->insert_id,
            'response_text' => $response_text,
        );
    }

    /**
     * Count Lentgh of SMS 
     * 
     * @return int 
     * 
     * @params string $message
     * 
     * @since 1.0.0
     * @access public
     */
    public static function countLength($message='')
    {
        // 160 characters = 1 SMS
        return ceil(strlen($message) / 160);
    }


    /**
     * Check Balance 
     * 
     * @return float 
     *
     */
    public static function checkBalance()
    {
        $api_key = Settings::get('sms_api_key');
        $url = "http://bulksmsbd.net/api/getBalanceApi?api_key={$api_key}";
        $response = wp_remote_get($url);
        if(is_wp_error($response)) return 0;
        $response = json_decode($response['body'], true);
        return number_format($response['balance'], 2);

    }

    public function getBalance()
    {
        echo self::checkBalance();
        die();
    }

    /**
     * Get List HTML 
     * 
     * @return string 
     * 
     * @since 1.0
     * @access public 
     */
    public function getList()
    {
        $count_pages = ceil($this->countTotalRows() / $this->posts_per_page);
        $paged = max( 1, max(get_query_var('paged'), get_query_var('page')));
        $offset = ($paged - 1) * $this->posts_per_page;
        
        global $wpdb;
        $qry = "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT {$offset}, {$this->posts_per_page} ";
        $results = $wpdb->get_results($qry);



        ob_start();
        ?>
        <style>.sc-sms-select{width: 150px !important; margin-right: 10px !important; }</style>
        <div class="sms-send-options">
            <form action="" method="post" class='sc-form sc-ajax-form'>
                <div class="sc-form-row">
                    <label for="send_to"><?php _e('Send To', 'smartcommerce'); ?></label>
                    <?php 
                        echo Form::generateElement('select', 'send_type', array(
                            'options' => array(
                                'mobile_numbers' => __('Mobile numbers', 'smartcommerce'),
                                'all_customers' => __('All customers', 'smartcommerce'),
                                'order_status' => __('Order status', 'smartcommerce'),
                            ),
                            'class' => 'sc-sms-select',
                            'placeholder' => __('Select option', 'smartcommerce'),
                            'required' => true,
                        ));
                        echo Form::generateElement('hidden', 'action', array('value' => 'smartcommerce_ajax'));
                        echo Form::generateElement('hidden', 'ajax_action', array('value' => 'showSmsSendScreen'));
                        echo Form::generateElement('hidden', '_wpnonce', array('value' => wp_create_nonce('smartcommerce')));
                        echo Form::generateElement('hidden', 'success_callback', array('value' => 'popupData'));
                        echo Form::generateElement('submit', 'submit', array('value' => __('Submit', 'smartcommerce')));
                    ?>
                </div>
            </form>
        </div>

        <?php 
        if(empty($results)){
            _e('No SMS found', 'smartcommerce');
            return ob_get_clean();
        }
        
        ?>
        <div class="sc-table-wrap">
            <table class="sc-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mobile</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>SMS Count</th>
                        <th>Rate</th>
                        <th>SMS Cost</th>
                        <th>Record Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($results as $result){ ?>
                        <tr>
                            <td><?php echo $result->id; ?></td>
                            <td><a href="tel:<?php echo $result->mobile; ?>"><?php echo $result->mobile; ?></a></td>
                            <td><?php echo nl2br($result->message); ?></td>
                            <td><?php echo $result->status == 202 ? 'Sent' : 'Failed'; ?></td>
                            <td><?php echo $result->sms_count; ?></td>
                            <td><?php echo $result->rate; ?></td>
                            <td><?php echo $result->rate * $result->sms_count; ?></td>
                            <td><?php echo date('h:i A, d/m/Y', strtotime($result->record_time)); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php
        echo SmartCommerce::getPagination($count_pages);
        return ob_get_clean();
    }

    /**
     * Count total rows 
     * 
     * @return int 
     * 
     * @since 1.0
     * @access public 
     */
    public function countTotalRows()
    {
        global $wpdb;
        $qry = "SELECT COUNT(*) FROM {$this->table}";
        return $wpdb->get_var($qry);
    }

    /**
     * SMS screen 
     * 
     * @return string 
     * 
     * @since 1.0
     * @access public 
     * @static 
     */
    public static function getSendSmsScreen( $type = '' )
    {
        if($type == '') return __('No SMS option selected', 'smartcommerce');
        $customers = [];
        if($type == 'all_customers'){
            global $wpdb;
            $qry = "SELECT * FROM {$wpdb->usermeta} WHERE meta_key = 'mobile' ";
            $results = $wpdb->get_results($qry, ARRAY_A);
            $values = array_column($results, 'meta_value');
            $customers = array_unique($values);
        }
        ob_start();
        ?>
        <?php if($type == 'all_customers'): ?>
            <script>
                smartcommerce.count_sms_numbers = <?php echo count($customers); ?>;
                jQuery(document).ready(function($){
                    $('.total-numbers').text('<?php echo count($customers); ?>');
                });
            </script>
        <?php endif; ?>
        <form action="" class="sc-form sc-ajax-form">
            <div class="sc-flex-wrap send-sms-screen">
                <div class="flex-6">
                    <?php if($type == 'mobile_numbers' || $type == 'all_customers'){ ?>
                        <div class="sc-form-row">
                            <label for="sms_mobile_numbers"><?php _e('Mobile Numbers', 'smartcommerce'); ?></label>
                            <?php echo Form::generateElement('textarea', 'sms_mobile_numbers', array(
                                'placeholder' => __('Enter mobile numbers (1 number each line)', 'smartcommerce'), 
                                'required'=>true,
                                'class' => 'sc-textarea',
                                'style' => 'width: 100%;',
                                'id' => 'sms_mobile_numbers',
                                'value' => $type == 'all_customers' ? implode("\r\n", $customers) : '',
                            )); ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="flex-6">
                <div class="sc-form-row">
                        <label for="sms_message"><?php _e('Message', 'smartcommerce'); ?></label>
                        <?php echo Form::generateElement('textarea', 'sms_message', array(
                            'required'=>true,
                            'placeholder' => __('Enter message', 'smartcommerce'),
                            'rows' => 10,
                            'cols' => 50,
                            'class' => 'sc-textarea',
                            'style' => 'width: 100%;',
                            'required'=>true,
                            'id' => 'sms_message',
                        )); ?>
                        <div class='sms-stats'>
                            <?php _e('Total Numbers', 'smartcommerce'); ?>: <span class='total-numbers'>0</span>
                            <?php _e('Total SMS', 'smartcommerce'); ?>: <span class='total-sms'>0</span>
                            <?php _e('Total Cost', 'smartcommerce'); ?>: <span class='total-cost'>0</span>
                        </div>
                        <?php echo Form::generateElement('hidden', 'action', array('value' => 'smartcommerce_ajax')); ?>
                        <?php echo Form::generateElement('hidden', 'ajax_action', array('value' => 'sendBulkSms')); ?>
                        <?php echo Form::generateElement('hidden', '_wpnonce', array('value' => wp_create_nonce('smartcommerce'))); ?>
                        <?php echo Form::generateElement('hidden', 'before_send_callback', array('value' => 'scConfirm')); ?>
                        <?php echo Form::generateElement('hidden', 'type', array('value' => $type)); ?>
                        <?php //echo Form::generateElement('hidden', 'success_callback', array('value' => 'popupData')); ?>
                        <?php echo Form::generateElement('submit', 'submit', array('value' => __('Send SMS', 'smartcommerce'))); ?>
                        </div>
                </div>
            </div>
        </form>
        <?php 
        return ob_get_clean();
    }

}

$sms = Sms::instance();