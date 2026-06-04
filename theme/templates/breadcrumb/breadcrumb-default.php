<?php
defined('ABSPATH') || exit;
use SmartCommerce\SmartCommerce;
if(is_home() || is_front_page()) return;
?>
<style>
    div#breadcrumb{
        display: flex;
        flex: 1;
        justify-content: flex-start;
        align-items: center;
        padding: 5px 10px;
        gap: 5px;
        background-color: var(--sc-primary-bg-color);
        font-size: 12px;
    }
    div#breadcrumb a{
        text-decoration: none;
        color: var(--sc-primary-color);
        line-height: 1em;
    }
    div#breadcrumb a i{
        color: #ccc;
    }
    div#breadcrumb span{
        color: #777;
    }
</style>
<?php
    $breadcrumb = ThemeBreadcrumb::getItems();
    echo implode( "<span class='breadcrumb-separator'>" . __(' > ', 'smartcommerce') . "</span>", $breadcrumb);
?>