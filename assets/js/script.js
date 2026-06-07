if(typeof $j == 'undefined') $j = jQuery;

if(typeof preventDefault == 'undefined'){
    window.preventDefault = function(e){
        e.preventDefault();
        e.stopPropagation();
    }
}

if(typeof clog == 'undefined'){
    window.clog = function(x){
        console.log(x);
    }
}

if(typeof showLoading == 'undefined'){
    window.showLoading = function(){
        $j('body').append("<div class='smartcommerce-loading' id='smartcommerce-loading'><div class='lds-ripple'> <div></div> <div></div> </div></div>");
    }
}

if(typeof hideLoading == 'undefined'){
    function hideLoading(){
        $j('.smartcommerce-loading').remove();
    }
}

if(typeof showStatus == 'undefined'){
    window.showStatus = function(type='success', message='', timeout=1000, reload = false){
        let icon = '';
        if(type == 'success') icon = '<i class="fa fa-check"></i>';
        else if(type == 'error') icon = '<i class="fa fa-times"></i>';
        else if(type == 'warning') icon = '<i class="fa fa-exclamation-triangle"></i>';
        else if(type == 'info') icon = '<i class="fa fa-info-circle"></i>';
        else if(type == 'question') icon = '<i class="fa fa-question-circle"></i>';
        else icon = '<i class="fa fa-info-circle"></i>';
        $j('.smartcommerce-status-message').remove();        
        $j('body').append("<div class='smartcommerce-status-message " + type + "'><div class='icon'>" + icon + "</div><div class='content'>" + message + "</div></div>");
        setTimeout(function(){
            $j('.smartcommerce-status-message').fadeOut(1000, function(){
                $j(this).remove();
            });
        }, timeout);
        if(reload){
            setTimeout(function(){
                window.location.reload();
            }, timeout + 100);
        }
    }
}


if(typeof preventDefault == 'undefined') {
    window.preventDefault = function(e){
        e.preventDefault();
        e.stopPropagation();
    }
}

$j(document).ready(function($){

    $j.get(smartcommerce.ajax_url, { action: 'get_nonce' }, function(response){
        smartcommerce._wpnonce = response;
    });

    // facebook pixel integration 
    $j(document).on('click', '[data-ajax_action="showOrderFormOnClick"]', function(e){
        let id = $j(this).data('id');
        let price = $j(this).data('price');
        if(typeof fbmp !== 'undefined') {
            fbmp.custom_data.contents = [{id: id, quantity: 1}];
            fbmp.custom_data.content_type = 'product';
            fbmp.custom_data.value = price;
            fbmp.custom_data.currency = 'BDT';
            if(typeof sendMetaEvent !== 'undefined'){
                sendMetaEvent('AddToCart', fbmp.custom_data); 
            }
        }
    });

    // Ajax link 
    $j(document).on('click', '.sc-ajax-link', function(e){
        // preventDefault(e);
        var link = $j(this);
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
                clog(data);
                if( beforeSendCallback != 'undefined' && beforeSendCallback != null && typeof window[beforeSendCallback] == 'function'){
                    if(beforeSendCallback == 'scConfirm'){
                        if(scConfirm() == false) return false;
                    }else{
                        if(window[beforeSendCallback]() == false) return false;
                    }
                }
                showLoading();
            },
            success: function(r){
                clog(r);
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
    $j(document).on('submit', '.sc-ajax-form', function(e){
        preventDefault(e);
        var form = $j(this);
        var data = form.serialize();
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
                console.log(data);
                if( beforeSendCallback != 'undefined' && beforeSendCallback != null && typeof window[beforeSendCallback] == 'function'){
                    if(beforeSendCallback == 'scConfirm'){
                        if(scConfirm() == false) return false;
                    }else{
                        if(window[beforeSendCallback](data) == false) return false;
                    }
                    showLoading();
                } else {
                    showLoading();
                }
            },
            success: function(r){
                console.log(r);
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

    // updating selling price on change of regular price or discount
    $j(document).on('change keyup keydown', "[name='regular_price'],[name='discount']", updateSellingPrice);

    // Add row when btn clicked 
    $j(document).on('click', '#add_product_variation_btn', function(){
        addProductVarRow();
    });

    // duplicate row 
    $j(document).on('click', '.copy-product-var', copyProductVar);

    // delete row 
    $j(document).on('click', '.delete-product-var', deleteProductVar);

    // Confirm Before Logut 
    $j(document).on('click', '.sc-welcome-message-logout', function(e){
        if(!confirm("Are you sure to logout?")) return false;
        return true;
    });

    // Reload window when menu bar updated 
    $j(document).on('change', '#admin_main_nav', function(){
        let newUrl = $j(this).val();
        window.location.href = newUrl;
    });

    // bulk select 
    $j(document).on('change', '.sc-select-all', function(){
        let target = $j(this).data('target');
        let checked = $j(this).prop('checked');
        $j(`input[name='${target}']`).prop('checked', checked);
    });

    // Confirm Logout
    $j(document).on('click', '.confirmLogout', function(e){
        preventDefault(e);
        let confirm = scConfirm("Are you sure to logout?");
        let url = $j(this).attr('href');
        if(confirm && url != ''){
            window.location.href = url;
        }
    });

    // Reload page on change status filter 
    $j(document).on('change', 'select[name="filter_post_status"]', function(e){
        reloadPageOnChange(e);
    });

    // Bulk Print Action
    $j(document).on('change', '.bulk-action', function(e){
        let ajax_action = $j(this).data('ajax_action');
        let action_value = $j(e.target).find(':selected').val();
        let postType = $j(this).data('post_type');
        let beforeSendCallback = $j(this).data('before_send_callback');
        let successCallback = $j(this).data('success_callback');
        let errorCallback = $j(this).data('error_callback');
        let completeCallback = $j(this).data('complete_callback');
        let ids = $j(`:input[name='id[]']:checked`).map(function(){
            return parseInt($(this).val());
        }).get();
        if(ids.length == 0){
            showStatus('error', 'Please select at least one item');
            return false;
        }
        if(action_value == ''){
            showStatus('error', 'Please select an action');
            return false;
        }
        if(!confirm('Are you sure to proceed?')) return false;
        
        let data = {
            action: 'smartcommerce_ajax',
            ajax_action: ajax_action,
            action_value: action_value,
            post_type: postType,
            _wpnonce: smartcommerce._wpnonce,
            id: ids,
        }
        $j.ajax({
            url: smartcommerce.ajax_url,
            type: 'POST',
            data: data,
            beforeSend: function(){
                clog(data);
                if(typeof beforeSendCallback !== 'undefined' && typeof window[beforeSendCallback] === 'function'){
                    return window[beforeSendCallback](data) == false ? false : true;
                } else {
                    showLoading();
                }
            },
            success: function(r){
                clog(r);
                hideLoading();
                if(typeof successCallback !== 'undefined' && typeof window[successCallback] === 'function'){
                    window[successCallback](r);
                } else {
                    showStatus( r.data.status ? 'success' : 'error', r.data.message);
                }
            },
            error: function(r){
                clog(r);
            },
            complete: function(){
                hideLoading();
            }
        })
    });

    // Update inline meta key 
    $j(document).on('change', '.updateInlineMetaValue', function(e){
        let data = $j(this).data();
        data.action = 'smartcommerce_ajax';
        data.ajax_action = 'updateInlineMetaValue';
        data._wpnonce = smartcommerce._wpnonce;
        data.meta_value = $j(e.target).val();

        $j.ajax({
            url: smartcommerce.ajax_url,
            type: 'POST',
            data: data,
            beforeSend: function(){
                clog(data);
            },
            success: function(r){
                clog(r);
                if(r.data.payload.post_type == 'sc_order' && r.data.payload.meta_key == 'order_status'){
                    $j(`tr[data-id='${r.data.id}']`).attr('data-order-status', r.data.payload.meta_value);
                } else {
                    $j(e.target).after('<i class="fa fa-check" style="font-size: 12px;"></i>');
                }
            },
            error: function(r){
                clog(r);
            },
            complete: function(){
            }
        });
    });

    smartcommerce.sms_count_number = 0;
    smartcommerce.sms_count_sms = 0;
    smartcommerce.sms_cost = 0;
    // SMS mobile numbers 
    $j(document).on('change keyup keydown', '#sms_mobile_numbers,#sms_message', function(e){
        let id = $j(e.target).attr('id');
        if(id == 'sms_mobile_numbers'){
            let mobileNumbers = $j(e.target).val();
            let mobileNumbersArray = mobileNumbers.split('\n');
            let mobilesCleaned = [];
            mobileNumbersArray.forEach(function(mobile){
                mobile = mobile.trim();
                if(mobile != '' && mobile.length == 11 && mobile[0] == '0' && mobile[1] == '1'){
                    mobilesCleaned.push(mobile);
                }
            });
            smartcommerce.sms_count_number = mobilesCleaned.length;
        } else if(id == 'sms_message'){
            let message = $j(e.target).val();
            smartcommerce.sms_count_sms = Math.ceil(message.length / 160);
        }
        updateBulkSmsData();
    });

    // youtube video player 
    $j(document).on('click', '.sc-youtube-thumbnail', function(e){
        preventDefault(e);
        let videoId = $j(this).data('video-id');
        // find window size 
        let windowWidth = window.innerWidth;
        let windowHeight = window.innerHeight;
        let aspectRatio = windowWidth / windowHeight;
        let width = windowWidth;
        let height = windowWidth / aspectRatio;
        if(height > windowHeight){
            height = windowHeight;
            width = windowHeight * aspectRatio;
        }
        let embedCode = `<iframe class="sc-youtube-player-popup" width="${width}" height="${height}" autoplay="1" src="https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0&showinfo=0&controls=0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>`;
        showScPopup(embedCode);
    });

    function updateBulkSmsData(){
        console.log(`sms_count_number ${smartcommerce.sms_count_number} sms_count_sms ${smartcommerce.sms_count_sms} sms_rate ${smartcommerce.sms_rate}`);
        $j('.total-numbers').text(smartcommerce.sms_count_number);
        $j('.total-sms').text(smartcommerce.sms_count_sms);
        smartcommerce.sms_cost = smartcommerce.sms_count_number * smartcommerce.sms_count_sms * smartcommerce.sms_rate;
        $j('.total-cost').text(parseFloat(smartcommerce.sms_cost).toFixed(2));
    }
});

// confirm before proceed  
window.scConfirm = function(message='Are you sure you want to proceed?'){
    return confirm(message);
}

// duplicate post success callback  
window.duplicatePostSuccessCallback = function(res){
    hideLoading();
    showStatus( res.data.status ? 'success' : 'error', res.data.message);
    if(res.data.status){
        setTimeout(function(){
            window.location.href = smartcommerce.site_url + '/dashboard/?submenu=edit&id=' + res.data.post_id + '&type=' + res.data.post_type;
        }, 1000);
    }
}

window.deletePostSuccessCallback = function(res){
    clog(res);
    hideLoading();
    showStatus( res.success ? 'success' : 'error', res.data.message);
    if(res.success){
        $j(`tr[data-id='${res.data.id}']`).slideUp(300, function(){
            $j(this).remove();
        });
    }
}

window.publishPostBeforeSendCallback = function(data){
    return true;
}

window.publishPostSuccessCallback = function(res){
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
window.editPostBeforeSendCallback = function(data){
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
 
window.editPostSuccessCallback = function(res){
    clog(res);
    hideLoading();
    showStatus( res.success ? 'success' : 'error', res.data.message);
}

// Generate unique id 
window.generateUid = function(){
    return Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
}

// login before send callback 
window.loginBeforeSendCallback = function(data){
    let params = new URLSearchParams(data);
    let username = params.get('username');
    let password = params.get('password');
    if(username == '' || password == ''){
        showStatus('error', 'Please enter mobile and password');
        return false;
    }
    if(username.length != 11 || username[0] != '0' || username[1] != '1'){
        showStatus('error', 'Please enter valid mobile number');
        return false;
    }
    return true;
}

window.loginSuccessCallback = function(r){
    clog(r);
    hideLoading();
    showStatus( r.data.status ? 'success' : 'error', r.data.message);
    if(r.data.status){ 
        setTimeout(function(){
            window.location.assign(r.data.redirect_to);
        }, 500);
    }
}

// Copy Product Var Row 
window.copyProductVar = function(e){
    e.preventDefault();
    e.stopPropagation();
    let uid = $j(this).closest('li').data('uid');
    let html = $j(`li[data-uid='${uid}']`).clone();
    let newUid = generateUid();
    // replace uid with new uid for input and hidden input
    html.find('input[name="variation_uid[]"]').val(newUid); 
    // replace li uid with new id 
    html.attr('data-uid', newUid);
    $j('.sc-product-variation-list').append(html);
}

window.updateSellingPrice = function(){
    var reg = parseFloat($j("[name='regular_price']").val());
    let dis = parseFloat($j("[name='discount']").val());
    let price = reg - dis;
    if(price < 0) price = 0;
    $j("[name='selling_price']").val(price);
    $j("[name='display_price']").val(price);
}


window.deleteProductVar = function(e){
    e.preventDefault();
    e.stopPropagation();
    if(!confirm("Are you sure to delete this variation?")) return;
    let uid = $j(this).closest('li').data('uid');
    $j(`li[data-uid='${uid}']`).slideUp(300, function(){
        $j(this).remove();
    });
}


// Add product variation row 
window.addProductVarRow = function(){
    let currentHtml = $j('.sc-product-variation-list').html();
    if(currentHtml == ''){
        let currentHtml = `
        <li class='header-row'>
            <div class='var-item' data-item-name='variation_name'>Variation <br>Name</div>
            <div class='var-item' data-item-name='variation_images'>Images</div>
            <div class='var-item' data-item-name='variation_price'>Selling <br>Price</div>
            <div class='var-item' data-item-name='variation_sku'>SKU</div>
            <div class='var-item' data-item-name='variation_stock'>Stock <br>Quantity</div>
            <div class='var-item' data-item-name='variation_size_options'>Size <br>Options</div>
            <div class='var-item' data-item-name='variation_color_options'>Color <br>Options</div>
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
        <div class='var-item' data-item-name='variation_description'>
            <input type='text' name='variation_description[]' class='sc-form-control' placeholder='Price' required>
        </div>

        <div class='var-item' data-item-name='variation_price'>
            <input type='text' name='variation_price[]' class='sc-form-control' placeholder='Price' required>
        </div>
        <div class='var-item' data-item-name='variation_sku'>
            <input type='text' name='variation_sku[]' class='sc-form-control' placeholder='SKU'>
        </div>
        <div class='var-item' data-item-name='variation_stock'>
            <input type='text' min='0' name='variation_stock[]' class='sc-form-control' value='99999' placeholder='Stock Quantity'>
        </div>
        <div class='var-item' data-item-name='variation_size_options'>
            <input type='text' name='variation_size_options[]' class='sc-form-control' placeholder='Size Options'>
        </div>
        <div class='var-item' data-item-name='variation_color_options'>
            <input type='text' name='variation_color_options[]' class='sc-form-control' placeholder='Color Options'>
        </div>
        <div class='var-item' data-item-name='variation_actions' style='flex-direction: column;'>
            <a href='javascript:void(0)' class="sc-icon-wrap copy-product-var">
                <i class="fa fa-plus" style="font-size: 12px;"></i>
            </a>
            <a href='javascript:void(0)' class="sc-icon-wrap delete-product-var">
                <i class="fa fa-trash" style="font-size: 12px;"></i>
            </a>
        </div>
    </li>`;
    $j('.sc-product-variation-list').append(html);
    //focusing on new row name field 
    $j(`input[name='variation_name[]']`).focus();
}

// reload page after saving settings 
window.saveSettingsSuccessCallback = function(res){
    hideLoading();
    showStatus( res.data.status ? 'success' : 'error', res.data.message, 1000, true);
}
window.deleteTaxonomySuccessCallback = function(res){
    hideLoading();
    showStatus( res.data.status ? 'success' : 'error', res.data.message );
    if(res.data.status){
        $j(`tr[data-id='${res.data.payload}']`).slideUp(300, function(){
            $j(this).remove();
        });
    }
}

window.validateMobile = function(mobile){
    if(mobile == '' || mobile == 'undefined' || mobile == null){
        showStatus('error', 'Please enter mobile number', 1000);
        return false;
    }
    if(mobile.length != 11 || mobile[0] != '0' || mobile[1] != '1'){
        showStatus('error', 'Please enter valid mobile number', 1000);
        return false;
    }
    return true;
}

window.addUserBeforeSendCallback = function(data){
    let params = new URLSearchParams(data);
    let role = params.get('role');
    if(role == ''){
        showStatus('error', 'Please select a role', 1000);
        return false;
    }
    let mobile = params.get('mobile');
    if(!validateMobile(mobile)){
        return false;
    }
    
    return true;
}
window.addUserSuccessCallback = function(res){
    hideLoading();
    showStatus( res.data.status ? 'success' : 'error', res.data.message);
    if(res.data.status && res.data.payload.ajax_action == 'publishPost'){
        setTimeout(function(){
            window.location.href = smartcommerce.site_url + '/dashboard/?submenu=sc_customer';
        }, 1000);
    }
}

///// Theme JS //////
$j(document).ready(function(){
    // order quantity increase or decrease 
    $j(document).on('click', '.sc-item-qty-btn', function(e){
        validateOrderQuantity(e);
        calculateOrderPrice();
    });

    $j(document).on('change', '.sc-item-qty', function(e){
        validateOrderQuantity(e);
        calculateOrderPrice();
    });

    // allow digits only in a field
    $j(document).on("input", ".digit-only", function () {
        this.value = this.value.replace(/[^0-9-]/g, "");
    });

    // Update Delivery Chrange
    $j(document).on('change', `:input[name='delivery_charge']`, function(e){
        let deliveryCharge = $j(e.target).val();
        // split by : 
        let deliveryChargeParts = deliveryCharge.split(':');
        let deliveryChargeAmount = deliveryChargeParts[1];
        console.log(deliveryChargeAmount);
        $j('.sc-order-delivery-charge').text(deliveryChargeAmount);
        calculateOrderPrice();
    });

    // click sc order now button
    $j(document).on('click', '.sc-order-now', function(e){
        preventDefault(e);
        $j('#sc_order_delivery').toggle(300, function(){
            $j('#delivery_name').focus();
        });
    });

    // hide popup
    $j(document).on('click', '.sc-popup-close', function(e){
        preventDefault(e);
        hideScPopup();
    });
})

function validateOrderQuantity(e){
    let action = $j(e.target).data('action');
    let qty = parseFloat($j(e.target).closest('tr').find('.sc-item-qty').val());
    let minQty = parseFloat($j(e.target).closest('tr').find('.sc-item-qty').attr('min'));
    let maxQty = parseFloat($j(e.target).closest('tr').find('.sc-item-qty').attr('max'));
    if(isNaN(qty)) qty = minQty;
    let newQty = action == 'add' ? qty + 1 : qty - 1;
    if(newQty < minQty) newQty = minQty;
    if(newQty > maxQty) newQty = maxQty;
    $j(e.target).closest('tr').find('.sc-item-qty').val(newQty);
}

function calculateOrderPrice(){
    let deliveryCharge = parseFloat($j('.sc-order-delivery-charge').text());
    let total = 0;
    let grandTotal = 0;
    let discount = parseFloat($j('.sc-order-discount').text());
    let net = 0;
    let selectedRows = $j('.sc-order-item-row');
    selectedRows.each(function(){
        let itemTotal = 0;
        let price = parseFloat($j(this).find('.sc-item-price').data('price'));
        let qty = parseFloat($j(this).find('.sc-item-qty').val());
        itemTotal = price * qty;
        total += itemTotal;
        // updating this row price 
        $j(this).find('.sc-item-total').text(itemTotal.toFixed(0));
    });
    grandTotal = total + deliveryCharge;
    net = grandTotal - discount;
    $j('.sc-order-total').text(total.toFixed(0));
    $j('.sc-order-grand-total').text(grandTotal.toFixed(0));
    $j(`:input[name='order_total']`).val(total);
    $j('.sc-order-discount').text(discount.toFixed(0));
    $j(`:input[name='order_discount']`).val(discount);
    $j('.sc-order-net-total, .sc-order-payable-amount').text(net.toFixed(0));
}

window.showScPopup = function(text='',header=''){
    let html = `
    <div class="sc-popup" style='display:none'>
        <div class="sc-popup-content-wrap">
            <div class="sc-popup-header">
                <div class='sc-popup-header-title'>${header}</div>
                <a href='javascript:void(0)' class="sc-popup-close">
                    <i class="fa fa-times"></i>
                </a>
            </div>
            <div class="sc-popup-content">
                ${text}
            </div>
        </div>
    </div>
    `;

    $j('body').append(html);
    $j('.sc-popup').fadeIn(300);
}

window.hideScPopup = function(){
    $j('.sc-popup').fadeOut(300, function(){
        $j(this).remove();
    });
}

// validating order 
window.orderBeforePublishCallback = function(data){
    let isError = false;
    let params = new URLSearchParams(data);
    let totalQty = 0;
    let mobile = params.get('delivery_mobile');

    if(typeof fbmp !== 'undefined'){
        fbmp.custom_data.contents = [];
        fbmp.custom_data.num_items = 0;
        fbmp.custom_data.value = params.get('order_total');
        fbmp.custom_data.currency = 'BDT';
        fbmp.custom_data.content_type = 'product';
        fbmp.user_data.fn = params.get('delivery_name');
        fbmp.user_data.ph = "+88" + params.get('delivery_mobile');    
    }

    $j(`.sc-order-item-row`).each(function(){
        let productId = $j(this).data('product-id');
        let qty = parseFloat($j(this).find('.sc-item-qty').val());
        if(qty > 0){
            let fbProduct = {
                id: productId,
                quantity: qty
            };
            if(typeof fbmp !== 'undefined'){
                fbmp.custom_data.contents.push(fbProduct);
                fbmp.custom_data.num_items += qty;    
            }
        }
        totalQty += qty;
        let color = $j(this).find('.sc-item-color').val();
        let size = $j(this).find('.sc-item-size').val();
        if(qty>0 && color != 'undefined' ){
            if(color == ''){
                showStatus('error', 'Please select color for this product');
                isError = true;
            }
        }
        if(qty>0 && size != 'undefined'){
            if(size == ''){
                showStatus('error', 'Please select size for this product');
                isError = true;
            }
        }
    });
    if(totalQty == 0){
        showStatus('error', 'Please select at least one variation');
        isError = true;
    }
    // if(!validateMobile(mobile)){
    //     return false;
    // }
    if(!isError){
        $j(`:input[type='submit']`).prop('disabled', true);
        showLoading();
        if(typeof sendMetaEvent !== 'undefined' && typeof fbmp !== 'undefined'){
            sendMetaEvent('Purchase', fbmp.custom_data);
        }
    }
    return !isError;
}

window.orderSuccessCallback = function(res){
    hideLoading();
    showStatus( res.data.status ? 'success' : 'error', res.data.message );
    if(res.data.status){
        setTimeout(function(){
            window.location.href = res.data.tracking_id;
        }, 1000);
    }
}

window.reloadPageOnChange = function(e, target='data-url'){
    let url = $j(e.target).find(':selected').attr(target);
    if(url != ''){
        window.location.href = url;
    }
}

window.printInvoiceSuccessCallback = function(res){
    hideLoading();
    showStatus( res.data.status ? 'success' : 'error', res.data.message );
    if(res.data.status){
        printContent(res.data.data);
    }
}

window.printContent = function(content=''){
    let pw = window.open('', '_blank');
    pw.document.write(content);
    pw.document.close();
    setTimeout(function(){
        pw.print();
    }, 100);
}

window.bulkPrintSuccessCallback = function(res){
    hideLoading();
    if(res.data.status){
        printContent(res.data.data);
    }
}

window.bulkChangeOrderStatusCallback = function(res){
    hideLoading();
    showStatus( res.data.status ? 'success' : 'error', res.data.message );
    if(res.data.status){
        // loop through ids 
        res.data.ids.forEach(function(id){
            $j(`tr[data-id='${id}']`).attr('data-order-status', res.data.payload.action_value);
        });
    }
}

window.viewOrderSuccessCallback = function(res){
    hideLoading();
    // showStatus( res.data.status ? 'success' : 'error', res.data.message );
    if(res.data.status){
        showScPopup(res.data.data, 'Order Details');
    }
}

window.showPopupOnCallback = function(res){
    hideLoading();
    if(res.data.status){
        showScPopup(res.data.data, '');
    } else {
        showStatus( res.data.status ? 'success' : 'error', res.data.message );
    }
}

window.popupData = function(res){
    hideLoading();
    hideScPopup();
    showScPopup(res.data.data, '');
}

window.insertDeliveryChocies = function(res){
    hideLoading();
    showStatus( res.data.status ? 'success' : 'error', res.data.message );
    if(res.data.status){
        $j(`:input[name='delivery_charges']`).val(res.data.data);
    }
}