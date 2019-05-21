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

class Tinkoffcredit extends PaymentModule
{
    public $tc_testmode;
    public $tc_url;
    public $tc_shopId;
    public $tc_showcaseId;
    public $tc_promoCode;
    private $html = '';
    private $postErrors = array();

    public function __construct()
    {
        $this->name = 'tinkoffcredit';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.1';
        $this->author = 'WAGOOD';
        $this->need_instance = 0;
        $this->bootstrap = true;

        $this->setMode();
        parent::__construct();

        $this->displayName = 'Tinkoff Credit';
        $this->description = $this->l('Accept payments with Tinkoff Credit');
        $this->confirmUninstall = $this->l('Are you sure you want to delete module and your details ?');
    }

    public function install()
    {
        //URL https://loans-qa.tcsbank.ru/api/partners/v1/lightweight/create
        //shopId test_online
        //showcaseId test_online
        //promoCode default
        Configuration::updateValue('TINKOFF_CREDIT_TEST_MODE', '1');
        Configuration::updateValue('TINKOFF_CREDIT_URL', '');
        Configuration::updateValue('TINKOFF_CREDIT_SHOPID', '');
        Configuration::updateValue('TINKOFF_CREDIT_SHOWCASEID', '');
        Configuration::updateValue('TINKOFF_CREDIT_PROMOCODE', '');
        Configuration::updateValue('TINKOFF_CREDIT_EMAIL', '');

        $this->_clearCache('tinkoffcredit.tpl');

        return parent::install() &&
            $this->registerHook('displayProductButtons') &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('displayPayment') &&
            $this->registerHook('displayPaymentEU') &&
            $this->registerHook('header') &&
            $this->registerHook('displayRightColumnProduct') &&
            $this->registerHook('displaySocialSharing');
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('TINKOFF_CREDIT_TEST_MODE')
            || !Configuration::deleteByName('TINKOFF_CREDIT_URL')
            || !Configuration::deleteByName('TINKOFF_CREDIT_SHOPID')
            || !Configuration::deleteByName('TINKOFF_CREDIT_SHOWCASEID')
            || !Configuration::deleteByName('TINKOFF_CREDIT_PROMOCODE')
            || !Configuration::deleteByName('TINKOFF_CREDIT_EMAIL')
            || !parent::uninstall()) {
            return false;
        }
        return true;
    }

    public function hookDisplayRightColumnProduct($params)
    {
        return $this->hookDisplaySocialSharing($params);
    }

    public function hookDisplaySocialSharing($params)
    {
        if (Configuration::get('PS_CATALOG_MODE')) {
            return;
        }

        if (!$this->active) {
            return;
        }

        if (isset($this->context->controller->php_self) &&
            ($this->context->controller->php_self == 'product')
        ) {
            $this->smarty->assign(array(
                'TINKOFF_CREDIT_URL' => $this->tc_url,
                'TINKOFF_CREDIT_SHOPID' => $this->tc_shopId,
                'TINKOFF_CREDIT_SHOWCASEID' => $this->tc_showcaseId,
                'TINKOFF_CREDIT_PROMOCODE' => $this->tc_promoCode,
            ));

            return $this->display(__FILE__, 'tinkoffcredit_button_form.tpl');
        }
    }

    public function postProcessExternal()
    {
        if (Tools::getToken(false) != Tools::getValue('token')) {
            return;
        }

        $id_product = (int)Tools::getValue('id_product');
        $Product = new Product($id_product);

        $emails = explode(",", Configuration::get('TINKOFF_CREDIT_EMAIL'));
        $id_lang = Context::getContext()->language->id;
        $dir_mail = dirname(__FILE__) . '/mails/';

        if ($emails and sizeof($emails) > 0) {
            foreach ($emails as $mail) {
                Mail::Send(
                    $id_lang,
                    'tinkoff_alert',
                    $this->l('Интересовались товаром'),
                    [
                        '{id_product}' => Tools::getValue('id_product'),
                        '{product_name}' => $Product->name[$id_lang],
                    ],
                    $mail,
                    null,
                    Configuration::get('PS_SHOP_EMAIL'),
                    Configuration::get('PS_SHOP_NAME'),
                    null,
                    null,
                    $dir_mail,
                    null,
                    Context::getContext()->shop->id
                );
            }
        }
    }

    public function setMode()
    {
        // set test mode
        $config = Configuration::getMultiple(
            array(
                'TINKOFF_CREDIT_TEST_MODE',
                'TINKOFF_CREDIT_URL',
                'TINKOFF_CREDIT_SHOPID',
                'TINKOFF_CREDIT_SHOWCASEID',
                'TINKOFF_CREDIT_PROMOCODE'
            )
        );

        if (isset($config['TINKOFF_CREDIT_TEST_MODE'])) {
            $this->tc_testmode = $config['TINKOFF_CREDIT_TEST_MODE'];
        }

        if ($this->tc_testmode) {
            $this->tc_url = 'https://loans-qa.tcsbank.ru/api/partners/v1/lightweight/create';
            $this->tc_shopId = $this->tc_showcaseId = 'test_online';
            $this->tc_promoCode = 'default';
        } else {
            $this->tc_url = $config['TINKOFF_CREDIT_URL'];
            $this->tc_shopId = $config['TINKOFF_CREDIT_SHOPID'];
            $this->tc_showcaseId = $config['TINKOFF_CREDIT_SHOWCASEID'];
            $this->tc_promoCode = $config['TINKOFF_CREDIT_PROMOCODE'];
        }
    }

    private function postValidation()
    {
        if (Tools::isSubmit('btnSubmit') &&
            Tools::getValue('TINKOFF_CREDIT_TEST_MODE') == 0) {
            if (empty(Tools::getValue('TINKOFF_CREDIT_URL'))) {
                $this->postErrors[] = $this->l('Url is required');
            }
            if (!Validate::isUrl(Tools::getValue('TINKOFF_CREDIT_URL'))) {
                $this->postErrors[] = $this->l('Wrong Url format');
            }
            if (empty(Tools::getValue('TINKOFF_CREDIT_SHOPID'))) {
                $this->postErrors[] = $this->l('Shop ID is required');
            }
        }
    }

    private function postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('TINKOFF_CREDIT_TEST_MODE', Tools::getValue('TINKOFF_CREDIT_TEST_MODE'));
            Configuration::updateValue('TINKOFF_CREDIT_URL', Tools::getValue('TINKOFF_CREDIT_URL'));
            Configuration::updateValue('TINKOFF_CREDIT_SHOPID', Tools::getValue('TINKOFF_CREDIT_SHOPID'));
            Configuration::updateValue('TINKOFF_CREDIT_SHOWCASEID', Tools::getValue('TINKOFF_CREDIT_SHOWCASEID'));
            Configuration::updateValue('TINKOFF_CREDIT_PROMOCODE', Tools::getValue('TINKOFF_CREDIT_PROMOCODE'));
            Configuration::updateValue('TINKOFF_CREDIT_EMAIL', Tools::getValue('TINKOFF_CREDIT_EMAIL'));
            $this->setMode();
        }
        $this->html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    private function displayRb()
    {
        $this->html .= '<b>' .
            $this->l('This module allows you to accept payments by Tinkoff Credit.') .
            '</b><br /><br />';
        $this->html .= '<script>
        function showTinkoffTestMode(test_mode) {
            console.log(test_mode);            
            var $tinkoffTestMode = $(".tinkoffPayment");
	        [].forEach.call($tinkoffTestMode, function (item) {
                test_mode ? item.style.display = "none" : item.style.display = "inline" ;
            });
	     }
        $(document).ready(function() {
            var test_mode = $("#TINKOFF_CREDIT_TEST_MODE_on").prop("checked");            
            showTinkoffTestMode(test_mode);				
			$("#TINKOFF_CREDIT_TEST_MODE_on").change(function() {
			    var test_mode = $("#TINKOFF_CREDIT_TEST_MODE_on").prop("checked");
			    showTinkoffTestMode(test_mode);			    
			});
			$("#TINKOFF_CREDIT_TEST_MODE_off").change(function() {
			    var test_mode = $("#TINKOFF_CREDIT_TEST_MODE_off").prop("checked");
			    showTinkoffTestMode(!test_mode);			    
			});
	    });

</script>
<style>.hidden {display: none;}</style>';
    }


    public function getContent()
    {
        $this->html = '<h2>' . $this->displayName . '</h2>';

        if (Tools::isSubmit('btnSubmit')) {
            $this->postValidation();
            if (!count($this->postErrors)) {
                $this->postProcess();
            } else {
                foreach ($this->postErrors as $err) {
                    $this->html .= $this->displayError($err);
                }
            }
        } else {
            $this->html .= '<br />';
        }

        $this->html .= $this->displayRb();
        $this->html .= $this->renderForm();

        return $this->html;
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Shop details'),
                    'icon' => 'icon-envelope'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Test Mode'),
                        'name' => 'TINKOFF_CREDIT_TEST_MODE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'TINKOFF_CREDIT_TEST_MODE_on',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id' => 'TINKOFF_CREDIT_TEST_MODE_off',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Email address(es)'),
                        'name' => 'TINKOFF_CREDIT_EMAIL',
                        'desc' => $this->l('Emails separated by commas'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Form Url'),
                        'name' => 'TINKOFF_CREDIT_URL',
                        'desc' => $this->l('Form Url for bank requests'),
                        'required' => true,
                        'form_group_class' => "tinkoffPayment",
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Shop ID'),
                        'name' => 'TINKOFF_CREDIT_SHOPID',
                        'required' => true,
                        'form_group_class' => "tinkoffPayment",
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Showcase ID'),
                        'name' => 'TINKOFF_CREDIT_SHOWCASEID',
                        'required' => false,
                        'form_group_class' => "tinkoffPayment",
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Promo Code'),
                        'name' => 'TINKOFF_CREDIT_PROMOCODE',
                        'required' => false,
                        'form_group_class' => "tinkoffPayment",
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'TINKOFF_CREDIT_TEST_MODE' => Tools::getValue(
                'TINKOFF_CREDIT_TEST_MODE',
                Configuration::get('TINKOFF_CREDIT_TEST_MODE')
            ),
            'TINKOFF_CREDIT_URL' => Tools::getValue(
                'TINKOFF_CREDIT_URL',
                Configuration::get('TINKOFF_CREDIT_URL')
            ),
            'TINKOFF_CREDIT_SHOPID' => Tools::getValue(
                'TINKOFF_CREDIT_SHOPID',
                Configuration::get('TINKOFF_CREDIT_SHOPID')
            ),
            'TINKOFF_CREDIT_SHOWCASEID' => Tools::getValue(
                'TINKOFF_CREDIT_SHOWCASEID',
                Configuration::get('TINKOFF_CREDIT_SHOWCASEID')
            ),
            'TINKOFF_CREDIT_PROMOCODE' => Tools::getValue(
                'TINKOFF_CREDIT_PROMOCODE',
                Configuration::get('TINKOFF_CREDIT_PROMOCODE')
            ),
            'TINKOFF_CREDIT_EMAIL' => Tools::getValue(
                'TINKOFF_CREDIT_EMAIL',
                Configuration::get('TINKOFF_CREDIT_EMAIL')
            ),
        );
    }

    public function hookDisplayPaymentEU($params)
    {
        return $this->hookPayment($params);
    }

    /**
     * @return array|string
     * @throws PrestaShopException
     */
    public function hookPayment($params)
    {
        if (!$this->active) {
            return;
        }

        $this->smarty->assign(
            array(
                'this_path_bw' =>
                    $this->_path,
                'this_path_ssl' =>
                    Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
            )
        );

        return $this->display(__FILE__, 'tinkoffcredit_payment.tpl');
    }

    public function hookDisplayProductButtons($params)
    {
        if (Configuration::get('PS_CATALOG_MODE')) {
            return;
        }

        if (!$this->active) {
            return;
        }

        return $this->display(__FILE__, 'tinkoffcredit_button.tpl');
    }

    public function hookHeader($params)
    {
        if (Configuration::get('PS_CATALOG_MODE')) {
            return;
        }

        if (!$this->active) {
            return;
        }

        if (isset($this->context->controller->php_self) &&
            $this->context->controller->php_self == 'order-confirmation') {
            $this->context->controller->addJs($this->_path . 'views/js/tinkoffcredit_payment.js');
        }

        if (isset($this->context->controller->php_self) &&
            $this->context->controller->php_self == 'product') {
            $this->context->controller->addJs($this->_path . 'views/js/tinkoffcredit_button.js');
        }
    }

    public function getL($key)
    {
        $translations = array(
            'success' => 'Tinkoff transaction is carried out successfully.',
            'fail' => 'Tinkoff transaction is refused.'
        );
        return $translations[$key];
    }

    public function hookPaymentReturn($params)
    {
        if (!isset($params) ||
            !isset($params['objOrder']) ||
            !$params['objOrder'] instanceof Order ||
            !$this->active) {
            return '';
        }

        try {
            $state = $params['objOrder']->getCurrentState();
            if (in_array($state, array(
                    Configuration::get('PS_OS_PAYMENT'),
                    Configuration::get('PS_OS_OUTOFSTOCK'),
                    Configuration::get('PS_OS_OUTOFSTOCK_UNPAID'))
            )) {
                $Order = $params['objOrder'];
                $products = $Order->getCartProducts();
                $Customer = new Customer($Order->id_customer);
                $delivery = new Address($Order->id_address_delivery);
                if (Product::getTaxCalculationMethod($Customer->id) == PS_TAX_EXC) {
                    $total_products = $Order->getTotalProductsWithoutTaxes();
                } else {
                    $total_products = $Order->getTotalProductsWithTaxes();
                }

                $this->smarty->assign(array(
                    'TINKOFF_CREDIT_URL' => $this->tc_url,
                    'TINKOFF_CREDIT_sum' => $total_products,
                    'TINKOFF_CREDIT_shopId' => $this->tc_shopId,
                    'TINKOFF_CREDIT_showcaseId' => $this->tc_showcaseId,
                    'TINKOFF_CREDIT_promoCode' => $this->tc_promoCode,
                    'TINKOFF_CREDIT_orderNumber' => $Order->reference,
                    'TINKOFF_CREDIT_customerNumber' => $Order->id_customer,
                    'TINKOFF_CREDIT_customerEmail' => $Customer->email,
                    'TINKOFF_CREDIT_customerPhone' => $delivery->phone ? $delivery->phone : $delivery->phone_mobile,
                    'status' => 'ok',
                    'id_order' => $params['objOrder']->id,
                    'Order' => $Order,
                    'products' => $products,
                ));
                if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference)) {
                    $this->smarty->assign('reference', $params['objOrder']->reference);
                }
            } else {
                $this->smarty->assign('status', 'failed');
            }
        } catch (PrestaShopException $e) {
            Logger::addLog("Tinkoffcredit module error: {$e->getMessage()}");

            return '';
        }
        return $this->display(__FILE__, 'tinkoffcredit_payment_return.tpl');
    }
}
