<?php
/**
 * Copyright (c) 2019 Nexi Payments S.p.A.
 *
 * @author      iPlusService S.r.l.
 * @category    Payment Module
 * @package     Nexi XPay
 * @version     4.0.0
 * @copyright   Copyright (c) 2019 Nexi Payments S.p.A. (https://ecommerce.nexi.it)
 * @license     GNU General Public License v3.0
 */

class WC_Gateway_XPay_Easy extends WC_Gateway_XPay
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->supports = array(
            'products',
            'refunds',
            'subscriptions', 'subscription_cancellation', 'subscription_suspension', 'subscription_reactivation', 'subscription_amount_changes', 'subscription_date_changes', 'subscription_payment_method_change'
        );

        $this->set_api();

        // Actions
        add_action('woocommerce_api_wc_gateway_' . $this->id, array($this, 'wc_xpay_page_ritorno'));
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'wc_xpay_page_ringrazia'));
        add_action('woocommerce_receipt_' . $this->id, array($this, 'wc_xpay_page_invioform'));
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'get_available_methods' ));
    }

    /**
     * Setta i campi per il form di configurazione,
     * se attivato recurring viene sovrascritte dalla classe figlia
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'title_section_1' => array(
                'title' => __("Payment module configuration", 'woocommerce-gateway-nexi-xpay'),
                'type' => 'title',
            //'description' => ""),
            ),
            'enabled' => array(
                'title' => __('Enable/Disable', 'woocommerce-gateway-nexi-xpay'),
                'type' => 'checkbox',
                'label' => __("Enable Nexi XPay Payment Module.", 'woocommerce-gateway-nexi-xpay'),
                'default' => 'no'
            ),
            'cartasi_form_language' => array(
                'title' => __('Language form', 'woocommerce-gateway-nexi-xpay'),
                'type' => 'select',
                'options' => $this->get_options_config_language(),
                'desc_tip' => __('Select the language for Nexi form', 'woocommerce-gateway-nexi-xpay')
            ),
            'contabilizzazione' => array(
                'title' => __('Accounting', 'woocommerce-gateway-nexi-xpay'),
                'type' => 'select',
                'options' => $this->get_options_config_contab(),
                'desc_tip' => __('(PRODUCTION ONLY) The field identifies the collection method that the merchant wants to apply to the single transaction, if valued with:<br>- I (immediate) the transaction if authorized is also collected without further intervention by the operator and without considering the default profile set on the terminal.<br>- D (deferred) or the field is not inserted the transaction if authorized is managed according to what is defined by the terminal profile', 'woocommerce-gateway-nexi-xpay')
            ),
            'cartasi_modalita_test' => array(
                'title' => __('Enable/Disable TEST Mode', 'woocommerce-gateway-nexi-xpay'),
                'type' => 'checkbox',
                'label' => __('Enable Nexi Payment Module in testing mode.', 'woocommerce-gateway-nexi-xpay'),
                'default' => 'no',
                'description' => __('Register on', 'woocommerce-gateway-nexi-xpay') . ' <a href=\'https://ecommerce.nexi.it/area-test\'>' . __('ecommerce.nexi.it/area-test', 'woocommerce-gateway-nexi-xpay') . '</a> ' . __('to have TEST\'s credentials.', 'woocommerce-gateway-nexi-xpay')
            ),
            'enable_imagetracker' => array(
                'title' => __('Enable/Disable image tracker', 'woocommerce-gateway-nexi-xpay'),
                'type' => 'checkbox',
                'label' => __('Enable image tracer service', 'woocommerce-gateway-nexi-xpay'),
                'default' => 'yes',
                'description' => __('Disable only if with your template you have problem in card logos', 'woocommerce-gateway-nexi-xpay')
                ),
            'title_section_2' => array(
                'title' => __("Gateway Nexi XPay configuration", 'woocommerce-gateway-nexi-xpay'),
                'type' => 'title',
            //'description' => ""),
            ),
            'enabled3ds' => array(
                'title' => __('Enable 3D Secure 2.0', 'woocommerce-gateway-nexi-xpay'),
                'type' => 'checkbox',
                'label' => __('Enable the sending of the fields for the 3D Secure 2.0', 'woocommerce-gateway-nexi-xpay'),
                'default' => 'no',
                'description' => __('The new 3D Secure 2.0 protocol adopted by the main international circuits (Visa, MasterCard, American Express), introduces new authentication methods, able to improve and speed up the cardholder\'s purchase experience.<br><br>By activating this option it is established that the terms and conditions that you submit to your customers, with particular reference to the privacy policy, are foreseen to include the acquisition and processing of additional data provided by the <a href=\"https://ecommerce.nexi.it/specifiche-tecniche/3dsecure20/introduzione.html\" target="_blank">3D Secure 2\.0 Service</a> \(for example, shipping and / or invoicing address, payment details). Nexi and the International Circuits use the additional data collected separately for the purpose of fraud prevention', 'woocommerce-gateway-nexi-xpay')
            ),
            'cartasi_alias' => array(
                'title' => __('Alias', 'woocommerce-gateway-nexi-xpay'),
                'type' => 'text',
                'desc_tip' => __('Given to Merchant by Nexi.', 'woocommerce-gateway-nexi-xpay')
            ),
            'cartasi_mac' => array(
                'title' => __('Key MAC', 'woocommerce-gateway-nexi-xpay'),
                'type' => 'text',
                'desc_tip' => __('Given to Merchant by Nexi.', 'woocommerce-gateway-nexi-xpay')
            )
        );
    }

    /**
     * setta credenziali API per operazioni
     */
    protected function set_api()
    {
        $this->API->set_credentials($this->alias, $this->chiave_segreta);
    }

    /**
     * controlla se ci sono ricorrenze da attivare al ritorno dalla pagina
     * di cassa
     *
     * @param int $order_id Order ID.
     */
    protected function ctrl_return_subscription($order_id, $api = false, $info_payment = null)
    {
        return false;
    }

    /**
     * pagina di invio form, con tasto se non funziona il redirect JS
     *
     * @param type $order_id
     */
    public function wc_xpay_page_invioform($order_id)
    {
        echo '<p>' . __('Please click the button below to pay with Nexi XPay.', 'woocommerce-gateway-nexi-xpay') . '</p>';
        echo $this->wc_xpay_genera_form($order_id);
    }

    /**
     * genera form da inviare
     *
     * @global type $woocommerce
     * @param string $order_id
     * @return string
     */
    public function wc_xpay_genera_form($order_id)
    {
        $param = $this->get_params_form($order_id);
        $aParam = array();
        foreach ($param as $key => $value) {
            $value = addslashes($value);
            $aParam[] = '<input type="hidden" name="' . $key . '" value="' . $value . '" />' . PHP_EOL;
        }

        $nexi_xpay_inputs = implode('', $aParam);
        $submit_form = '<form action="' . $this->url_pagina_cassa . '" method="post" id="nexi_xpay_payment_form">
                                        ' . $nexi_xpay_inputs . '
                                        <input type="submit" class="button alt" id="submit_nexi_payment_form" value="' . __('Pay via Nexi XPay', 'woocommerce-gateway-nexi-xpay') . '" />
                                        <a class="button" style="float:right;" href="' . $param['url_back'] . '">' . __('Cancel order &amp; restore cart', 'woocommerce-gateway-nexi-xpay') . '"</a>
                                </form>
                                <script type="text/javascript">
                                    jQuery( document ).ready(function() {
                                        jQuery(function() {
                                            jQuery("body").block(
                                            {
                                                    message:"' . __("Thank you for your order. Now you'll be redirect to Nexi Payment Page.", 'woocommerce-gateway-nexi-xpay') . '",
                                                    overlayCSS: {background: "#fff",opacity: 0.6},
                                                    css: {padding:20,textAlign:"center",color:"#555",border:"3px solid #aaa",backgroundColor:"#fff",cursor:"wait",lineHeight:"32px"}
                                            });
                                            jQuery("#submit_nexi_payment_form").click();
                                        });
                                    });
                                </script>';

        return $submit_form;
    }

    /**
     * restituisce tutti i parametri necessari per il redirect sulla
     * pagina di cassa (sovrascritta da subscription)
     */
    public function get_params_form($order_id)
    {
        global $woocommerce;

        $order = new WC_Order($order_id);
        $cod_trans = $this->get_cod_trans($order_id);
        $importo = ($order->get_total() * 100);

        $params = array(
            'alias' => $this->alias,
            'importo' => $importo,
            'divisa' => 'EUR',
            'codTrans' => $cod_trans,
            'mail' => $order->get_billing_email(),
            'url' => $this->url_notifica, //url ritorno
            'url_back' => $order->get_cancel_order_url(), //url di annullo
            'languageId' => $this->get_language_payment_page(), //lingua pagina di cassa
            'descrizione' => "WC Order: " . $order->get_order_number(),
            'urlpost' => $this->url_notifica, //url notifica S2S
            'TCONTAB' => $this->contab,
            'Note1' => 'woocommerce',
            'Note2' => $this->wpbo_get_woo_version_number_nexi(),
            'Note3' => $this->module_version
        );
        $params['mac'] = $this->get_mac_calculated($params);
        if ($this->settings['enabled3ds'] == 'yes') {
            $params = array_merge($params, $this->get_params_3ds2($order_id));
        }
        return $params;
    }

    public function get_params_3ds2($order_id)
    {
        $order = new WC_Order($order_id);
        $user_id = $order->get_user_id();

        $user = new WC_Customer($user_id);

        require_once('class-wc-gateway-nexi-xpay-captostatecode.php');

        $params = array();
        if ($order->get_billing_email() != '') {
            $params['Buyer_email'] = $order->get_billing_email();
        }
        if ($order->get_billing_email() != '') {
            $params['Buyer_account'] = $order->get_billing_email();
        }
        if ($order->get_billing_phone() != '') {
            $params['Buyer_homePhone'] = $order->get_billing_phone();
            $params['Buyer_workPhone'] = $order->get_billing_phone();
        }
        if ($order->get_shipping_city() != '') {
            $params['Dest_city'] = $order->get_shipping_city();
        }
        if ($order->get_shipping_country() != '') {
            $params['Dest_country'] = $order->get_shipping_country();
        }
        if ($order->get_shipping_address_1() != '') {
            $params['Dest_street'] = $order->get_shipping_address_1();
        }
        if ($order->get_shipping_address_2() != '') {
            $params['Dest_street2'] = $order->get_shipping_address_2();
        }
        if ($order->get_shipping_postcode() != '') {
            $params['Dest_cap'] = $order->get_shipping_postcode();
        }
        if ($order->get_shipping_state() != '') {
            $params['Dest_stateCode'] = CapToStateCode::getStateCode($order->get_shipping_postcode());
        }
        if ($order->get_billing_city() != '') {
            $params['Bill_city'] = $order->get_billing_city();
        }
        if ($order->get_billing_country() != '') {
            $params['Bill_country'] = $order->get_billing_country();
        }
        if ($order->get_billing_address_1() != '') {
            $params['Bill_street'] = $order->get_billing_address_1();
        }
        if ($order->get_billing_address_2() != '') {
            $params['Bill_street2'] = $order->get_billing_address_2();
        }
        if ($order->get_billing_postcode() != '') {
            $params['Bill_cap'] = $order->get_billing_postcode();
        }
        if ($order->get_billing_state() != '') {
            $params['Bill_stateCode'] = CapToStateCode::getStateCode($order->get_billing_postcode());
        }

        $userParams = array();
        if ($user->get_date_created() != '') {
            $userParams['chAccDate'] = $this->getChAccDate($user);
        }
        if ($this->getAccountDateIndicator($user->get_date_created()) != '') {
            $userParams['chAccAgeIndicator'] = $this->getAccountDateIndicator($user->get_date_created());
        }
        if ($this->getOrderInLastSixMonth() != '') {
            $userParams['nbPurchaseAccount'] = $this->getOrderInLastSixMonth();
        }
        if ($this->getLastUsagedestinationAddress($order->get_shipping_city(), $order->get_shipping_country(), $order->get_shipping_address_1(), $order->get_shipping_address_2(), $order->get_shipping_postcode(), $order->get_shipping_state()) != '') {
            $userParams['destinationAddressUsageDate'] = $this->getLastUsagedestinationAddress($order->get_shipping_city(), $order->get_shipping_country(), $order->get_shipping_address_1(), $order->get_shipping_address_2(), $order->get_shipping_postcode(), $order->get_shipping_state());
        }
        if ($this->getDateIndicator($this->getFirstUsagedestinationAddress($order->get_shipping_city(), $order->get_shipping_country(), $order->get_shipping_address_1(), $order->get_shipping_address_2(), $order->get_shipping_postcode(), $order->get_shipping_state())) != '') {
            $userParams['destinationAddressUsageIndicator'] = $this->getDateIndicator($this->getFirstUsagedestinationAddress($order->get_shipping_city(), $order->get_shipping_country(), $order->get_shipping_address_1(), $order->get_shipping_address_2(), $order->get_shipping_postcode(), $order->get_shipping_state()));
        }
        if ($this->checkName($order->get_shipping_first_name(), $order->get_shipping_last_name()) != '') {
            $userParams['destinationNameIndicator'] = $this->checkName($order->get_shipping_first_name(), $order->get_shipping_last_name());
        }
        if ($this->getReorderItemsIndicator($order) != '') {
            $userParams['reorderItemsIndicator'] = $this->getReorderItemsIndicator($order);
        }

        if ($user_id != 0) {
            $params = array_merge($params, $userParams);
        }
        return $params;
    }

    private function getChAccDate($user)
    {
        $date = $user->get_date_created();
        return $date->format("Y-m-d");
    }

    private function getReorderItemsIndicator($order)
    {
        $customer_orders = $this->getUserOrders();
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            foreach ($customer_orders as $customer_order) {
                if ($customer_order->ID != $order->get_order_number()) {
                    $ord = new WC_Order($customer_order->ID);
                    $products = $ord->get_items();
                    foreach ($products as $product) {
                        $old_product_id = $product->get_product_id();
                        if ($old_product_id == $product_id) {
                            return '02';
                            //reorder for one product in cart
                        }
                    }
                }
            }
        }
        //First order for all product
        return '01';
    }

    private function checkName($first_name, $last_name)
    {
        $user_id = get_current_user_id();
        $user = new WC_Customer($user_id);
        if ($first_name == $user->get_first_name() && $last_name == $user->get_last_name()) {
            return '01';
        }
        return '02';
    }

    private function getLastUsagedestinationAddress($city, $country, $street_1, $street_2, $postcode, $state)
    {
        $customer_orders = $this->getUserOrders();
        $date = null;
        foreach ($customer_orders as $customer_order) {
            $order = new WC_Order($customer_order->ID);
            if ($order->get_shipping_city() == $city && $order->get_shipping_country() == $country && $order->get_shipping_address_1() == $street_1 && $order->get_shipping_address_2() == $street_2 && $order->get_shipping_postcode() == $postcode && $order->get_shipping_state() == $state) {
                if ($customer_order->post_date > $date) {
                    $date = $customer_order->post_date;
                }
            }
        }
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $date);
        return $date->format('Y-m-d');
    }

    private function getFirstUsagedestinationAddress($city, $country, $street_1, $street_2, $postcode, $state)
    {
        $customer_orders = $this->getUserOrders();
        $date = date('Y-m-d H:i:s');
        foreach ($customer_orders as $customer_order) {
            $order = new WC_Order($customer_order->ID);
            if ($order->get_shipping_city() == $city && $order->get_shipping_country() == $country && $order->get_shipping_address_1() == $street_1 && $order->get_shipping_address_2() == $street_2 && $order->get_shipping_postcode() == $postcode && $order->get_shipping_state() == $state) {
                if ($customer_order->post_date < $date) {
                    $date = $customer_order->post_date;
                }
            }
        }
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $date);

        return $date->format('Y-m-d');
    }

    private function getOrderInLastSixMonth()
    {
        $customer_orders = $this->getUserOrders();
        $today = date("Y-m-d");
        $newDate = new DateTime($today. ' - 6 month');
        $count = 0;
        foreach ($customer_orders as $customer_order) {
            if ($customer_order->post_date >= $newDate->format("Y-m-d")) {
                $count += 1;
            }
        }
        return $count;
    }

    private function getUserOrders()
    {
        return get_posts(array(
                  'numberposts' => -1,
                  'meta_key'    => '_customer_user',
                  'orderby' => 'date',
                  'order' => 'DESC',
                  'meta_value'  => get_current_user_id(),
                  'post_type'   => wc_get_order_types(),
                  'post_status' => array_keys(wc_get_order_statuses()),
                  ));
    }

    private function getAccountDateIndicator($date)
    {
        $today = date("Y-m-d");
        if ($date == false) {
            //Account not registred
            return '01';
        }
        if ($date->format("Y-m-d") == $today) {
            //Account Created in this transaction
            return '02';
        }
        $newDate = new DateTime($today. ' - 30 day');
        if ($date->format("Y-m-d") >= $newDate->format("Y-m-d")) {
            //Account created in last 30 days
            return '03';
        }
        $newDate = new DateTime($today. ' - 60 day');
        if ($date->format("Y-m-d") >= $newDate->format("Y-m-d")) {
            //Account created from 30 to 60 days ago
            return '04';
        }
        if ($date->format("Y-m-d") < $newDate->format("Y-m-d")) {
            //Account created more then 60 days ago
            return '05';
        }
    }

    private function getDateIndicator($date)
    {
        $date = new DateTime($date);
        $today = date("Y-m-d");
        if ($date->format("Y-m-d") == $today) {
            //Account Created in this transaction
            return '01';
        }
        $newDate = new DateTime($today. ' - 30 day');
        if ($date->format("Y-m-d") >= $newDate->format("Y-m-d")) {
            //Account created in last 30 days
            return '02';
        }
        $newDate = new DateTime($today. ' - 60 day');
        if ($date->format("Y-m-d") >= $newDate->format("Y-m-d")) {
            //Account created from 30 to 60 days ago
            return '03';
        }
        if ($date->format("Y-m-d") < $newDate->format("Y-m-d")) {
            //Account created more then 60 days ago
            return '04';
        }
    }
    /**
     * Funzione obbigatoria per WP, processa il pagamento e fa il redirect
     *
     * @param type $order_id
     * @return type
     */
    public function process_payment($order_id)
    {
        $order = new WC_Order($order_id);
        update_post_meta($order_id, '_post_data', $_POST);
        return array('result' => 'success', 'redirect' => $order->get_checkout_payment_url(true));
    }

    /**
     * Se la valuta è EUR il modulo è disponibile tra le opzioni
     */
    public function is_available()
    {
        return parent::is_available();
    }
    public function get_available_methods()
    {
        global $woocommerce;
        $this->process_admin_options();
        $this->log_action('info', "xpay get_available_methods");
        $avaiable_methods = $this->API->get_available_methods($woocommerce->version, WC_GATEWAY_XPAY_VERSION);
        if ($avaiable_methods) {
            update_option('xpay_available_methods', json_encode($this->API->response['availableMethods']));
            update_option('xpay_logo_small', $this->API->response['urlLogoNexiSmall']);
            update_option('xpay_logo_large', $this->API->response['urlLogoNexiLarge']);
        } else {
            delete_option('xpay_available_methods');
            delete_option('xpay_logo_small');
            delete_option('xpay_logo_large');
        }
    }
}
