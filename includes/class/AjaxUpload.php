<?php
defined( 'ABSPATH' ) || die();

if(!class_exists('AjaxUpload')){
        class AjaxUpload
        {

            private static $_instance;

            /**
             * Initialize instance
             */
            public static function instance()
            {

                if ( is_null( self::$_instance ) ) self::$_instance = new self();
                return self::$_instance;

            }

            public function __construct()
            {
                add_action('wp_ajax_custom_ajax_upload', [ $this, 'uploadCallback' ]);
                add_action('wp_ajax_nopriv_custom_ajax_upload', [ $this, 'uploadCallback' ] );
                add_action('wp_head', [ $this, 'headHtml' ] );
                add_action('admin_enqueue_scripts', [ $this, 'my_enqueue' ] );
                wp_localize_script('jquery', 'wpAjaxUpload',  ['filesProcessing' => 0 ]);
            }



            public function uploadCallback() {

                // Check if the nonce is set
                if ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'wp_ajax_upload' ) ) {

                    $uploaded_files = $_FILES['files'];
                    $attachments = array();
                    $ids = array();

                    foreach ($uploaded_files['name'] as $key => $value) {

                        if ($uploaded_files['name'][$key]) {
                            $file = array(
                                'name'     => $uploaded_files['name'][$key],
                                'type'     => $uploaded_files['type'][$key],
                                'tmp_name' => $uploaded_files['tmp_name'][$key],
                                'error'    => $uploaded_files['error'][$key],
                                'size'     => $uploaded_files['size'][$key]
                            );

                            // Set up the array of arguments for the media uploader
                            $upload_overrides = array( 'test_form' => false );
                            $movefile = wp_handle_upload( $file, $upload_overrides );

                            if ( $movefile && empty( $movefile['error'] ) ) {
                                // File is successfully uploaded
                                $attachment = array(
                                    'post_mime_type' => $movefile['type'],
                                    'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $movefile['file'] ) ),
                                    'post_content' => '',
                                    'post_status' => 'inherit'
                                );

                                // Insert the attachment into the media library
                                $attach_id = wp_insert_attachment( $attachment, $movefile['file'] );

                                // Generate attachment metadata and update the attachment
                                $attach_data = wp_generate_attachment_metadata( $attach_id, $movefile['file'] );
                                wp_update_attachment_metadata( $attach_id, $attach_data );

                                // Save attachment ID to the array
                                $attachments['files'][] = array(
                                    'id' => $attach_id,
                                    'url' => wp_get_attachment_url( $attach_id )
                                );
                                $ids[] = $attach_id;
                            } else {
                                // Error handling
                                $attachments[] = 'Error uploading file: ' . $file['name'];
                            }

                        }
                    }

                    $attachments['ids'] = implode(',', $ids );

                    // Return array of attachment IDs
                    return wp_send_json_success($attachments);

                } else {
                    // If nonce is not set
                    return wp_send_json_error('Security check failed!');
                }

            }

            public function headHtml()
            {

                ob_start();
                ?>
                <div id="wp_ajax_processing_message" class="wp_ajax_processing_message" style="display: none; width: 100%; height: 30px; background:green; color: white; text-align: center; line-height: 30px; overflow:hidden; z-index: 999999!important; position: fixed; bottom: 0; left: 0;font-size:12px;">
                    Processing <span id="wp_ajax_processing_message_count">0</span> files...
                </div>
                <style>
                    .wp_ajax_upload_preview{
                        display: flex;
                        flex-wrap: wrap;
                        gap: 5px;
                        margin-top:10px;
                        position: relative;
                    }
                    .wp_ajax_upload_preview span{
                        width: 50px;
                        height: 50px;
                        background: #f0f0f0;
                        border: 1px solid #ccc;
                        border-radius: 5px;
                        position: relative;
                        background-size: cover;
                        background-position: center;
                        background-repeat: no-repeat;
                    }
                    .wp_ajax_upload_preview span img{
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                    }
                    .wp_ajax_upload_preview span:hover{
                        border: 1px solid #000;
                    }
                    .wp_ajax_upload_preview span.loading::before{
                        content: 'loading...';
                        width: 100%;
                        height: 100%;
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        z-index: 10;
                        transform: translate(-50%, -50%);
                        font-size: 10px;
                    }
                    .wp_ajax_upload_preview a.delete-file{
                        position: absolute;
                        top: -6px;
                        right: -6px;
                        width: 12px;
                        height: 12px;
                        background: red;
                        color: #fff;
                        border-radius: 100%;
                        text-align: center;
                        line-height: 12px;
                        font-size: 10px;
                        cursor: pointer;
                        display: inline-block;
                        z-index: 999;
                        text-decoration: none;
                    }
                </style>
                <script>
                    if( typeof $j === 'undefined' ) var $j = jQuery;
                    wpAjaxUpload._wpnonce = '<?php echo wp_create_nonce('wp_ajax_upload'); ?>';
                    wpAjaxUpload.ajax_url = '<?php echo admin_url('admin-ajax.php'); ?>';
                    jQuery(document).ready(function(){

                        // Delete File 
                        $j(document).on('click', '.wp_ajax_upload_preview .delete-file', function(e){
                            e.preventDefault();
                            let uniqid = $j(this).parent().data('uniqid');
                            let curVal = $j(`:input[data-uniqid='${uniqid}']`).val();
                            let value = $j(this).data('value');
                            let newVal = curVal != 'undefined' ? curVal.replace(value, '') : '';
                            $j(`:input[data-uniqid='${uniqid}']`).val(newVal);
                            $j(this).closest('span').remove();
                        });

                        $j(document).on('change', '.wp_ajax_upload', function(e){
                            e.preventDefault();
                            let uniqid = $j(this).data('selector-uniqid');
                            let curVal = $j(`input[data-uniqid='${uniqid}']`).val();
                            let preview = $j(this).data('preview');
                            let targetName = $j(this).data('target-name');
                            let targetClass = $j(this).data('target-class');
                            let files_data = $j(this).prop('files');
                            let isMultiple = $j(this).prop('multiple');
                            let submitButton = $j(this).closest('form').find(':input[type="submit"]');
                            let submitButtonText = submitButton.html();
                            wpAjaxUpload.filesProcessing = parseInt(wpAjaxUpload.filesProcessing) + parseInt(files_data.length);
                            let form_data = new FormData();
                            $j.each(files_data, function(i, file){
                                form_data.append('files[]', file);
                            });
                            form_data.append('action', 'custom_ajax_upload');
                            form_data.append('_wpnonce', wpAjaxUpload._wpnonce  );

                            $j.ajax({
                                url: wpAjaxUpload.ajax_url,
                                type: 'post',
                                data: form_data,
                                contentType: false,
                                processData: false,
                                dataType:'JSON',
                                beforeSend: function(){
                                    if(wpAjaxUpload.loading){
                                        wpAjaxUpload.loading.show();
                                    }
                                    if(wpAjaxUpload.filesProcessing > 0){
                                        // Disable submit button 
                                        submitButton.html('Files are uploading...').prop('disabled', true);
                                        $j('#wp_ajax_processing_message').show();
                                        $j('#wp_ajax_processing_message_count').text(wpAjaxUpload.filesProcessing);
                                    }
                                    if( preview == 'yes' ){
                                        $j(`.wp_ajax_upload_preview[data-uniqid=${uniqid}]`).show();
                                        if(isMultiple){
                                            for(i = 0; i < files_data.length; i++){
                                                $j(`.wp_ajax_upload_preview[data-uniqid=${uniqid}]`).append(`<span class="processing"></span>`);
                                            }
                                        }else{
                                            $j(`.wp_ajax_upload_preview[data-uniqid=${uniqid}]`).html(`<span class="processing"></span>`);
                                        }
                                    }
                                },
                                success: function(res){
                                    if(wpAjaxUpload.loading){
                                        wpAjaxUpload.loading.hide();
                                    }
                                    if(res.success){
                                        wpAjaxUpload.filesProcessing = parseInt(wpAjaxUpload.filesProcessing) - parseInt(res.data.files.length);
                                        if(wpAjaxUpload.filesProcessing == 0){
                                            $j('#wp_ajax_processing_message').hide();
                                            submitButton.html(submitButtonText).prop('disabled', false);
                                        }
                                        if(preview == 'yes'){
                                            let target = $j(`.wp_ajax_upload_preview[data-uniqid=${uniqid}]`).find(`span.processing`);
                                            for(i = 0; i < res.data.files.length; i++){
                                                let img = res.data.files[i].url;
                                                console.log(img);
                                                // <img src="${img}">
                                                $j(target[i]).attr('data-value', res.data.files[i].id).css('background-image', `url(${img})`).html(`<a data-value='${res.data.files[i].id}' value='javasript:void(0)' class="delete-file">x</a>`);
                                                $j(target[i]).removeClass('processing');
                                            }
                                        }
                                        if(isMultiple){
                                            let newVal = curVal != '' ? curVal + ',' + res.data.ids : res.data.ids;
                                            $j(`input[data-uniqid='${uniqid}']`).attr('value', newVal);
                                        }else{
                                            $j(`input[data-uniqid='${uniqid}']`).val(res.data.ids);
                                        }
                                    }
                                }
                            });
                        });
                    });
                </script>
                <?php
                echo ob_get_clean();

            }

            public function my_enqueue()
            {
                // wp_enqueue_media();
            }
        }
}

AjaxUpload::instance();