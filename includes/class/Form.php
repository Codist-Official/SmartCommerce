<?php 
namespace SmartCommerce;

class Form {

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
    public function __construct( $id = 0 ) 
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
     * Generate HTML form element
     *
     * @return string
     *
     * @param string $type
     * @param string $name
     * @param array $settings
     *
     * @since 1.0
     * @acecess public
     * @static
     */
    public static function generateElement( $type, $name, array $settings = array() )
    {
        $required = $settings['required'] ?? false;
        $readonly = $settings['readonly'] ?? false;
        $disabled = $settings['disabled'] ?? false;
        $placeholder = $settings['placeholder'] ?? '';
        $value = $settings['value'] ?? '';
        $id = $settings['id'] ?? $name;
        if(is_numeric($id)) $id = 'sc_form_'.$id;
        $class = $settings['class'] ?? '';
        $options = $settings['options'] ?? '';
        $data = $settings['data'] ?? '';
        $before = $settings['before'] ?? '';
        $after = $settings['after'] ?? '';
        $multiple = $settings['multiple'] ?? false;
        $style = $settings['style'] ?? '';
        $autocomplete = $settings['autocomplete'] ?? true;

        if ( !empty($options) && !is_array($options) ) $options = explode( ',', $options );

        $required_string = '';
        $readonly_string = '';
        $disabled_string = '';
        $multiple_string = '';
        $style_string = '';
        $autocomplete_string = '';

        if( $required ) $required_string = " required='required' aria-required='true' ";
        if( $readonly ) $readonly_string = " readonly='readonly' aria-readonly='true' ";
        if( $disabled ) $disabled_string = " disabled='disabled' aria-disabled='true' ";
        if( $multiple ) $multiple_string = " multiple='multiple' ";
        if( !empty($style) ) $style_string = " style='{$style}' ";
        $autocomplete_string = $autocomplete ? " autocomplete='on' " : "";

        $data_string = '';
        if( !empty( $data ) ){
            foreach( $data as $k=>$v ){
                $data_string .= " {$k} = '{$v}' ";
            }
        }

        $html = '';

        $type = strtolower(trim($type));

        switch ($type) {
            case 'tel':
            case 'text':
            case 'number':
            case 'email':
            case 'phone':
            case 'url':
            case 'hidden':
            case 'submit':
            case 'date':
            case 'datetime-local':
            case 'time':
            case 'password':
            case 'file':
            case 'color':
                $html = "<input name='{$name}' id='{$id}' class='{$class}' type='{$type}' placeholder='{$placeholder}' value='{$value}' {$readonly_string} {$required_string} {$disabled_string} {$data_string} {$multiple_string} {$style_string} {$autocomplete_string} >";
                break;

            case 'textarea':

                $html = "<textarea name='{$name}' id='{$id}' class='{$class}' {$required_string} {$disabled_string} {$readonly_string} {$data_string} placeholder='{$placeholder}' {$style_string}>{$value}</textarea>";
                break;

            case 'select':
            case 'dropdown':

                $html .= "<select id='{$id}' class='{$class}' name='{$name}' {$readonly_string} {$disabled_string} {$required_string} {$data_string} {$multiple_string} {$style_string}>";

                if( !empty($placeholder) ) $html .= "<option value=''>".__( $placeholder, 'smartcommerce')."</option>";
                if( !is_array($options) ) $options = explode(',', $options);

                if( !empty($options) ){
                    foreach( $options as $k => $v ){
                        $selected = $k == $value ? ' selected ' : '';
                        $html .= "<option value='{$k}' {$selected}>{$v}</option>";
                    }
                }
                $html .= "</select>";
                break;

            case 'radio':
            case 'checkbox':

                if ( $type === 'checkbox' && !str_contains( $name, '[]') ) $name = $name . '[]';

                if ( !empty($options) ){

                    $first_item = true;
                    foreach( $options as $k => $v ){

                        $k = trim($k);
                        $v = trim($v);

                        $required_string = $first_item ? $required_string : '';
                        $clean_id = preg_replace('/[^a-zA-Z0-9\s]/', '', $k );
                        if(is_numeric($clean_id)) $clean_id = $type.'_'.$clean_id;
                        $value = !is_array( $value ) ? explode(',', $value) : $value;
                        $checked = in_array( $k, $value ) ?  ' checked ' : '';
                        $html .= " <span class='{$type}-item-wrap item-{$clean_id}'>";
                            $html .= " <input value='{$k}' type='{$type}' name='{$name}' id='{$clean_id}' {$checked} {$required_string}> ";
                            $html .= " <label for='{$clean_id}'>". __( $v, 'smartcommerce' ) ."</label>";
                        $html .= "</span>";

                        $first_item = false;

                    }
                }
                break;

            case 'html':
                $html = $settings['html'] ?? '';
                break;

            case 'button':
                $type = $settings['type'] ?? 'button';
                $html = "<button type='{$type}' class='{$class}' id='{$id}' placeholder='{$placeholder}' {$disabled_string} {$readonly_string} {$required_string} {$data_string} {$style_string}>{$value}</button>";
                break;

            case 'wp_editor':
                $configs =  array(
                    'textarea_name' => $name,
                    'textarea_rows' => isset($settings['textarea_rows']) ? $settings['textarea_rows'] : 10,
                    'quicktags' => isset($settings['quicktags']) ? $settings['quicktags'] : false,
                    'media_buttons' => isset($settings['media_buttons']) ? $settings['media_buttons'] : false,
                    'tinymce' => isset($settings['tinymce']) ? $settings['tinymce'] : true,
                    'textarea_rows' => isset($settings['textarea_rows']) ? $settings['textarea_rows'] : 10,
                    'editor_class' => isset($settings['editor_class']) ? $settings['editor_class'] : 'sc-form-control',
                    'editor_height' => isset($settings['editor_height']) ? $settings['editor_height'] : 300,
                    'editor_width' => isset($settings['editor_width']) ? $settings['editor_width'] : '100%',
                );
                $html = wp_editor($value, $name, $configs);
                break;

            case 'ajax_file':
                $uniqid = 'uid_'. uniqid();
                $preview = $settings['preview'] ? 'yes' : 'no';
                $preview_html = '';
                if($preview == 'yes'){
                    $preview_html = "<div class='wp_ajax_upload_preview' data-uniqid='{$uniqid}'>";
                    $values = explode(',', $value);
                    $values = array_filter($values, function($v){
                        return $v != '';
                    });
                    foreach($values as $v){
                        $type = get_post_mime_type($v);
                        if(str_contains($type, 'image')){
                            $img_url = wp_get_attachment_url($v, 'sc-thumbnail');
                            $preview_html .= "<span data-uniqid='{$uniqid}' data-value='{$v}' style='background-image: url({$img_url})'><a data-value='{$v}' href='javasript:void(0)' class='delete-file'>x</a></span>";
                        }else{
                            $preview_html .= "<span>{$v}<a data-value='{$v}' href='javasript:void(0)' class='delete-file'>x</a></span>";
                        }
                    }
                    $preview_html .= "</div>";
                }
                $html = "<input data-preview='{$preview}' data-selector-uniqid='{$uniqid}' name='placeholder_name_{$name}' id='{$id}' class='wp_ajax_upload {$class}' type='file' placeholder='{$placeholder}' {$multiple_string} {$readonly_string} {$required_string} {$disabled_string} {$data_string} {$style_string}  >";
                $html .= "<input type='hidden' name='{$name}' id='wp_ajax_upload_{$id}' class='wp_ajax_upload_{$class}' value='{$value}' data-uniqid='{$uniqid}'>";
                $html .= $preview_html;
                break;
                
            default:
                break;
        }

        return $before . $html . $after ;

    }
}

Brand::instance();