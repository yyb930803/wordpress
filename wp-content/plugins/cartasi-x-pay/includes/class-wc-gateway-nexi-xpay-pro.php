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

class WC_Gateway_XPay_Pro extends WC_Gateway_XPay_Easy
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        array_push($this->supports, 'tokenization');

        $this->set_api();
    }

    /**
     * Setta i campi per il form di configurazione,
     * se attivato recurring viene sovrascritte dalla classe figlia
     */

    /**
     * sovrascrive funzione classe padre
     */
    public function init_form_fields()
    {
        parent::init_form_fields();
        $form_fields_rico = array(
            'title_section_3' => array(
                'title' => __("OneClick checkout configuration", 'woocommerce-gateway-nexi-xpay'),
                'type' => 'title',
            //'description' => ""),
            ),
            'abilita_modulo_oneclick' => array(
                'title' => __('Enable/Disable OneClick', 'woocommerce-gateway-nexi-xpay'),
                'type' => 'checkbox',
                'label' => __('Enable Nexi XPay for OneClick payment', 'woocommerce-gateway-nexi-xpay'),
                'default' => 'no',
                'description' => ''
            ),
            'cartasi_alias_oneclick' => array(
                'title' => __('OneClick Alias', 'woocommerce-gateway-nexi-xpay'),
                'type' => 'text',
                'desc_tip' => __('Given to Merchant by Nexi.', 'woocommerce-gateway-nexi-xpay')
            ),
            'cartasi_mac_oneclick' => array(
                'title' => __('OneClick key MAC', 'woocommerce-gateway-nexi-xpay'),
                'type' => 'text',
                'desc_tip' => __('Given to Merchant by Nexi.', 'woocommerce-gateway-nexi-xpay')
            ),
            'gruppo_oneclick' => array(
                'title' => __('Group', 'woocommerce-gateway-nexi-xpay'),
                'type' => 'text',
                'desc_tip' => __('Given to Merchant by Nexi.', 'woocommerce-gateway-nexi-xpay')
            )
        );
        $this->form_fields = array_merge($this->form_fields, $form_fields_rico);
    }

    /**
     * setta credenziali API per operazioni
     */
    protected function set_api()
    {
        parent::set_api();
        $this->API->set_credentials_oneclick($this->alias_oneclick, $this->chiave_segreta_oneclick, $this->gruppo_oneclick);
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
     * restituisce tutti i parametri necessari per il redirect sulla
     * pagina di cassa (sovrascritta da subscription)
     */
    public function get_params_form($order_id)
    {
        $params = parent::get_params_form($order_id);

        //Se fleggato il "ricordami" sovrascrivo valori con ONECLICK
        if ($_REQUEST['xpay-oc'] === "true") {
            //Recupero ID cliente
            $costumer_id = get_post_meta($order_id, '_customer_user', true);
            //Aggiungo info per primo pagamento oneclick
            $params['num_contratto'] = substr("OCXP_" . $costumer_id . "_" . time(), 0, 30); //Numero contratto con XPAY
            $params['codTrans'] = $this->get_cod_trans($order_id, "PP-OC"); //Sovrascrivo codTrans per capire che è un PP
            $params['tipo_servizio'] = "paga_multi"; //FISSO
            $params['tipo_richiesta'] = "PP"; //Primo Pagamento
            $params['gruppo'] = $this->gruppo_oneclick; //Da config
            $params['mac'] = $this->get_mac_calculated($params);
            //}
        }



        return $params;
    }

    /**
     * Funzione obbigatoria per WP, processa il pagamento e fa il redirect
     * oppure se c'è token utilizza API per il pagamento OneClick
     *
     * @param type $order_id
     * @return type
     */
    public function process_payment($order_id)
    {
        $order = new WC_Order($order_id);

        //ONECLICK
        if (isset($_POST['wc-' . $this->id . '-payment-token']) && 'new' !== $_POST['wc-' . $this->id . '-payment-token']) {
            return $this->one_click_payment_process($order_id);
        } else { //REDIRECT
            update_post_meta($order_id, '_post_data', $_POST);
            if (isset($_REQUEST['wc-' . $this->id . '-new-payment-method'])) {
                return array('result' => 'success', 'redirect' => $order->get_checkout_payment_url(true) . "&xpay-oc=" . $_REQUEST['wc-' . $this->id . '-new-payment-method']);
            }
            return array('result' => 'success', 'redirect' => $order->get_checkout_payment_url(true) . "&xpay-oc=");
        }
    }

    /**
     *
     * @param type $order
     * @return type
     */
    protected function one_click_payment_process($order_id)
    {
        $order = new WC_Order($order_id);
        $oXPayToken = new WC_Nexi_XPay_Token();
        $token = $oXPayToken->get_token_nexi($_POST['wc-' . $this->id . '-payment-token']);

        if ($token == false) {//Faccio redirect se token non valido
            update_post_meta($order_id, '_post_data', $_POST);
            return array('result' => 'success', 'redirect' => $order->get_checkout_payment_url(true));
        }

        $scadenza_pan = $token->get_expiry_year() . $token->get_expiry_month();
        $cod_trans = $this->get_cod_trans($order_id, "PR-OC");
        if ($this->API->recurring_payment($token->get_token(), $scadenza_pan, $order->get_total(), $cod_trans, 'woocommerce', $this->wpbo_get_woo_version_number_nexi(), $this->module_version) === true) {

            //SALVO INFO XPAY IN ORDINE
            $aInfoPayment = $this->API->response;
            $aInfoPayment['codTrans'] = $cod_trans;
            $aInfoPayment['importo'] = $order->order_total * 100;
            $aInfoPayment['scadenza_pan'] = $scadenza_pan;
            $aInfoPayment['orario'] = $this->API->response['ora'];
            $aInfoPayment['num_contratto'] = $token->get_token();

            $oInfoOrderXPay = new WC_Gateway_XPay_Order_Payment_Info($order_id);
            $oInfoOrderXPay->SetInfoXPay($aInfoPayment);

            $order->payment_complete();
            $order->add_order_note(__("Thank you for shopping with us. Your account has been charged and your transaction is successful. We will be shipping your order to you soon.", 'woocommerce-gateway-nexi-xpay'));

            //Controlla se ci sono ricorrenze e le attiva
            $this->ctrl_return_subscription($order_id, true, $aInfoPayment);

            WC()->cart->empty_cart();

            return array('result' => 'success', 'redirect' => $this->get_return_url($order));
        } else {
            return array('result' => 'failure', 'message' => __("Thank you for shopping with us. However, the transaction has been declined.", 'woocommerce-gateway-nexi-xpay') . " - " . ($this->API->response['errore']['messaggio']));
        }
    }
    /**
     * Se la valuta è EUR il modulo è disponibile tra le opzioni
     */
    public function is_available()
    {
        return parent::is_available();
    }
}
