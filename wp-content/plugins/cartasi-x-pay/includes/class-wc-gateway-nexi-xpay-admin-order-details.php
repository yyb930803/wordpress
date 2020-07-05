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

class WC_Gateway_XPay_Admin_Order_Details extends WC_Gateway_XPay
{

    /**
     * da definire
     */
    public function init_form_fields()
    {
        return array();
    }

    /**
     * hook metabox visibility
     */
    public function set_meta_box_xpay()
    {
        add_action('add_meta_boxes', array($this, 'add_meta_box_details_payment_xpay'));
        add_action('add_meta_boxes', array($this, 'remove_meta_box_custom_fields'));
    }

    /**
     * add metabox with payment info where payment method is XPay
     *
     * @return type
     */
    public function add_meta_box_details_payment_xpay()
    {
        $order = wc_get_order(get_post_field("ID"));
        if (!$order) {
            return;
        }

        if (wc_nxp_get_order_prop($order, 'payment_method') === 'xpay') {
            add_meta_box('xpay-subscription-box', __('XPay payment details ', 'woocommerce-gateway-nexi-xpay'), array($this, 'details_payment_xpay'), 'shop_order', 'normal', 'high');
        }
    }

    /**
     * Get info XPay
     *
     * @return type
     */
    public function details_payment_xpay()
    {
        $oInfoOrderXPay = new WC_Gateway_XPay_Order_Payment_Info(get_post_field("ID"));
        $aDetailsOrder = $oInfoOrderXPay->GetInfoXPay();
        if (isset($aDetailsOrder['codTrans']) && $aDetailsOrder['codTrans'] != '') {
            $params = array('_cliente', 'mail', 'nazionalita', 'pan', '_scadenza_pan', 'messaggio', 'num_contratto');
            foreach ($params as $param) {
                if (!isset($aDetailsOrder[$param])) {
                    $aDetailsOrder[$param] = null;
                }
            }

            $codTrans = $aDetailsOrder['codTrans'];
            if ($codTrans) {
                $this->API->set_credentials($this->alias, $this->chiave_segreta);
                $this->API->set_credentials_rico($this->alias_rico, $this->chiave_segreta_rico, $this->gruppo_rico);
                $this->API->set_credentials_oneclick($this->alias_oneclick, $this->chiave_segreta_oneclick, $this->gruppo_oneclick);
                if ($this->API->order_detail($codTrans) === true) {
                    $aInfoBO = $this->API->response;
                }
            }
            $path = plugin_dir_path(__DIR__);

            include_once $path . 'templates/' . __FUNCTION__ . ".php";
        }
    }

    /**
     * Remove default WC box postcustom fields
     */
    public function remove_meta_box_custom_fields()
    {
        remove_meta_box('postcustom', 'shop_order', 'normal');
    }
}

new WC_Gateway_XPay_Admin_Order_Details();
