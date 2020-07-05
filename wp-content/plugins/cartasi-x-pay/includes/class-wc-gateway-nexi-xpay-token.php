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

class WC_Nexi_XPay_Token extends WC_Payment_Token_CC
{

    /**
     * Controlla che nella request in ritorno dalla pagina di cassa
     * ci sia qualche token da salvare
     */
    public function ctrl_request_return_payment_page($request)
    {
        if (isset($request['brand']) && $request['brand'] != 'PAYPAL') {
            if ((isset($request['num_contratto']) && $request['num_contratto']) && strpos($request['codTrans'], "-" . "PP-OC")
                && $request['esito'] == 'OK') {
                $this->set_token($request['num_contratto']);
                $this->set_gateway_id('xpay');
                $this->set_card_type($request['brand']);
                $this->set_last4(substr($request['pan'], -4));
                $this->set_expiry_month(substr($request['scadenza_pan'], -2));
                $this->set_expiry_year(substr($request['scadenza_pan'], 0, 4));
                $this->set_user_id(get_current_user_id());
                $this->save();
            }
        }
    }

    /**
     *
     */
    public function get_token_nexi($token_id)
    {
        $token = WC_Payment_Tokens::get($token_id);
        // Token user ID does not match the current user... bail out of payment processing.
        if ($token != false || $token->get_user_id() !== get_current_user_id()) {
            // Optionally display a notice with `wc_add_notice`
            return $token;
        }
        return false;
    }
}
