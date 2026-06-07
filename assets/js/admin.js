if(typeof $j == 'undefined') $j = jQuery;

if(typeof preventDefault == 'undefined') {
    function preventDefault(e){
        e.preventDefault();
        e.stopPropagation();
    }
}

$j(document).ready(function($){

    // Ajax link 
    $(document).on('click', '.sc-ajax-link', function(e){
        preventDefault(e);
        var link = $(this);
        var data = link.data();
        // add action to data
        data.action = 'smartcommerce_ajax';
        data._wpnonce = smartcommerce._wpnonce;
        var params = new URLSearchParams(data);
        var beforeSendCallback = params.get('before_send_callback');
        var successCallback = params.get('success_callback');
        var errorCallback = params.get('error_callback');
        var completeCallback = params.get('complete_callback');
        var ajaxAction = params.get('ajax_action');

        $j.ajax({
            url: smartcommerce.ajax_url,
            type: 'POST',
            data: data,
            beforeSend: function(){
                if( beforeSendCallback != 'undefined' && beforeSendCallback != null && typeof window[beforeSendCallback] == 'function'){
                    if(window[beforeSendCallback]() == false) return false;
                }
                showLoading();
            },
            success: function(r){
                if( successCallback != 'undefined' && successCallback != null && typeof window[successCallback] == 'function'){
                    window[successCallback](r);
                } else {
                    hideLoading();
                    showStatus( r.data.status ? 'success' : 'error', r.data.message);
                }
            },
            error: function(r){
                clog(r);
            },
            complete: function(){

            }
        });
    });

    // Ajax Form
    $(document).on('submit', '.sc-ajax-form', function(e){
        preventDefault(e);
        var form = $(this);
        var data = form.serialize();
        var params = new URLSearchParams(data);
        var beforeSendCallback = params.get('before_send_callback');
        var successCallback = params.get('success_callback');
        var errorCallback = params.get('error_callback');
        var completeCallback = params.get('complete_callback');
        var ajaxAction = params.get('ajax_action');

        $j.ajax({
            url: smartcommerce.ajax_url,
            type: 'POST',
            data: data,
            beforeSend: function(){       
                if( beforeSendCallback != 'undefined' && beforeSendCallback != null && typeof window[beforeSendCallback] == 'function'){
                    if(window[beforeSendCallback](data) == false) return false;
                }
                showLoading();
            },
            success: function(r){
                if( successCallback != 'undefined' && successCallback != null && typeof window[successCallback] == 'function'){
                    window[successCallback](r);
                } else {
                    hideLoading();
                    showStatus( r.data.status ? 'success' : 'error', r.data.message);
                }
            },
            error: function(r){
                clog(r);
            },
            complete: function(){
                // form.find(':input[type="submit"]').prop('disabled', true).html('Processed');

            }
        })

    });

    // hiding stock unit and quantity field 
    if($j("[name='stock_type']").length){
        if($j("[name='stock_type']").val() == 'limited'){
            $j("[data-field='stock_quantity'],[data-field='stock_unit']").show();
        }else{
            $j("[data-field='stock_quantity'],[data-field='stock_unit']").hide();
        }
    }
    $j(document).on('change', "[name='stock_type']", function(){
        var stock_type = $(this).val();
        if(stock_type == 'limited'){
            $j("[data-field='stock_quantity'],[data-field='stock_unit']").show();
        }else{
            $j("[data-field='stock_quantity'],[data-field='stock_unit']").hide();
        }
    });

    function updateSellingPrice(){
        var reg = parseFloat($j("[name='regular_price']").val());
        let dis = parseFloat($j("[name='discount']").val());
        let price = reg - dis;
        if(price < 0) price = 0;
        $j("[name='selling_price']").val(price);
    }

    // updating selling price on change of regular price or discount
    $j(document).on('change keyup keydown', "[name='regular_price'],[name='discount']", updateSellingPrice);

    // Add product variation row 
    function addProductVarRow(){
        let currentHtml = $j('.sc-product-variation-list').html();
        if(currentHtml == ''){
            let currentHtml = `
            <li class='header-row'>
                <div class='var-item' data-item-name='variation_name'>Varitation <br>Name</div>
                <div class='var-item' data-item-name='variation_images'>Images</div>
                <div class='var-item' data-item-name='variation_price'>Selling <br>Price</div>
                <div class='var-item' data-item-name='variation_sku'>SKU</div>
            <div class='var-item' data-item-name='variation_stock'>Stock <br>Quantity</div>
                <div class='var-item' data-item-name='variation_actions'>Actions</div>
            </li>`;
            $j('.sc-product-variation-list').prepend(currentHtml);
            
        }
        let uid = generateUid();
        let html = `
        <li data-uid='${uid}'>
            <div class='var-item' data-item-name='variation_name'>
                <input type='text' name='variation_name[]' class='sc-form-control' placeholder='Variation Name' required>
                <input type='hidden' name='variation_uid[]' value='${uid}'>
            </div>
            <div class='var-item' data-item-name='variation_images'>
                <input type='file' name='variation_images[]' class='sc-form-control' placeholder='Variation Image' multiple accept='image/*'>
            </div>
            <div class='var-item' data-item-name='variation_price'>
                <input type='text' name='variation_price[]' class='sc-form-control' placeholder='Price' required>
            </div>
            <div class='var-item' data-item-name='variation_sku'>
                <input type='text' name='variation_sku[]' class='sc-form-control' placeholder='SKU'>
            </div>
            <div class='var-item' data-item-name='variation_stock'>
                <input type='number' min='0' name='variation_stock[]' class='sc-form-control' value='99999' placeholder='Stock Quantity'>
            </div>
            <div class='var-item' data-item-name='variation_actions'>
                <a href='javascript:void(0)' class="sc-icon-wrap copy-product-var">
                    <i class="fa fa-plus" style="font-size: 12px;"></i>
                </a>
                <a href='javascript:void(0)' class="sc-icon-wrap delete-product-var">
                    <i class="fa fa-trash" style="font-size: 12px;"></i>
                </a>
            </div>
        </li>`;
        $j('.sc-product-variation-list').append(html);
    }

    // Add row when btn clicked 
    $j(document).on('click', '#add_product_variation_btn', function(){
        addProductVarRow();
    });

    // duplicate row 
    $j(document).on('click', '.copy-product-var', copyProductVar);

    // Copy Product Var Row 
    function copyProductVar(e){
        e.preventDefault();
        e.stopPropagation();
        let uid = $(this).closest('li').data('uid');
        let html = $j(`li[data-uid='${uid}']`).clone();
        let newUid = generateUid();
        // replace uid with new uid for input and hidden input
        html.find('input[name="variation_uid[]"]').val(newUid); 
        // replace li uid with new id 
        html.attr('data-uid', newUid);
        $j('.sc-product-variation-list').append(html);
    }

    // delete row 
    $j(document).on('click', '.delete-product-var', deleteProductVar);

    function deleteProductVar(e){
        e.preventDefault();
        e.stopPropagation();
        if(!confirm("Are you sure to delete this variation?")) return;
        let uid = $(this).closest('li').data('uid');
        $j(`li[data-uid='${uid}']`).slideUp(300, function(){
            $j(this).remove();
        });
    }




});

// confirm before proceed 
function scConfirm(message='Are you sure you want to proceed?'){
    return confirm(message);
}

// duplicate post success callback 
function duplicatePostSuccessCallback(res){
    clog(res);
    hideLoading();
    showStatus( res.success ? 'success' : 'error', res.data.message);
    if(res.success){
        setTimeout(function(){
            window.location.href = smartcommerce.site_url + '/dashboard/?submenu=edit&id=' + res.data.post_id + '&type=' + res.data.post_type;
        }, 1000);
    }
}

function deletePostSuccessCallback(res){
    clog(res);
    hideLoading();
    showStatus( res.success ? 'success' : 'error', res.data.message);
    if(res.success){
        $j(`tr[data-id='${res.data.post_id}']`).slideUp(300, function(){
            $j(this).remove();
        });
    }
}

function publishPostBeforeSendCallback(data){
    return true;
}

function publishPostSuccessCallback(res){
    clog(res);
    hideLoading();
    showStatus( res.data.status ? 'success' : 'error', res.data.message);
    return;
    if(res.data.status){
        setTimeout(function(){
            window.location.href = smartcommerce.site_url + '/dashboard/?submenu=' + res.data.post_type;
        }, 1000);
    }
}
function editPostBeforeSendCallback(data){
    let params = new URLSearchParams(data);
    let postType = params.get('post_type');
    let postContent = params.get('post_content');
    if(postType == 'sc_product'){
        if(postContent == ''){
            alert('Please add product description');
            return false;
        }
    }
    return true;
}

function editPostSuccessCallback(res){
    clog(res);
    hideLoading();
    showStatus( res.success ? 'success' : 'error', res.data.message);
}

// Generate unique id 
function generateUid(){
    return Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
}