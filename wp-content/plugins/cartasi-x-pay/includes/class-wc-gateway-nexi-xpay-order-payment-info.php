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

class WC_Gateway_XPay_Order_Payment_Info
{
    private $order_id;

    public function __construct($order_id)
    {
        $this->order_id = $order_id;
    }

    public function log_action($log_type, $message)
    {
        $logger = wc_get_logger();
        $context = array('source' => 'woocommerce-gateway-nexi-xpay');
        $logger->log($log_type, $message, $context);
    }

    public function SetInfoXPay($request)
    {
        $aDataToSave = array(
            "alias",
            "numeromerchant",
            "importo",
            "data",
            "divisa",
            "codTrans",
            "orario",
            "esito",
            "codAut",
            "brand",
            "tipoTransazione",
            "mail",
            "languageId",
            "descrizione",
            "TCONTAB",
            "codiceEsito",
            "messaggio",
            "tipoProdotto",
            "nazionalita",
            "num_contratto",
            "scadenza_pan",
            "pan"
        );

        $info = array();
        foreach ($request as $key => $value) {
            if (in_array($key, $aDataToSave)) {
                if ($key == "messaggio") {
                    $info[$key] = str_replace("\\'", "", $value);
                } else {
                    $info[$key] = $value;
                }
            }
        }

        $info['_data_pagamento'] = "";
        if (isset($info['nome'])) {
            $info['_cliente'] = $info['nome'] . " ";
        }
        if (isset($info['cognome'])) {
            $info['_cliente'] .= $info['cognome'];
        }
        $info['_importo'] = number_format(($info['importo'] / 100), 2, ",", ".");

        if (isset($info['scadenza_pan']) && $info['scadenza_pan'] != "") {
            $date = DateTime::createFromFormat('Ym', $info['scadenza_pan']);
            $info['_scadenza_pan'] = $date->format("m/Y");
        }

        $date = DateTime::createFromFormat('YmdHis', $info['data'] . $info['orario']);
        if ($date == false) {
            $date = DateTime::createFromFormat('d/m/Y', $info['data']);
            if ($date) {
                $info['_data_pagamento'] = $date->format("d/m/Y");
            }
        } else {
            if ($date) {
                $info['_data_pagamento'] = $date->format("d/m/Y H:i:s");
            }
        }

        update_post_meta($this->order_id, 'xpay_details_order', json_encode(wc_clean($info)));
        update_post_meta($this->order_id, 'xpay_cod_trans', $info['codTrans']);
    }

    public function GetInfoXPay($detailField = null)
    {
        $order = wc_get_order($this->order_id);
        if (!$order) {
            return;
        }

        $jDetailsOrder = get_post_meta($this->order_id, 'xpay_details_order', true);
        $aDetailsOrder = json_decode($jDetailsOrder, true);

        if ($aDetailsOrder["codTrans"] == "") {
            $aDetailsOrder["codTrans"] = get_post_meta($this->order_id, 'xpay_cod_trans', true);
        }

        if ($detailField) {
            return $aDetailsOrder[$detailField];
        } else {
            return $aDetailsOrder;
        }
    }
}
