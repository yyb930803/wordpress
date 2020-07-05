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

class WC_Gateway_XPay_Api
{
    public $response;
    private $alias;
    private $chiaveSegreta;
    private $gruppo;
    private $aliasPP;
    private $chiaveSegretaPP;
    private $aliasRico;
    private $chiaveSegretaRico;
    private $gruppoRico;
    private $aliasOneClick;
    private $chiaveSegretaOneClick;
    private $gruppoOneClick;
    private $url;
    private $contab;

    const XPAY_URI_REFUND = "ecomm/api/bo/storna";
    const XPAY_URI_RECURRING = "ecomm/api/recurring/pagamentoRicorrente";
    const XPAY_URI_ORDER_DETAIL = "ecomm/api/bo/situazioneOrdine";
    const XPAY_URI_ACCOUNT_INFO = "ecomm/api/profileInfo";

    public function set_env($url)
    {
        $this->url = $url;
    }

    public function set_credentials($alias, $chiaveSegreta)
    {
        $this->aliasPP = $alias;
        $this->chiaveSegretaPP = $chiaveSegreta;
    }

    public function set_credentials_rico($aliasRico, $chiaveSegretaRico, $gruppo)
    {
        $this->aliasRico = $aliasRico;
        $this->chiaveSegretaRico = $chiaveSegretaRico;
        $this->gruppoRico = $gruppo;
    }

    public function set_credentials_oneclick($aliasOneClick, $chiaveSegretaOneClick, $gruppo)
    {
        $this->aliasOneClick = $aliasOneClick;
        $this->chiaveSegretaOneClick = $chiaveSegretaOneClick;
        $this->gruppoOneClick = $gruppo;
    }

    public function set_contab($contab)
    {
        $this->contab = $contab;
    }

    private function init_credential($cod_trans)
    {
        if ($this->is_codtrans_oneclick($cod_trans)) {
            $this->alias = $this->aliasOneClick;
            $this->chiaveSegreta = $this->chiaveSegretaOneClick;
            $this->gruppo = $this->gruppoOneClick;
        } elseif ($this->is_codtrans_recurring($cod_trans)) {
            $this->alias = $this->aliasRico;
            $this->chiaveSegreta = $this->chiaveSegretaRico;
            $this->gruppo = $this->gruppoRico;
        } else {
            $this->alias = $this->aliasPP;
            $this->chiaveSegreta = $this->chiaveSegretaPP;
        }
    }

    /**
     * return if the codTrans is used for PP or PR
     *
     * @param type $cod_trans
     * @return boolean
     */
    private function is_codtrans_oneclick($cod_trans)
    {
        if (strstr($cod_trans, '_' . "PR_OC")) {
            return true;
        } elseif (strstr($cod_trans, '-' . "PR_OC")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * return if the codTrans is used for PP or PR
     *
     * @param type $cod_trans
     * @return boolean
     */
    private function is_codtrans_recurring($cod_trans)
    {
        if (strstr($cod_trans, '_' . "PR")) {
            return true;
        } elseif (strstr($cod_trans, '-' . "PR")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * API Nexi per lo storno di una transazione
     *
     * @param type $codiceTransazione
     * @param type $importo
     * @return \WP_Error|boolean
     */
    public function refund($codiceTransazione, $importo)
    {
        $this->init_credential($codiceTransazione);

        $apiKey = $this->alias; // Alias fornito da Nexi
        $chiaveSegreta = $this->chiaveSegreta; // Chiave segreta fornita da Nexi
        $importo = $importo * 100; // 5000 = 50,00 EURO (indicare la cifra in centesimi)
        $divisa = "978"; // divisa 978 indica EUR
        $timeStamp = (time()) * 1000;

        // Calcolo MAC
        $mac = sha1('apiKey=' . $apiKey . 'codiceTransazione=' . $codiceTransazione . 'divisa=' . $divisa . 'importo=' . $importo . 'timeStamp=' . $timeStamp . $chiaveSegreta);

        // Parametri
        $pay_load = array(
            // Obbligatori
            'apiKey' => $apiKey,
            'codiceTransazione' => $codiceTransazione,
            'importo' => (int)(string)$importo,
            'divisa' => $divisa,
            'timeStamp' => (string) $timeStamp,
            'mac' => $mac,
        );

        $res = $this->exec_curl($this::XPAY_URI_REFUND, $pay_load);
        if ($res !== true) {
            return $res;
        }

        $MACrisposta = sha1('esito=' . $this->response['esito'] . 'idOperazione=' . $this->response['idOperazione'] . 'timeStamp=' . $this->response['timeStamp'] . $chiaveSegreta);

        // Controllo MAC di risposta
        if ($this->response['mac'] == $MACrisposta) {

            // Controllo esito
            if ($this->response['esito'] == 'OK') {
                $this->log_action('info', sprintf(__("Refuond %s OK", 'woocommerce-gateway-nexi-xpay'), $codiceTransazione));
                return true;
            } else {
                $this->log_action('info', __($this->response['errore']['messaggio'], 'woocommerce-gateway-nexi-xpay'));
                return new WP_Error('broke', __($this->response['errore']['messaggio'], 'woocommerce-gateway-nexi-xpay'));
            }
        } else {
            //echo 'Errore nel calcolo del MAC di risposta';
            $this->log_action('info', __('Error in the calculation of the return MAC parameter', 'woocommerce-gateway-nexi-xpay'));
            return new WP_Error('broke', __('Error in the calculation of the return MAC parameter', 'woocommerce-gateway-nexi-xpay'));
        }
    }

    /**
     * API Nexi per effettuare pagamento ricorrente
     *
     * @param type $num_contratto
     * @param type $scadenza_pan
     * @param type $amount_to_charge
     * @return \WP_Error|boolean
     */
    public function recurring_payment($num_contratto, $scadenza_pan, $amount_to_charge, $newCodTrans, $note1 = null, $note2 = null, $note3 = null)
    {
        $this->init_credential($newCodTrans);
        $apiKey = $this->alias; // Alias fornito da Nexi
        $chiaveSegreta = $this->chiaveSegreta; // Chiave segreta fornita da Nexi

        $codTrans = $newCodTrans; //"PR_" . date('YmdHis'); // Codice della transazione
        $importo = $amount_to_charge * 100; // 5000 = 50,00 EURO (indicare la cifra in centesimi)
        $divisa = "978"; // divisa 978 indica EUR
        $timeStamp = (time()) * 1000;

        // Calcolo MAC
        $mac = sha1('apiKey=' . $apiKey . 'numeroContratto=' . $num_contratto . 'codiceTransazione=' . $codTrans . 'importo=' . $importo . "divisa=" . $divisa . "scadenza=" . $scadenza_pan . "timeStamp=" . $timeStamp . $chiaveSegreta);

        $pay_load = array(
            'apiKey' => $apiKey,
            'numeroContratto' => $num_contratto,
            'codiceTransazione' => $codTrans,
            'importo' => (int)(string)$importo,
            'divisa' => $divisa,
            'scadenza' => $scadenza_pan,
            'codiceGruppo' => $this->gruppo,
            'timeStamp' => (string) $timeStamp,
            'mac' => $mac,
            'parametriAggiuntivi' => array(
                'Note1' => $note1,
                'Note2' => $note2,
                'Note3' => $note3,
                'TCONTAB' => $this->contab,
            )
        );
        $res = $this->exec_curl($this::XPAY_URI_RECURRING, $pay_load);
        if ($res !== true) {
            return $res;
        }
        if ($this->response['esito'] == "OK") { // Transazine andata a buon fine
            // Calcolo MAC con i parametri di ritorno
            $macCalculated = sha1('esito=' . $this->response['esito'] . 'idOperazione=' .
                    $this->response['idOperazione'] . 'timeStamp=' .
                    $this->response['timeStamp'] . $chiaveSegreta);

            if ($macCalculated != $this->response['mac']) {
                $this->log_action('info', __('Error in the calculation of the return MAC parameter', 'woocommerce-gateway-nexi-xpay'));
                return new WP_Error('broke', __('Error in the calculation of the return MAC parameter', 'woocommerce-gateway-nexi-xpay'));
            }
            $this->log_action('info', sprintf(__("The transaction %s OK", 'woocommerce-gateway-nexi-xpay'), $codTrans));
            return true;
        } else { // Transazione rifiutata
            $this->log_action('info', sprintf(__("The transaction %s was rejected. Error detail: %s. payLoad: %s", 'woocommerce-gateway-nexi-xpay'), $codTrans, $this->response['errore']['messaggio'], json_encode($pay_load)));
            return new WP_Error('broke', sprintf(__("The transaction %s was rejected. Error detail: %s", 'woocommerce-gateway-nexi-xpay'), $codTrans, $this->response['errore']['messaggio']));
        }
    }

    public function order_detail($codiceTransazione, $recurring = false)
    {
        $this->init_credential($codiceTransazione);

        $apiKey = $this->alias; // Alias fornito da Nexi
        $chiaveSegreta = $this->chiaveSegreta; // Chiave segreta fornita da Nexi

        $timeStamp = (time()) * 1000;

        // Calcolo MAC
        $mac = sha1('apiKey=' . $apiKey . 'codiceTransazione=' . $codiceTransazione . 'timeStamp=' . $timeStamp . $chiaveSegreta);

        // Parametri
        $pay_load = array(
            'apiKey' => $apiKey,
            'codiceTransazione' => $codiceTransazione,
            'timeStamp' => $timeStamp,
            'mac' => $mac
        );

        $res = $this->exec_curl($this::XPAY_URI_ORDER_DETAIL, $pay_load);
        if ($res !== true) {
            return $res;
        }

        $MACrisposta = sha1('esito=' . $this->response['esito'] . 'idOperazione=' .
                $this->response['idOperazione'] . 'timeStamp=' .
                $this->response['timeStamp'] . $chiaveSegreta);

        // Controllo MAC di risposta
        if ($this->response['mac'] == $MACrisposta) {

            // Controllo esito
            if ($this->response['esito'] == 'OK') {
                $this->log_action('info', __('Transaction Result: OK', 'woocommerce-gateway-nexi-xpay'));
                return true;
            } else {
                $this->log_action('info', sprintf(__("Detail order %s operation was rejected. Error detail: %s", 'woocommerce-gateway-nexi-xpay'), $codTrans, $this->response['errore']['messaggio']));
                return new WP_Error('broke', sprintf(__("Detail order %s operation was rejected. Error detail: %s", 'woocommerce-gateway-nexi-xpay'), $codTrans, $this->response['errore']['messaggio']));
            }
        } else {
            $this->log_action('info', __('Error in the calculation of the return MAC parameter', 'woocommerce-gateway-nexi-xpay'));
            return new WP_Error('broke', __('Error in the calculation of the return MAC parameter', 'woocommerce-gateway-nexi-xpay'));
        }
    }

    public function get_available_methods($platformVers, $pluginVers)
    {
        $apiKey = $this->aliasPP; // Alias fornito da Nexi
        $chiaveSegreta = $this->chiaveSegretaPP; // Chiave segreta fornita da Nexi

        $timeStamp = (time()) * 1000;

        // Calcolo MAC
        $mac = sha1('apiKey=' . $apiKey . 'timeStamp=' . $timeStamp . $chiaveSegreta);

        // Parametri
        $pay_load = array(
            'apiKey' => $apiKey,
            'timeStamp' => $timeStamp,
            'mac' => $mac,
            'platform' => 'woocommerce',
            'platformVers' => $platformVers,
            'pluginVers' => $pluginVers
        );
        $res = $this->exec_curl($this::XPAY_URI_ACCOUNT_INFO, $pay_load);
        if ($res !== true) {
            return $res;
        }
        $MACrisposta = sha1('esito=' . $this->response['esito'] . 'idOperazione=' .
                $this->response['idOperazione'] . 'timeStamp=' .
                $this->response['timeStamp'] . $chiaveSegreta);

        // Controllo MAC di risposta
        if ($this->response['mac'] == $MACrisposta) {

        // Controllo esito
            if ($this->response['esito'] == 'OK') {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function exec_curl($request_uri, $pay_load, $url_complete = false)
    {
        $connection = curl_init();

        if ($url_complete) {
            $url = $request_uri;
        } else {
            $url = $this->url . $request_uri;
        }

        if ($connection) {
            curl_setopt_array($connection, array(
                CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => json_encode($pay_load),
                CURLOPT_RETURNTRANSFER => 1,
                CURLINFO_HEADER_OUT => true,
                CURLOPT_SSL_VERIFYPEER => 0
            ));

            $response = curl_exec($connection);

            if ($response == false) {
                return new WP_Error('broke', sprintf(__('CURL exec error: %s', 'woocommerce-gateway-nexi-xpay'), curl_error($connection)));
            }

            curl_close($connection);

            $json = json_decode($response, true);

            if (is_array($json) && json_last_error() === JSON_ERROR_NONE) {
                $this->response = $json;
                return true;
            } else {
                return new WP_Error('broke', __('JSON error', 'woocommerce-gateway-nexi-xpay'));
            }
        } else {
            return new WP_Error('broke', __('Can\'t connect!', 'woocommerce-gateway-nexi-xpay'));
        }
    }
    public function log_action($log_type, $message)
    {
        $logger = wc_get_logger();
        $context = array('source' => 'woocommerce-gateway-nexi-xpay');
        $logger->log($log_type, $message, $context);
    }
}
