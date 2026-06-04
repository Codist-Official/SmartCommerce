<?php 
namespace SmartCommerce;

class MetaPixel {
    private static $_instance;
    private $pixel_id;
    private $access_token;


    public function __construct() {
        include_once SMART_COMMERCE_CLASS_DIR . 'Settings.php';
    }

    public function wpInit()
    {
        $pixel_id = Settings::get('facebook_pixel_id', '');
        $access_token = Settings::get('facebook_access_token', '');
        $this->setPixelId($pixel_id);
        $this->setAccessToken($access_token);
        if(empty($this->pixel_id)) return;

        add_action('wp_head', [$this, 'metaPixel'], 1);

        add_action('wp_ajax_fb_meta_pixel_ajax', [$this, 'fbMetaPixelAjax']);
        add_action('wp_ajax_nopriv_fb_meta_pixel_ajax', [$this, 'fbMetaPixelAjax']);
    }


    public static function instance() 
    {
        if (self::$_instance == null) self::$_instance = new self();
        return self::$_instance; 
    }

    public static function hashValue($value) {
        return hash('sha256', $value);
    }


    public function metaPixel()
    {
        if(empty($this->pixel_id)) return;
        ob_start();
        $user_data = $this->generateUserData();
        $hashed_fields = array('first_name', 'last_name', 'email', 'full_name', 'phone', 'fn', 'ln', 'em', 'ph');
        foreach($user_data as $key => $value) {
            if(in_array($key, $hashed_fields)) $user_data[$key] = self::hashValue($value);
        }
        ?>
        <script>
            var fbmp = {};
            fbmp.user_data = <?php echo json_encode($user_data); ?>;
            fbmp.custom_data = <?php echo json_encode($this->generateCustomData()); ?>;
            fbmp.pixel_id = '<?php echo $this->getPixelId(); ?>';
            fbmp.event_id = 'evt_' + Date.now() + '_' + Math.floor(Math.random() * 100000);

        </script>

        <!-- fb pixel -->
        <?php if(!empty($this->getPixelId())): ?>
            <!-- Meta Pixel Code -->
            <script>
                !function(f,b,e,v,n,t,s)
                {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
                fbq('init', '<?php echo $this->getPixelId(); ?>', fbmp.user_data);
            </script>
            <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo $this->getPixelId(); ?>&ev=PageView&noscript=1"/></noscript>
            <!-- End Meta Pixel Code -->
        <?php endif; ?>

        <script>
                // Helper: send event to Pixel + WP REST (CAPI)
                function sendMetaEvent(eventName, customData={}, userData={}) {

                    // checking customData and userData is empty or not 
                    if(Object.keys(customData).length == 0) customData = fbmp.custom_data;
                    if(Object.keys(userData).length == 0) userData = fbmp.user_data;


                    // 1. Client-side Pixel event
                    fbq('track', eventName, customData, {eventID: fbmp.event_id});

                    // 2. Server-side via WordPress REST API
                    var formData = new FormData();
                    formData.append('event_name', eventName);
                    formData.append('event_id', fbmp.event_id);
                    formData.append('user_data', JSON.stringify(userData));
                    formData.append('custom_data', JSON.stringify(customData));
                    formData.append('action', 'fb_meta_pixel_ajax');

                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: formData,  
                    })
                    .then(res => res.json())
                    .then(data => console.log("CAPI Response: " + eventName + " => ", data, userData, customData))
                    .catch(err => console.error("CAPI Error:", err));
                }
                sendMetaEvent('PageView');
                sendMetaEvent('ViewContent');
        </script>
        <?php
        echo ob_get_clean();
    }

    public function getPixelId() {
        return $this->pixel_id;
    }

    public function getAccessToken() {
        return $this->access_token;
    }

    public function setPixelId($pixel_id) {
        $this->pixel_id = $pixel_id;
    }

    public function setAccessToken($access_token) {
        $this->access_token = $access_token;
    }

    public function generateUserData()
    {
        $user_data = array();
        $user_data['client_ip_address'] = self::getClientIp();
        $user_data['client_user_agent'] = $_SERVER['HTTP_USER_AGENT'];

        $user_id = get_current_user_id();
        $fbp = $this->getFbpCookie();
        $fbc = $this->getFbcCookie();
        if(!empty($fbp)) $user_data['fbp'] = $fbp;
        if(!empty($fbc)) $user_data['fbc'] = $fbc;

        if($user_id) {
            $userdata = get_userdata($user_id);
            $first_name = self::normalize('first_name', $userdata->first_name);
            $last_name = self::normalize('last_name', $userdata->last_name);
            $email = self::normalize('email', $userdata->user_email);
            $user_data['external_id'] = $user_id;
            if(!empty($first_name)) $user_data['fn'] = $first_name;
            if(!empty($last_name))  $user_data['ln'] = $last_name;
            if(!empty($email))  $user_data['em'] = $email;
            if(empty($user_data['fn'])) $user_data['fn'] = $userdata->display_name;
        }
        if(isset($user_data['ph'])) unset($user_data['ph']);
        if(isset($user_data['phone'])) unset($user_data['phone']);
        $user_data['ph'] = $user_data['client_ip_address'];
        return $user_data;
    }

    public function generateCustomData()
    {
        $protocol = is_ssl() ? 'https' : 'http';
        $custom_data = array();
        $custom_data['plugin'] = 'SmartCommerce';
        $custom_data['domain'] = $_SERVER['HTTP_HOST'] ?? '';
        $custom_data['post_id'] = get_queried_object_id();
        $custom_data['post_name'] = esc_html(get_queried_object()->post_title);
        $custom_data['post_url'] = get_permalink(get_queried_object_id());
        $custom_data['post_type'] = is_singular() ? get_queried_object()->post_type : '';
        $custom_data['action_source'] = 'website';
        $custom_data['event_source_url'] = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $custom_data['currency'] = 'BDT';
        $custom_data['value'] = 0;
        $custom_data['event_time'] = time();
        if(is_singular('sc_product')) {
            $value = (float) get_post_meta(get_queried_object_id(), 'selling_price', true);
            $custom_data['content_ids'] = [get_queried_object_id()];
            $custom_data['content_type'] = 'product';
            $custom_data['value'] = $value;
            $cats = get_the_terms(get_queried_object_id(), 'sc_product_category');
            if(!empty($cats)) {
                $custom_data['content_category'] = implode(',', array_map(function($cat) {
                    return esc_html($cat->name);
                }, $cats));
            }
        }
        return $custom_data;
    }

    public function getFbpCookie()
    {
        $fbp = $_COOKIE['_fbp'] ?? '';
        if(empty($fbp)) $fbp = $this->setFbpCookie();
        return $fbp;
    }

    public function setFbpCookie()
    {
        $time = strtotime(current_time('mysql'));
        $rand = rand(1000000000, 9999999999);
        $fbp = "fb.1." . $time . "." . $rand;
        setcookie('_fbp', $fbp, time() + 30 * 24 * 60 * 60, '/');
        return $fbp;
    }

    public function getFbcCookie()
    {
        $fbc = $_COOKIE['_fbc'] ?? '';
        if(empty($fbc)) $fbc = $this->setFbcCookie();
        return $fbc;
    }
    
    public function setFbcCookie()
    {
        $fbclid = $_GET['fbclid'] ?? '';
        if(empty($fbclid) && !empty($_COOKIE['_fbc'])) {
            return $_COOKIE['_fbc'];
        }
        $time = current_time('timestamp', true);
        $fbc = "fb.1." . $time . "." . $fbclid;
        setcookie('_fbc', $fbc, time() + (90 * DAY_IN_SECONDS), '/');
        return $fbc;
    }

    public function fbMetaPixelAjax()
    {

        $hashed_fields = array('first_name', 'last_name', 'email', 'full_name', 'phone', 'fn', 'ln', 'em', 'ph');
        if(isset($user_data['autoConfig'])) unset($user_data['autoConfig']);
        $event_name = stripslashes($_REQUEST['event_name'] ?? '');
        $event_id = stripslashes($_REQUEST['event_id'] ?? '');

        $user_data = stripslashes($_REQUEST['user_data'] ?? []);
        $user_data = json_decode($user_data, true);

        if(!empty($user_data)){
            foreach($user_data as $key => $value) {
                if(in_array($key, $hashed_fields)) $user_data[$key] = self::hashValue($value);
            }
        }


        $custom_data = stripslashes($_REQUEST['custom_data'] ?? []);
        $custom_data = json_decode($custom_data, true);

        $url = "https://graph.facebook.com/v25.0/{$this->getPixelId()}/events?access_token={$this->getAccessToken()}";

        $event = array(
            "event_name"    => $event_name,
            "event_time"    => time(),
            "event_id"      => $event_id, // deduplication
            "action_source" => "website",
            "user_data"     => $user_data,
            "custom_data"   => $custom_data
        );

        $response = wp_remote_post($url, [
            "headers" => ["Content-Type" => "application/json"],
            "body"    => json_encode(["data" => [$event]])
        ]);
    
        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }
    
        $response = json_decode(wp_remote_retrieve_body($response), true);
        wp_send_json_success($response);
    }

    public static function getClientIp()
    {

        $headers = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($headers as $h) {
            if (!empty($_SERVER[$h])) {
                // handle lists (X-Forwarded-For)
                $ips = explode(',', $_SERVER[$h]);
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                        return $ip; // ✅ Prefer IPv6
                    }
                }
                // fallback IPv4 if no IPv6 found
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                        return $ip;
                    }
                }
            }
        }
        return null;
    }


    /**
     * Default normalizer for common PII that Facebook recommends before hashing:
     * - Email: trim + lowercase
     * - Phone: remove non-digits
     * - Names/other: trim + lowercase + collapse spaces
     *
     * You can pass your own normalizer callback($fieldName, $value) if needed.
     */
    public function normalize(string $field, $value) {
        if (!is_string($value)) return $value;

        $v = trim($value);
        switch (strtolower($field)) {
            case 'email':
                return strtolower($v);
            case 'phone':
            case 'phonenumber':
            case 'phone_number':
                // keep digits only (you may want to ensure country code present)
                return preg_replace('/\D+/', '', $v);
            case 'firstname':
            case 'lastname':
            case 'fullname':
            case 'name':
                // collapses multiple spaces, lowercase
                return preg_replace('/\s+/', ' ', strtolower($v));
            default:
                // generic: trim & lowercase (optional)
                return strtolower($v);
        }
    }

    /**
     * Detect if a string looks like a SHA-256 hash.
     * Accepts either:
     *  - 64 hex chars (lower/upper) => hex representation
     *  - base64 of 32 bytes => length 44 and valid base64 (ends with ==)
     */
    public static function looksLikeSha256(string $s): bool {
        // Strip whitespace just in case
        $s = trim($s);

        // 64 hex chars
        if (preg_match('/\A[0-9a-fA-F]{64}\z/', $s)) {
            return true;
        }

        // base64 of 32 bytes -> 44 chars typically ending with '=='
        // We'll validate base64 and that decoded length == 32 bytes
        if (strlen($s) === 44 && base64_decode($s, true) !== false) {
            $decoded = base64_decode($s, true);
            if ($decoded !== false && strlen($decoded) === 32) {
                return true;
            }
        }

        return false;
    }

    /**
     * Hash a single scalar value if needed (returns hexadecimal 64-char hash).
     * If value already appears hashed (hex or base64), returns it unchanged.
     * Accepts optional normalize callback function($fieldName, $value) -> string.
     */
    public static function ensureSha256ForValue($value, string $fieldName = '') 
    {

        if (!is_string($value) && !is_numeric($value)) {
            // convert other scalars to string
            $value = (string)$value;
        }

        $value = (string)$value;
        $value = trim($value);

        // Apply normalization (if fieldName provided)
        if ($fieldName !== '') {
            $value = self::normalize($fieldName, $value);
        }

        // If it already looks like SHA-256 (hex or base64), return as-is.
        if (self::looksLikeSha256($value)) {
            return $value;
        }

        // Otherwise hash and return lowercase hex
        return hash('sha256', $value);
    }

    /**
     * Recursively ensure SHA-256 for an array of fields.
     * $data can be a scalar string or an associative array ['email'=>'foo', 'phone'=>'...']
     * $normalize callback will receive ($fieldName, $value).
     *
     * Example usage:
     * $out = ensure_sha256($input, 'default_normalize');
     */
    public static function ensureSha256($data)
    {
        if (is_array($data)) {
            $out = [];
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    $out[$k] = self::ensureSha256($v); // recurse
                } else {
                    $out[$k] = self::ensureSha256ForValue($v, (string)$k);
                }
            }
            return $out;
        } else {
            // scalar
            return self::ensureSha256ForValue($data, '');
        }
    }
}

$mp = MetaPixel::instance();
$mp->wpInit();
