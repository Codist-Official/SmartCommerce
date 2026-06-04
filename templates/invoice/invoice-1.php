<?php
    use SmartCommerce\SmartCommerce;
    use SmartCommerce\Settings;
    use SmartCommerce\Product;

    $logo = Settings::get('shop_logo');
    $name = Settings::get('shop_name');
    $shop_address = Settings::get('shop_address');
    $shop_email = Settings::get('shop_email');
    $shop_phone = Settings::get('shop_phone');
    $shop_website = Settings::get('shop_website');
    $currency_symbol = Settings::get('currency_symbol');
?>
<style>
    .sc-invoice-wrap{
        width: 100%;
        height: 100%;
    }
    .sc-invoice-header{
        width: 100%;
        height: 120px;
        display: inline-block;
        overflow: hidden;
    }
    .sc-invoice-logo{
        float: left;
        height: 80px;
    }
    .sc-invoice-logo img{
        height: 50px;
        width: auto;
    }
    .sc-invoice-shipper-details,
    .sc-invoice-delivery-details{
        display: inline-block;
        float: left;
        margin-right: 10px;
        padding-right: 10px;
        font-size: 12px;
    }
    .sc-invoice-header-right{
        display: inline-block;
        float: right;
        width: 150px;
    }
    .sc-invoice-address-info{
        width: 100%;
        height: auto;
        display: inline-block;
        padding-top: 20px;
    }
    .sc-invoice-shipper-info,
    .sc-invoice-delivery-info{
        width: 50%;
    }
    .sc-invoice-shipper-info,
    .sc-invoice-delivery-info{
        height: auto;
        display: inline-block;
        float: left;
        font-size: 14px;
    }
    .sc-invoice-header-info{
        line-height: 1em;
    }
    .sc-invoice-header-info-item{
        border-bottom: 1px dashed #000;
        font-size: 11px;
        display: inline-block;
    }
    .sc-invoice-header-info-item .label-wrapper{
        width: 65px;
        display: inline-block;
        float: left;
    }
    .sc-invoice-header-info-item .value-wrapper{
        width: 75px;
        display: inline-block;
        float: left;
        padding-left: 10px;
        box-sizing: border-box;
    }
    .highlighted_text{
        font-size: 16px;
        text-transform: uppercase;
        text-decoration: underline;
        font-weight: bold;
    }

    .sc-invoice-condition-details{
        font-size: 20px;
        line-height: 1em;
        font-weight:bold;
        border: 2px solid red;
        padding: 5px;
        text-align: center;
        color: red;
        box-sizing: border-box;
    }
    table.product-table{
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
        
    }
    table.product-table thead tr th{
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
    }
    table.product-table tr th,
    table.product-table tr td{
        font-size: 12px;
        text-align: left;
        padding: 2px;
        line-height: 1.25em;
        vertical-align: middle;
    }
    table.product-table tr td{
        border-bottom: 1px solid #000;
        font-size: <?php echo Settings::get('print_invoice_font_size', '12'); ?>px;
    }
    table.product-table tr td img{
        max-height:50px;
        width: auto;
    }
    table.product-table tr td.td-no-border{
        border-bottom: none;
    }
</style>

<div class="sc-invoice-wrap">
    <div class='sc-invoice-header'>
        <!-- Header Left -->
        <div class='sc-invoice-header-left'>
            <!-- Logo -->
            <div class='sc-invoice-logo' '>
                <?php if($logo){ ?>
                    <?php echo wp_get_attachment_image($logo, 'full'); ?>
                <?php } ?>
            </div>

        </div>
        <!-- Header Right -->
        <div class='sc-invoice-header-right'>
            <?php echo SmartCommerce::generateBarCode($order_id, 25); ?>
            <div class='sc-invoice-header-info'>
                <div class='sc-invoice-header-info-item'>
                    <span class='label-wrapper'><?php _e('Invoice', 'smartcommerce'); ?></span>
                    <span class='value-wrapper'>#<?php echo SmartCommerce::showDigit($order_id); ?></span>
                </div>
                <div class='sc-invoice-header-info-item'>
                    <span class='label-wrapper'><?php _e('Order Date', 'smartcommerce'); ?></span>
                    <span class='value-wrapper'><?php echo SmartCommerce::showDigit($order_date); ?></span>
                </div>
                <?php 
                    if($order_delivery_partner_title){
                        ?>
                        <div class='sc-invoice-header-info-item'>
                            <span class='label-wrapper'><?php echo ucwords($order_delivery_partner_title); ?></span>
                            <span class='value-wrapper'>#<?php echo $consignment_id; ?></span>
                        </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class='sc-invoice-address-info'>
        
        <!-- Shipper Info -->
        <div class='sc-invoice-shipper-info'>

            <div class='sc-invoice-shipper-details'>
                <div class='highlighted_text'><?php _e('SELLER', 'smartcommerce'); ?></div>
                <div class='sc-invoice-details-name'><strong><?php echo $name; ?></strong></div>
                <div class='sc-invoice-details-address'><?php echo $shop_address; ?></div>
                <div class='sc-invoice-details-email'><?php echo $shop_email; ?></div>
                <div class='sc-invoice-details-phone'><?php echo SmartCommerce::showDigit($shop_phone); ?></div>
                <div class='sc-invoice-details-website'><?php echo $shop_website; ?></div>
            </div>

        </div>

        <!-- Delivery Info -->
         <div class='sc-invoice-delivery-info'>
            <div class="highlighted_text"><?php _e('DELIVERY', 'smartcommerce'); ?></div>
            <div class='sc-invoice-delivery-details'>
                <div class='sc-invoice-delivery-name'><strong><?php echo $reciever_name; ?></strong></div>
                <div class='sc-invoice-delivery-address'><?php echo $reciever_address; ?></div>
                <div class='sc-invoice-delivery-mobile'><?php echo SmartCommerce::showDigit($reciever_mobile); ?></div>
            </div>
         </div>
        
    </div>

    <!-- Condition Info -->
    <div class='sc-invoice-condition-info'>
        <div class='sc-invoice-condition-details'>
            <?php if($order_due > 0){ ?>
                <span class='sc-invoice-condition-due'>
                    <strong><?php _e('Condition', 'smartcommerce'); ?></strong>
                    <?php echo $currency_symbol . SmartCommerce::showDigit($order_due); ?>
                </span>
            <?php } ?>
        </div>
    </div>

    <!-- Product Details -->
    <div class='sc-invoice-body'>
        <div class='product-table-wrap'>
            <table class="product-table">
                <thead>
                    <tr>
                        <th><?php _e('Image', 'smartcommerce'); ?> </th>
                        <th><?php _e('Product', 'smartcommerce'); ?></th>
                        <th style='text-align: center;'><?php _e('Price', 'smartcommerce'); ?></th>
                        <th style='text-align: center;'><?php _e('Qty', 'smartcommerce'); ?></th>
                        <th><?php _e('Total', 'smartcommerce'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($product_details as $product_id=>$items){ ?>
                        <?php $product = new Product($product_id); ?>
                        <?php foreach($items as $item){ 
                            $qty = !empty($item['variation_id']) ? $item['variation_quantity'] : $item['product_quantity'];
                            if($qty == 0) continue;
                            $price = !empty($item['variation_id']) ? $item['variation_price'] : $item['product_price'];
                            $total = number_format($price * $qty, 2);
                            $thumb_id = !empty($item['variation_id']) ? $product->getThumbnail($item['variation_id']) : $product->getThumbnail();
                            $thumb_img = is_numeric($thumb_id) ? wp_get_attachment_image($thumb_id, [50,50]) : "<img src='{$thumb_id}' width='50'>";
                            ?>
                            <tr>
                                <td width='60'><?php echo $thumb_img; ?></td>
                                <td>(#<?php echo $product_id; ?>) <?php echo $product->getTitle(); if(!empty($item['variation_id'])) echo "<br>" . $product->getTitle($item['variation_id']); ?></td>
                                <td style='text-align: center;'><?php echo $currency_symbol . SmartCommerce::showDigit($price); ?></td>
                                <td style='text-align: center;'><?php echo SmartCommerce::showDigit($qty); ?></td>
                                <td><?php echo $currency_symbol . SmartCommerce::showDigit($total); ?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td class='td-no-border' colspan="3"> &nbsp; </td>
                            <td style="text-align: right;"><?php _e('Total', 'smartcommerce'); ?></td>
                            <td><?php echo $currency_symbol . SmartCommerce::showDigit($order_total); ?></td>
                        </tr>
                        <tr>
                            <td class='td-no-border' colspan="3"> &nbsp; </td>
                            <td style="text-align: right;"><?php _e('Discount', 'smartcommerce'); ?></td>
                            <td><?php echo $currency_symbol . SmartCommerce::showDigit($order_discount); ?></td>
                        </tr>
                        <tr>
                            <td class='td-no-border' colspan="3"> &nbsp; </td>
                            <td style="text-align: right;"><?php _e('Delivery', 'smartcommerce'); ?></td>
                            <td><?php echo $currency_symbol . SmartCommerce::showDigit($order_delivery); ?></td>
                        </tr>
                        <tr>
                            <td class='td-no-border' colspan="3"> &nbsp; </td>
                            <td style="text-align: right;"><?php _e('Payable', 'smartcommerce'); ?></td>
                            <td><?php echo $currency_symbol . SmartCommerce::showDigit($order_payable); ?></td>
                        </tr>
                        <tr>
                            <td class='td-no-border' colspan="3"> &nbsp; </td>
                            <td style="text-align: right;"><?php _e('Paid', 'smartcommerce'); ?></td>
                            <td><?php echo $currency_symbol . SmartCommerce::showDigit($order_paid); ?></td>
                        </tr>
                        <tr>
                            <td class='td-no-border' colspan="3"> &nbsp; </td>
                            <td style="text-align: right;"><?php _e('Due', 'smartcommerce'); ?></td>
                            <td><?php echo $currency_symbol . SmartCommerce::showDigit($order_due); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $footer_text = ""; ?>
<div class='sc-invoice-footer'>
    <div class='sc-invoice-footer-left'>
        <div class='sc-invoice-footer-left-text'>
            <?php echo $footer_text; ?>
        </div>
    </div>
</div>
<!-- Add Page Break -->
<div style="page-break-after: always;"></div>