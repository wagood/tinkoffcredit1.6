/**
 * Copyright (C) 2017-2019 WAGOOD
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
 */
$(document).ready(function() {
    $("#tinkoff_credit_button").click(function() {
        $.ajax({
            type: "POST",
            url: "/modules/tinkoffcredit/send.php",
            data: { token: token, id_product: id_product }
        }).done(function( ) {
            $("#tinkoff_credit_form").submit();
        });
    });
});