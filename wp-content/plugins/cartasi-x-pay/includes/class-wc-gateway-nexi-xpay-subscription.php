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

class WC_Gateway_XPay_Subscription extends WC_Gateway_XPay_Pro
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        array_push(
            $this->supports,
                'subscriptions',
                'subscription_cancellation',
                'subscription_suspension',
                'subscription_reactivation',
                'subscription_amount_changes',
                'subscription_date_changes',
                'subscription_payment_method_change',
                'subscription_payment_method_change_customer'
        );


        add_action('woocommerce_scheduled_subscription_payment_' . $this->id, array($this, 'scheduled_subscription_payment'), 10, 2);
        add_filter('wcs_view_subscription_actions', array($this, 'eg_remove_my_subscriptions_button'), 100, 2);
    }


    public function eg_remove_my_subscriptions_button($actions, $subscription)
    {
        foreach ($actions as $action_key => $action) {
            switch ($action_key) {
                    case 'change_payment_method':	// Hide "Change Payment Method" button?
                         unset($actions[ $action_key ]);
                        break;
                }
        }
        return $actions;
    }

    /**
     * setta credenziali API per ricorrenze
     */
    protected function set_api()
    {
        parent::set_api();
        $this->API->set_credentials_rico($this->alias_rico, $this->chiave_segreta_rico, $this->gruppo_rico);
    }

    /**
     * sovrascrive funzione classe padre
     */
    public function init_form_fields()
    {
        parent::init_form_fields();
        $form_fields_rico = array(
            'title_section_4' => array(
                'title' => __("Subscription configuration", 'woocommerce-gateway-nexi-xpay'),
                'type' => 'title',
            //'description' => ""),
            ),
            'abilita_modulo_ricorrenze' => array(
                'title' => __('Enable/Disable Recurring', 'woocommerce-gateway-nexi-xpay'),
                'type' => 'checkbox',
                'label' => __("Enable Nexi XPay for subscription's payment", 'woocommerce-gateway-nexi-xpay'),
                'default' => 'no',
                'description' => ''
            ),
            'cartasi_alias_rico' => array(
                'title' => __('Recurring Alias', 'woocommerce-gateway-nexi-xpay'),
                'type' => 'text',
                'desc_tip' => __('Given to Merchant by Nexi.', 'woocommerce-gateway-nexi-xpay')
            ),
            'cartasi_mac_rico' => array(
                'title' => __('Recurring key MAC', 'woocommerce-gateway-nexi-xpay'),
                'type' => 'text',
                'desc_tip' => __('Given to Merchant by Nexi.', 'woocommerce-gateway-nexi-xpay')
            ),
            'gruppo_rico' => array(
                'title' => __('Group', 'woocommerce-gateway-nexi-xpay'),
                'type' => 'text',
                'desc_tip' => __('Given to Merchant by Nexi.', 'woocommerce-gateway-nexi-xpay')
            ),
        );
        $this->form_fields = array_merge($this->form_fields, $form_fields_rico);
    }

    /**
     * Check if order contains subscriptions.
     *
     * @param  int $order_id Order ID.
     * @return bool Returns true of order contains subscription.
     */
    protected function order_contains_subscription($order_id)
    {
        return function_exists('wcs_order_contains_subscription') && (wcs_order_contains_subscription($order_id) || wcs_order_contains_renewal($order_id));
    }

    /**
     * controlla se ci sono ricorrenze da attivare al ritorno dalla pagina
     * di cassa
     *
     * @param int $order_id Order ID.
     */
    protected function ctrl_return_subscription($order_id, $api = false, $info_payment = null)
    {

        //SE non contiene ricorrenze l'ordine processato, richiamo la funzione padre
        if (!$this->order_contains_subscription($order_id) && !wcs_is_subscription($order_id)) {
            return false;
        }

        //Se ritorno da API o da REDIRECT
        if ($api) {
            $num_contratto = isset($info_payment['num_contratto']) ? $info_payment['num_contratto'] : false;
            $scadenza_pan = isset($info_payment['scadenza_pan']) ? $info_payment['scadenza_pan'] : false;
        } else {
            $num_contratto = isset($_POST['num_contratto']) ? wc_clean($_POST['num_contratto']) : false;
            $scadenza_pan = isset($_POST['scadenza_pan']) ? wc_clean($_POST['scadenza_pan']) : false;
            $info_payment = $_REQUEST;
        }

        //SE non c'è num contratto in risposta da Nexi o sono disabilitati le ricorrenze automatiche
        if (!$num_contratto && get_option('woocommerce_subscriptions_turn_off_automatic_payments') === 'yes') {
            return false;
        }

        try {
            $subscriptions = wcs_get_subscriptions_for_order($order_id);
            foreach ($subscriptions as $subscription) {
                $subscription_id = wc_nxp_get_order_prop($subscription, 'id');
                //SALVO INFO XPAY IN SUBSCRIPTION
                $oInfoOrderXPay = new WC_Gateway_XPay_Order_Payment_Info($subscription_id);
                $oInfoOrderXPay->SetInfoXPay($info_payment);
            }
        } catch (Exception $e) {
            wc_add_notice(sprintf(__('Error: %s', 'woocommerce-gateway-nexi-xpay'), $e->getMessage()), 'error');
            return;
        }
    }

    /**
     *
     * @param type $order_id
     */
    protected function ctrl_return_subscription_api($order_id, $response)
    {
    }

    /**
     * restituisce tutti i parametri necessari per il redirect sulla
     * pagina di cassa (estende la funzione del padre)
     */
    public function get_params_form($order_id)
    {
        $params = parent::get_params_form($order_id);

        //SE ordine contiene ricorrenze e non è già stato sovrascritto con ONECLICK
        if (WC_Subscriptions_Cart::cart_contains_subscription() /* && $_REQUEST['xpay-oc'] !== "true" */) {
            if ($_REQUEST['xpay-oc'] !== "true") {
                //Recupero ID cliente
                $costumer_id = get_post_meta($order_id, '_customer_user', true);

                //Aggiungo info per primo pagamento ricorrente
                $params['num_contratto'] = "NCXP_" . $costumer_id . "_" . $order_id; //Numero contratto con XPAY
                $params['tipo_servizio'] = "paga_multi"; //FISSO
                $params['tipo_richiesta'] = "PP"; //Primo Pagamento
                $params['codTrans'] = $this->get_cod_trans($order_id, "PP"); //Sovrascrivo codTrans per capire che è un PP
                $params['gruppo'] = $this->gruppo_rico; //Da config
                $params['mac'] = $this->get_mac_calculated($params);
            }
            //setto di utilizzare per forza carte di credito e Paypal per poter scatenare ricorrenze successive
            $params['selectedcard'] = "CC";
            $avaiable_methods = json_decode(WC_Admin_Settings::get_option('xpay_available_methods'));
            foreach ($avaiable_methods as $method) {
                if ($method->type != 'CC' && $method->recurring == 'Y') {
                    if ($method->selectedcard == 'PAYPAL') {
                        $params['selectedcard'] .= ',' . $method->selectedcard;
                    }
                }
            }
        }
        return $params;
    }

    /**
     *
     */
    public function scheduled_subscription_payment($amount_to_charge, $order)
    {
        $this->set_api();
        $order_id = wc_nxp_get_order_prop($order, 'id');

        $oInfoOrderXPay = new WC_Gateway_XPay_Order_Payment_Info($order_id);
        $num_contratto = $oInfoOrderXPay->GetInfoXPay('num_contratto');
        $scadenza_pan = $oInfoOrderXPay->GetInfoXPay('scadenza_pan');
        $brand = $oInfoOrderXPay->GetInfoXPay('brand');

        try {
            if (!$num_contratto) {
                throw new Exception(sprintf(__('num_contratto for XPay was not found in order #%s.', 'woocommerce-gateway-nexi-xpay'), $order_id));
            }
            if ($brand != 'PAYPAL' && !$scadenza_pan) {
                throw new Exception(sprintf(__('scadenza pan for XPay was not found in order #%s.', 'woocommerce-gateway-nexi-xpay'), $order_id));
            }

            $cod_trans = $this->get_cod_trans($order_id, "PR");
            $recurring = $this->API->recurring_payment($num_contratto, $scadenza_pan, $amount_to_charge, $cod_trans, 'woocommerce', $this->wpbo_get_woo_version_number_nexi(), $this->module_version) === true;
            if (is_wp_error($recurring)) {
                throw new Exception(sprintf(__('error: #%s.', 'woocommerce-gateway-nexi-xpay'), $recurring->get_error_message()));
            }
            if ($recurring) {

                //SALVO INFO XPAY IN ORDINE-SUBSCRIPTION
                $aInfoOrderParent = $oInfoOrderXPay->GetInfoXPay(); //Info ritorno ordine padre
                $aInfoOrderRecurring = $this->API->response; //Info ritorno ricorrenza

                $aInfoOrderParent['codTrans'] = $cod_trans;
                $aInfoOrderParent['importo'] = $amount_to_charge * 100;
                $aInfoOrderParent['data'] = $aInfoOrderRecurring['data'];
                $aInfoOrderParent['messaggio'] = "";
                $aInfoOrderParent['orario'] = "";

                $oInfoOrderXPay->SetInfoXPay($aInfoOrderParent);

                $order->payment_complete();
            } else {
                $order->update_status('failed', __('Could not authorize payment.', 'woocommerce-gateway-amazon-payments-advanced'));
            }
        } catch (Exception $e) {
            $order->add_order_note(sprintf(__('Nexi XPay subscription renewal failed - %s', 'woocommerce-gateway-nexi-xpay'), $e->getMessage()));
        }
    }

    /**
     * Se la nella config è stato abilitato il modulo ai pagamenti recurring
     */
    public function is_available()
    {
        return parent::is_available();
    }
}
