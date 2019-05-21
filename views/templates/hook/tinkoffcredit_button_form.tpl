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
{if !isset($priceDisplayPrecision)}
    {assign var='priceDisplayPrecision' value=2}
{/if}
{if !$priceDisplay || $priceDisplay == 2}
    {assign var='productPrice' value=$product->getPrice(true, $smarty.const.NULL, 2)}
    {assign var='productPriceWithoutReduction' value=$product->getPriceWithoutReduct(false, $smarty.const.NULL)}
{elseif $priceDisplay == 1}
    {assign var='productPrice' value=$product->getPrice(false, $smarty.const.NULL, 2)}
    {assign var='productPriceWithoutReduction' value=$product->getPriceWithoutReduct(true, $smarty.const.NULL)}
{/if}
<div>
    <p class="buttons_bottom_block no-print">
    <form action="{$TINKOFF_CREDIT_URL|escape:'htmlall':'UTF-8'}" method="post" id="tinkoff_credit_form">
        <input name="shopId" value="{$TINKOFF_CREDIT_SHOPID|escape:'htmlall':'UTF-8'}" type="hidden"/>
        <input name="showcaseId" value="{$TINKOFF_CREDIT_SHOWCASEID|escape:'htmlall':'UTF-8'}" type="hidden"/>
        <input name="promoCode" value="{$TINKOFF_CREDIT_PROMOCODE|escape:'htmlall':'UTF-8'}" type="hidden"/>
        <input name="sum" value="{$productPrice|escape:'htmlall':'UTF-8'}" type="hidden">
        <input name="itemVendorCode_0" value="{$product->reference|escape:'htmlall':'UTF-8'}" type="hidden"/>
        <input name="itemName_0" value="{$product->name|escape:'html':'UTF-8'}" type="hidden"/>
        <input name="itemQuantity_0" value="1" type="hidden"/>
        <input name="itemPrice_0" value="{$productPrice|escape:'htmlall':'UTF-8'}" type="hidden"/>
    </form>
    </p>
</div>
<!-- /Tinkoffcreadit module -->
