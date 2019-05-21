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

<p class="payment_module">
    <a href="{$link->getModuleLink('tinkoffcredit', 'validation')|escape:'htmlall':'UTF-8'}" title="{l s='Купить в кредит' mod='tinkoffcredit'}">
      <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/tinkoffcredit.png" alt="{l s='Купить в кредит' mod='tinkoffcredit'}"/>
      <span>{l s='Купить в кредит' mod='tinkoffcredit'}</span>
    </a>
</p>
