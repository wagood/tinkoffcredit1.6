<?php
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

if (!defined('_PS_VERSION_')) {
    exit;
}

class TinkoffcreditValidationModuleFrontController extends ModuleFrontController
{
    /** @var BankWire $module */
    public $module;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $cart = $this->context->cart;
        if ($cart->id_customer == 0 ||
            $cart->id_address_delivery == 0 ||
            $cart->id_address_invoice == 0 ||
            !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'tinkoffcredit') {
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $currency = $this->context->currency;
        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);

        $this->module->validateOrder(
            $cart->id,
            Configuration::get('PS_OS_PAYMENT'),
            $total,
            $this->module->displayName,
            null,
            array(),
            (int) $currency->id,
            false,
            $cart->secure_key
        );

        Tools::redirect(
            'index.php?controller=order-confirmation&id_cart=' . $cart->id .
            '&id_module=' . $this->module->id .
            '&id_order=' . $this->module->currentOrder .
            '&key=' . $customer->secure_key
        );
    }
}
