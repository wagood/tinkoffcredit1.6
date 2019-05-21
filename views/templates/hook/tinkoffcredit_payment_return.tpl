{**
* Copyright (C) 2017-2018 WAGOOD
*
* WAGOOD is an extension to the PrestaShop software by PrestaShop SA.
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.md.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@thirtybees.com so we can send you a copy immediately.
*
* @author    WAGOOD <WAGOOD@YANDEX.RU>
* @copyright 2017-2018 WAGOOD
* @license   Academic Free License (AFL 3.0)
* PrestaShop is an internationally registered trademark of PrestaShop SA.
*}
<!-- Tinkoffcreadit module -->
<div>
    <p class="class="buttons_bottom_block no-print">
    <form action="{$TINKOFF_CREDIT_URL|escape:'htmlall':'UTF-8'}" method="post" id="tinkoff_credit_form">
        <input name="shopId" value="{$TINKOFF_CREDIT_shopId|escape:'htmlall':'UTF-8'}" type="hidden"/>
        <input name="showcaseId" value="{$TINKOFF_CREDIT_showcaseId|escape:'htmlall':'UTF-8'}" type="hidden"/>
        <input name="promoCode" value="{$TINKOFF_CREDIT_promoCode|escape:'htmlall':'UTF-8'}" type="hidden"/>
        <input name="sum" value="{$TINKOFF_CREDIT_sum|string_format:"%.2f"|escape:'htmlall':'UTF-8'}" type="hidden">
        <input name="orderNumber" value="{$TINKOFF_CREDIT_orderNumber|escape:'htmlall':'UTF-8'}" type="hidden">
        <input name="customerNumber" value="{$TINKOFF_CREDIT_customerNumber|escape:'htmlall':'UTF-8'}" type="hidden">
        <input name="customerEmail" value="{$TINKOFF_CREDIT_customerEmail|escape:'htmlall':'UTF-8'}" type="hidden">
        <input name="customerPhone" value="{$TINKOFF_CREDIT_customerPhone|escape:'htmlall':'UTF-8'}" type="hidden">
        {foreach from=$products key=index item=product name=productLoop}
            <input name="itemVendorCode_{$index|escape:'htmlall':'UTF-8'}"
                   value="{$product.reference|escape:'html':'UTF-8'}"
                   type="hidden"/>
            <input name="itemName_{$index|escape:'htmlall':'UTF-8'}"
                   value="{$product.product_name|escape:'html':'UTF-8'}"
                   type="hidden"/>
            <input name="itemQuantity_{$index|escape:'htmlall':'UTF-8'}"
                   value="{$product.product_quantity|escape:'htmlall':'UTF-8'}"
                   type="hidden"/>
            <input name="itemPrice_{$index|escape:'htmlall':'UTF-8'}"
                   value="{$product.unit_price_tax_incl|string_format:"%.2f"|escape:'htmlall':'UTF-8'}"
                   type="hidden"/>
        {/foreach}
    </form>
    </p>
    <p class="payment_module">
        <button type="button" class="exclusive" id="tinkoff_credit_button">
            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/tinkoffcredit.png" alt="{l s='Купить в кредит' mod='tinkoffcredit'}"/>
            <span>{l s='Купить в кредит' mod='tinkoffcredit'}</span>
        </button >
    </p>
</div>
<!-- /Tinkoffcreadit module -->
