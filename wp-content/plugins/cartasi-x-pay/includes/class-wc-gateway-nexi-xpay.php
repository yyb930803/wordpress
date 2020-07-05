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

class WC_Gateway_XPay extends WC_Payment_Gateway
{
    protected $API;
    protected $module_version;
    protected $cartasi_alias = null;
    protected $cartasi_mac = null;
    protected $cartasi_form_language = null;
    protected $cartasi_modalita_test = null;
    protected $abilita_modulo_oneclick = null;
    protected $abilita_modulo_ricorrenze = null;
    protected $cartasi_alias_rico = null;
    protected $cartasi_mac_rico = null;
    protected $gruppo_rico = null;
    protected $cartasi_alias_oneclick = null;
    protected $cartasi_mac_oneclick = null;
    protected $gruppo_oneclick = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->method_title = __('Nexi XPay', 'woocommerce-gateway-nexi-xpay');
        $this->method_description = __('Nexi XPay credit/debit card payment gateway.', 'woocommerce-gateway-nexi-xpay');
        $this->method_description .= '<br><p>' . __('For a correct behavior of the module, check in the configuration section of the Nexi back-office that the transaction cancellation in the event of a failed notification is set.', 'woocommerce-gateway-nexi-xpay') . '</p>';
        $this->id = 'xpay';
        $this->module_version = WC_GATEWAY_XPAY_VERSION;

        //URL DA IMPOSTARE PER RIMANDARE A BACKOFFICE NEXI SU ORDINE
        //$this->view_transaction_url = 'URL BACKOFFICE CON COD TRANS:%s';

        $this->init_form_fields();
        $this->init_options();
        $this->init_settings();
        $this->init_nexi_env();

        $this->icon = $this->get_icon_checkout_page();

        $this->msg = array();
        $this->msg['message'] = '';
        $this->msg['class'] = '';
    }

    protected function get_icon_checkout_page()
    {
        if (WC_Admin_Settings::get_option('xpay_logo_small') == "") {
            $icon = plugins_url('assets/images/logo.jpg', plugin_dir_path(__FILE__));
        } elseif (isset($this->settings['enable_imagetracker']) && $this->settings['enable_imagetracker'] == 'yes') {
            $icon = WC_Admin_Settings::get_option('xpay_logo_small');
        } else {
            $icon = plugins_url('assets/images/logo.jpg', plugin_dir_path(__FILE__));
        }
        $icon .= "?platform=woocommerce&platformVers=" . $this->wpbo_get_woo_version_number_nexi() . "&"
                . "pluginVers=" . $this->module_version . "&mac=" . $this->get_mac_for_icon();
        return $icon;
    }

    protected function get_mac_for_icon()
    {
        $timeStamp = (time()) * 1000;
        return sha1("apiKey=" . $this->cartasi_alias . "timeStamp=" . $timeStamp . "chiaveSegreta=" . $this->cartasi_mac);
    }

    public function log_action($log_type, $message)
    {
        $logger = wc_get_logger();
        $context = array('source' => 'woocommerce-gateway-nexi-xpay');
        $logger->log($log_type, $message, $context);
    }

    public function get_method_form_fields()
    {
        return null;
    }

    /**
     *
     */
    public function init_options()
    {
        $this->title = __("Payment Cards", 'woocommerce-gateway-nexi-xpay');
        $this->contab = $this->get_option('contabilizzazione');
        $this->set_description();
    }

    /**
     *
     */
    protected function set_description()
    {
        $avaiable_methods = json_decode(WC_Admin_Settings::get_option('xpay_available_methods'), true);
        $method_list = array();
        if (is_array($avaiable_methods)) {
            foreach ($avaiable_methods as $am) {
                if ($am['type'] == "CC") {
                    $method_list[] = $am['description'];
                }
            }
            foreach ($avaiable_methods as $am) {
                if ($am['type'] != "CC") {
                    $method_list[] = $am['description'];
                }
            }
        }
        if (isset($this->settings['enable_imagetracker']) && $this->settings['enable_imagetracker'] == 'yes') {
            $img_list = $this->get_logos_list();
        } else {
            $img_list = '';
        }

        $this->description = $img_list . __("Pay securely by credit and debit card or alternative payment methods through Nexi.", 'woocommerce-gateway-nexi-xpay');

        if (is_array($method_list) && count($method_list) > 0) {
            $this->description .= __(" Accepted methods:", 'woocommerce-gateway-nexi-xpay') . " " . implode(", ", $method_list) . ".";
        }

        $this->instructions = $this->get_option('instructions', $this->description);
    }

    protected function get_logos_list()
    {
        $avaiable_methods = json_decode(WC_Admin_Settings::get_option('xpay_available_methods'), true);
        $img_list = "";
        if (is_array($avaiable_methods)) {
            foreach ($avaiable_methods as $am) {
                if ($am['type'] == "CC") {
                    $img_list .= self::getImg($am['image'], $am['code']);
                }
            }
            foreach ($avaiable_methods as $am) {
                if ($am['type'] != "CC") {
                    $img_list .= self::getImg($am['image'], $am['code']);
                }
            }
        }
        if ($img_list != "") {
            $img_list = "<span id='xpay_list_icon' style='margin-bottom:10px;width:auto;display:inline-block;'>" . $img_list . "</span><br>";
        }
        return $img_list;
    }

    private static function getImg($link, $code)
    {
        return "<span style='display:inline-block; height:40px; float:left;" . self::getImgStyle($code) . "'>"
                        ."<img src='" . $link . "'  style='height:100%;float: none;position: unset;display: block;'>"
                    ."</span>";
    }

    public static function getImgStyle($code)
    {
        $configuration = array(
            'maestro' => 'padding-right:10px; padding-top:6px; padding-bottom:6px;',
            'amex' => 'padding-right:10px; padding-top:6px; padding-bottom:6px;',
            'mastercard' => 'padding-right:10px; padding-top:6px; padding-bottom:6px;',
            'visa' => 'padding-right:10px; padding-top:9px; padding-bottom:9px;',
            'paypal' => 'padding-right:14px; padding-top:10px; padding-bottom:10px;',
            'sofort' => 'padding-right:15px; padding-top:8px; padding-bottom:8px;',
            'amazonpay' => 'padding-right:15px; padding-top:8px; padding-bottom:8px;',
            'googlepay' => 'padding-right:14px; padding-top:9px; padding-bottom:9px;width:70px;',
            'alipay' => 'padding-right:10px; padding-top:6px; padding-bottom:0px;',
            'wechatpay' => 'padding-right:10px; padding-top:6px; padding-bottom:6px;',
            'masterpass' => 'padding-right:13px; padding-top:8px; padding-bottom:8px;',
            'applepay' => 'padding-right:15px; padding-top:8px; padding-bottom:8px;',
            'nexi' => 'padding-right:15px; padding-top:10px; padding-bottom:10px;'
        );
        if (isset($configuration[strtolower($code)])) {
            return $configuration[strtolower($code)];
        } else {
            return "";
        }
    }

    /**
     *
     */
    public function init_settings()
    {
        parent::init_settings();
        $this->cartasi_alias = (isset($this->settings['cartasi_alias'])) ? trim($this->settings['cartasi_alias']) : "";
        $this->cartasi_mac = (isset($this->settings['cartasi_mac'])) ? trim($this->settings['cartasi_mac']) : "";
        $this->cartasi_form_language = (isset($this->settings['cartasi_form_language'])) ? $this->settings['cartasi_form_language'] : "";
        $this->cartasi_modalita_test = (isset($this->settings['cartasi_modalita_test'])) ? $this->settings['cartasi_modalita_test'] : "";
        $this->abilita_modulo_oneclick = (isset($this->settings['abilita_modulo_oneclick'])) ? $this->settings['abilita_modulo_oneclick'] : "";
        $this->abilita_modulo_ricorrenze = (isset($this->settings['abilita_modulo_ricorrenze'])) ? $this->settings['abilita_modulo_ricorrenze'] : "";
        $this->cartasi_alias_rico = (isset($this->settings['cartasi_alias_rico'])) ? trim($this->settings['cartasi_alias_rico']) : "";
        $this->cartasi_mac_rico = (isset($this->settings['cartasi_mac_rico'])) ? trim($this->settings['cartasi_mac_rico']) : "";
        $this->gruppo_rico = (isset($this->settings['gruppo_rico'])) ? $this->settings['gruppo_rico'] : "";
        $this->cartasi_alias_oneclick = (isset($this->settings['cartasi_alias_oneclick'])) ? trim($this->settings['cartasi_alias_oneclick']) : "";
        $this->cartasi_mac_oneclick = (isset($this->settings['cartasi_mac_oneclick'])) ? trim($this->settings['cartasi_mac_oneclick']) : "";
        $this->gruppo_oneclick = (isset($this->settings['gruppo_oneclick'])) ? $this->settings['gruppo_oneclick'] : "";
    }

    /**
     *
     */
    public function init_nexi_env()
    {
        $this->url_test = 'https://int-ecommerce.nexi.it/';
        $this->url_produzione = 'https://ecommerce.nexi.it/';
        $this->uri_pagina_cassa = "ecomm/ecomm/DispatcherServlet";
        $this->url_notifica = home_url('?wc-api=WC_Gateway_XPay'); // home_url('/wc-api/WC_Gateway_XPay');

        if ($this->cartasi_modalita_test == "yes") {
            $this->url_gateway = $this->url_test;
        } else {
            $this->url_gateway = $this->url_produzione;
        }

        $this->alias = $this->cartasi_alias;
        $this->alias_rico = $this->cartasi_alias_rico;
        $this->alias_oneclick = $this->cartasi_alias_oneclick;
        $this->chiave_segreta = trim($this->cartasi_mac);
        $this->chiave_segreta_rico = trim($this->cartasi_mac_rico);
        $this->chiave_segreta_oneclick = trim($this->cartasi_mac_oneclick);
        $this->url_pagina_cassa = $this->url_gateway . $this->uri_pagina_cassa;

        $this->init_api();
    }

    /**
     *
     */
    private function init_api()
    {
        $this->API = new WC_Gateway_XPay_Api();
        $this->API->set_env($this->url_gateway);
        $this->API->set_contab($this->contab);
    }

    /**
     * funzione che resitiuisce le opzioni della tendina in configurazione
     * per la scelta della lingua della pagina di cassa
     */
    public function get_options_config_language()
    {
        if (get_locale() == 'it_IT') {
            $LANG_IT = "Italiano";
            $LANG_EN = "Inglese";
            $LANG_SP = "Spagnolo";
            $LANG_PR = "Portoghese";
            $LANG_FR = "Francese";
            $LANG_DE = "Tedesco";
            $LANG_JP = "Giapponese";
            $LANG_AR = "Arabo";
            $LANG_CH = "Cinese";
            $LANG_RU = "Russo";
            $LANG_AUTO = "Automatico";
        } else {
            $LANG_IT = "Italian";
            $LANG_EN = "English";
            $LANG_SP = "Spanish";
            $LANG_PR = "Portuguese";
            $LANG_FR = "Franch";
            $LANG_DE = "German";
            $LANG_JP = "Japanese";
            $LANG_AR = "Arabic";
            $LANG_CH = "Chinese";
            $LANG_RU = "Russian";
            $LANG_AUTO = "Automatic";
        }

        $cartasi_form_language_ids = array(
            "AUTO" => $LANG_AUTO,
            "ITA" => $LANG_IT,
            "ARA" => $LANG_AR,
            "CHI" => $LANG_CH,
            "ENG" => $LANG_EN,
            "RUS" => $LANG_RU,
            "SPA" => $LANG_SP,
            "POR" => $LANG_PR,
            "FRA" => $LANG_FR,
            "GER" => $LANG_DE,
            "JPG" => $LANG_JP,
        );

        return $cartasi_form_language_ids;
    }

    /**
     * elenco circuiti e metodi di pagamento accettati da XPAY per l'esercente
     * da mostrare in configurazione per descrizione
     *
     * @return array
     */
    public function get_options_config_cc_accepted()
    {
        $nexi_form_cc_accepted = array(
            "Amazon Pay" => "Amazon Pay",
            "American Express" => "American Express",
            "Apple Pay" => "Apple Pay",
            "Diners" => "Diners",
            "Google Pay" => "Google Pay",
            "Klarna" => "Klarna",
            "Maestro" => "Maestro",
            "MasterCard" => "MasterCard",
            "Masterpass" => "Masterpass",
            "MyBank" => "MyBank",
            "PayPal" => "PayPal",
            "Sofort" => "Sofort",
            "VISA" => "VISA",
            "VISA Electron" => "VISA Electron",
            "VPAY" => "VPAY"
        );
        return $nexi_form_cc_accepted;
    }

    /**
     * elenco opzioni di contabilizzazione da mostrare in configurazione
     *
     * @return array
     */
    public function get_options_config_contab()
    {
        $contab_ids = array(
            "C" => __('Immediate', 'woocommerce-gateway-nexi-xpay'),
            "D" => __('Deferred', 'woocommerce-gateway-nexi-xpay')
        );
        return $contab_ids;
    }

    /**
     * ritorna mac calcolato da inviare a pagina XPAY per pagamento
     *
     * @param type $params
     * @return type
     */
    protected function get_mac_return_calculated($params)
    {
        return sha1('codTrans=' . $params['codTrans'] .
                'esito=' . $params['esito'] .
                'importo=' . $params['importo'] .
                'divisa=' . $params['divisa'] .
                'data=' . $params['data'] .
                'orario=' . $params['orario'] .
                'codAut=' . $params['codAut'] .
                $this->chiave_segreta);
    }

    /**
     * Se non il cliente non è nella pagina di amministrazione account
     * Se la valuta è EUR il modulo è disponibile tra le opzioni
     */
    public function is_available()
    {
        if (is_add_payment_method_page()) {
            return false;
        }
        $currency = get_woocommerce_currency();
        return (parent::is_available() && ($currency === "EUR"));
    }

    /**
     * ritorna mac calcolato da inviare a pagina XPAY per pagamento
     *
     * @param type $params
     * @return type
     */
    protected function get_mac_calculated($params)
    {
        return sha1('codTrans=' . $params['codTrans'] . 'divisa=' . $params['divisa'] . 'importo=' . $params['importo'] . $this->chiave_segreta);
    }

    /**
     * funzione che restituisce la lingua della pagina di cassa in base
     * al settaggio del modulo e alla lingua attuale di navigazione del cliente
     */
    public function get_language_payment_page()
    {
        //lingua di default poi sovrascritta da quella scelta dall'utente
        $language_id = 'ENG';
        if (isset($this->cartasi_form_language)) {
            //se scelta automatica guardo com'è settato il sito
            if ($this->cartasi_form_language == "AUTO") {
                $locale = get_locale();
                switch ($locale) {

                    case 'it_IT':
                        $language_id = 'ITA';
                        break;

                    case 'ar':
                        $language_id = 'ARA';
                        break;

                    case 'zh_CN':
                        $language_id = 'CHI';
                        break;

                    case 'ru_RU':
                        $language_id = 'RUS';
                        break;

                    case 'es_ES':
                        $language_id = 'SPA';
                        break;

                    case 'fr_FR':
                        $language_id = 'FRA';
                        break;

                    case 'de_DE':
                        $language_id = 'GER';
                        break;

                    case 'ja':
                        $language_id = 'GER';
                        break;

                    case 'pt_PT':
                        $language_id = 'POR';
                        break;

                    case 'en_GB':
                    case 'en_US':
                    default:
                        $language_id = 'ENG';
                        break;
                }
            } else {
                $language_id = $this->cartasi_form_language;
            }
        }
        return $language_id;
    }

    /**
     * restituisce la versione di woocommerce da mettere nelle note del
     * pagamento
     *
     * @return type
     */
    public function wpbo_get_woo_version_number_nexi()
    {
        // If get_plugins() isn't available, require it
        if (!function_exists('get_plugins')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        // Create the plugins folder and file variables
        $plugin_folder = get_plugins('/' . 'woocommerce');
        $plugin_file = 'woocommerce.php';

        // If the plugin version number is set, return it
        if (isset($plugin_folder[$plugin_file]['Version'])) {
            return $plugin_folder[$plugin_file]['Version'];
        } else {
            // Otherwise return null
            return null;
        }
    }

    /**
     * pagina di ringraziamento dopo aver concluso il pagamento
     *
     * @param type $order
     */
    public function wc_xpay_page_ringrazia($order)
    {
        echo wpautop(wptexturize(__("Thank you for using Nexi XPay, the order will be processed as soon as possible.", 'woocommerce-gateway-nexi-xpay')));
    }

    /**
     * RIMBORSO
     *
     * solo se l'importo rimborsato è totale
     */
    public function process_refund($order_id, $amount = null, $reaseon = "")
    {
        $order = new WC_Order($order_id);
        /* if ($order->order_total != $amount) {
          return new WP_Error('error', __('It is not possible to make a partial refund, proceed with a full refund or with a manual refund via BO Nexi.', 'woocommerce-gateway-nexi-xpay'));
          } */

        $oInfoOrderXPay = new WC_Gateway_XPay_Order_Payment_Info($order_id);
        $aDetailsOrder = $oInfoOrderXPay->GetInfoXPay();
        $codTrans = $aDetailsOrder['codTrans'];
        if (empty($codTrans)) {
            $this->log_action('warning', sprintf(__('Unable to refund order %s. Order does not have XPay capture reference.', 'woocommerce-gateway-nexi-xpay'), $order_id));
            return new WP_Error('error', sprintf(__('Unable to refund order %s. Order does not have XPay capture reference.', 'woocommerce-gateway-nexi-xpay'), $order_id));
        }
        return $this->API->refund($codTrans, $amount);
    }

    /**
     * return codTrans param for XPay gateway
     *
     * @param boolean $payment_type
     * @return string
     */
    protected function get_cod_trans($order_id, $payment_type = false)
    {
        $cod_trans = $order_id . '-' . time();
        switch ($payment_type) {
            case "PP-OC":
                $cod_trans = $cod_trans . "-" . "PP-OC";
                break;
            case "PR-OC":
                $cod_trans = $cod_trans . "-" . "PR-OC";
                break;
            case "PP":
                $cod_trans = $cod_trans . "-" . "PP";
                break;
            case "PR":
                $cod_trans = $cod_trans . "-" . "PR";
                break;
        }
        return substr($cod_trans, 0, 30);
    }

    /**
     * pagina di ricezione parametri
     */
    public function wc_xpay_page_ritorno()
    {
        global $woocommerce;
        $msg['class'] = 'error';
        $msg['message'] = __("Thank you for shopping with us. However, the transaction has been declined.", 'woocommerce-gateway-nexi-xpay');

        if (!isset($_REQUEST) || count($_REQUEST) == 0) {
            $this->log_action('warning', "wc_xpay_page_ritorno - REQUEST empty!");
            return false;
        }

        $macError = false;
        $macCalcolato = $this->get_mac_return_calculated($_REQUEST);
        if ($macCalcolato != $_REQUEST['mac']) {
            $msg['class'] = 'error';
            $msg['message'] = __("Thank you for shopping with us. However, the transaction has been declined.", 'woocommerce-gateway-nexi-xpay') . " - MAC ERROR";
            $this->log_action('warning', "wc_xpay_page_ritorno - MAC error - MAC return:" . $_REQUEST['mac']);
            $macError = true;
        }

        if (isset($_REQUEST['codTrans']) && isset($_REQUEST['esito']) && !$macError) {
            $codTrans = $_REQUEST['codTrans'];
            if (strpos($codTrans, '_') !== false) {
                $order_id = explode('_', $codTrans);
                $order_id = (int) $order_id[0];
            } else {
                $order_id = (int) $codTrans;
            }

            $esito = $_REQUEST['esito'];

            if ($order_id != '') {
                try {
                    $order = new WC_Order($order_id);

                    //SALVO INFO XPAY IN ORDINE
                    $oInfoOrderXPay = new WC_Gateway_XPay_Order_Payment_Info($order_id);
                    $oInfoOrderXPay->SetInfoXPay($_REQUEST);

                    //CONTROLLO SE CI SONO TOKEN DA SALVARE
                    $oXPayToken = new WC_Nexi_XPay_Token();
                    $oXPayToken->ctrl_request_return_payment_page($_GET);

                    $nexi_order_total = floatval($_REQUEST['importo']);
                    $order_total = ($order->get_total() * 100);

                    if ((abs($order_total - $nexi_order_total)) < 1) {
                        $this->log_action('info', "wc_xpay_page_ritorno - payment result: " . $esito);
                        if ($esito == 'OK') {
                            $msg['class'] = 'success';
                            $msg['message'] = __("Thank you for shopping with us. Your account has been charged and your transaction is successful. We will be shipping your order to you soon.", 'woocommerce-gateway-nexi-xpay');

                            //check if the order has already been notified
                            if ($order->needs_payment()) {
                                $this->log_action('info', "wc_xpay_page_ritorno - Payment complete for order #" . $order_id);
                                $order->payment_complete($codTrans);
                                $order->add_order_note(__("Thank you for shopping with us. Your account has been charged and your transaction is successful. We will be shipping your order to you soon.", 'woocommerce-gateway-nexi-xpay'));

                                //Controlla se ci sono ricorrenze e le attiva
                                $this->ctrl_return_subscription($order_id);
                            }

                            $woocommerce->cart->empty_cart();
                        } else {
                            $msg['class'] = 'error';
                            $order->update_status('failed');
                            $order->add_order_note('Failed');
                        }
                    } else {
                        $msg['class'] = 'error';
                        $this->log_action('warning', "wc_xpay_page_ritorno - order total ($order_total) != total paid ($nexi_order_total)");
                    }
                } catch (Exception $e) {
                    $msg['class'] = 'error';
                    $this->log_action('warning', "wc_xpay_page_ritorno - Exception!");
                }
            }
        }


        if (function_exists('wc_add_notice')) {
            wc_add_notice($msg['message'], $msg['class']);
        } else {
            if ($msg['class'] == 'success') {
                $woocommerce->add_message($msg['message']);
                $redirect_url = $this->get_return_url($order);
            } else {
                $woocommerce->add_error($msg['message']);
            }
            $woocommerce->set_messages();
        }

        if ($_SERVER['REQUEST_METHOD'] != "POST") {
            if ($msg['class'] == 'success') {
                $redirect_url = $this->get_return_url($order);
                wp_redirect($redirect_url);
            } else {
                wp_redirect(wc_get_page_permalink('cart'));
            }
        } else {
            if ($msg['class'] == 'success') {
                $this->log_action('info', "wc_xpay_page_ritorno - S2S Notification for transaction #" . $codTrans . " OK");
            } else {
                $this->log_action('warning', "wc_xpay_page_ritorno - S2S Notification for transaction #" . $codTrans . " KO");
                header('500 Internal Server Error', true, 500);
                exit;
            }
        }
        exit;
    }

    /**
     * sovrascirvo funzione che mostra i dettagli del metodo di pagamento in
     * fase di checkout
     */
    public function payment_fields()
    {
        if (class_exists("WC_Subscriptions_Cart") && WC_Subscriptions_Cart::cart_contains_subscription()) {
            $this->description .= "<br><br>" . __('Attention, the order for which you are making payment contains recurring payments, payment data will be stored securely by Nexi.', 'woocommerce-gateway-nexi-xpay');
        }

        if ($this->supports('tokenization') && is_checkout() && $this->abilita_modulo_oneclick == "yes") {
            $this->tokenization_script();
            echo $this->description . "<br>";
            $this->saved_payment_methods();
            //$this->form();
            $this->save_payment_method_checkbox();
        } else {
            echo $this->description;
        }
    }


    /**
     * Sovrascrivo funzione per mostrare checkbox per abilitare "oneClick"
     */
    public function save_payment_method_checkbox()
    {
        printf(
                '<p class="form-row woocommerce-SavedPaymentMethods-saveNew">
                <input id="wc-%1$s-new-payment-method" name="wc-%1$s-new-payment-method" type="checkbox" value="true" style="width:auto;" />
                <label for="wc-%1$s-new-payment-method" style="display:inline;">%2$s</label>
            </p>',
            esc_attr($this->id),
            esc_html__('Remember the payment option securely for the next time.', 'woocommerce-gateway-nexi-xpay')
        );
    }

    public function get_saved_payment_method_option_html($token)
    {
        $html = sprintf(
            '<li class="woocommerce-SavedPaymentMethods-token">
                <input id="wc-%1$s-payment-token-%2$s" type="radio" name="wc-%1$s-payment-token" value="%2$s" style="width:auto;" class="woocommerce-SavedPaymentMethods-tokenInput" %4$s />
                <label for="wc-%1$s-payment-token-%2$s">%3$s</label>
            </li>',
            esc_attr($this->id),
            esc_attr($token->get_id()),
            $this->get_token_display_name($token)/*esc_html( $token->get_display_name() )*/,
            checked($token->is_default(), true, false)
        );

        return apply_filters('woocommerce_payment_gateway_get_saved_payment_method_option_html', $html, $token, $this);
    }

    private function get_token_display_name($token)
    {
        /* translators: 1: credit card type 2: last 4 digits 3: expiry month 4: expiry year */
        $display = sprintf(
            __('%1$s ending in %2$s (expiry %3$s/%4$s)', 'woocommerce-gateway-nexi-xpay'),
            wc_get_credit_card_type_label($token->get_card_type()),
            $token->get_last4(),
            $token->get_expiry_month(),
            substr($token->get_expiry_year(), 2)
        );
        return $display;
    }
}
