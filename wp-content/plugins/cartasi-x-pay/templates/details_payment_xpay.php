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

if (isset($aDetailsOrder['codTrans']) && $aDetailsOrder['codTrans'] != '') {
    echo '<link href="' . plugins_url('assets/css/xpay.css', plugin_dir_path(__FILE__)) . '" rel="stylesheet" type="text/css">'; ?>

<div id="order_xpay_details" class="panel">

    <div class="order_data_column_container">

        <div class="order_data_column">
            <h3><?php echo __("Card Holder", 'woocommerce-gateway-nexi-xpay') ?></h3>
            <p>
                <?php if (isset($aDetailsOrder['_cliente']) && $aDetailsOrder['_cliente'] != '') {
        ?>
                <strong><?php echo __("Name: ", 'woocommerce-gateway-nexi-xpay') ?></strong> <?php echo $aDetailsOrder['_cliente'] ?> <br>
                <?php
    }; ?>
                <?php if (isset($aDetailsOrder['mail']) && $aDetailsOrder['mail'] != '') {
        ?>
                <strong>Mail:</strong> <?php echo $aDetailsOrder['mail'] ?> <br>
                <?php
    }; ?>
            </p>
        </div>

        <div class="order_data_column">
            <h3><?php echo __("Card Detail", 'woocommerce-gateway-nexi-xpay') ?></h3>
            <p>
              <?php if (isset($aDetailsOrder['brand']) && $aDetailsOrder['brand'] != '') {
        ?>
                <strong><?php echo __("Card: ", 'woocommerce-gateway-nexi-xpay') ?></strong> <?php echo $aDetailsOrder['brand'] ?> <br>
              <?php
    };
    if (isset($aDetailsOrder['nazionalita']) && $aDetailsOrder['nazionalita'] != '') {
        ?>
                <strong><?php echo __("Nationality: ", 'woocommerce-gateway-nexi-xpay') ?></strong> <?php echo $aDetailsOrder['nazionalita'] ?> <br>
                <?php
    };
    if (isset($aDetailsOrder['pan']) && $aDetailsOrder['pan'] != '') {
        ?>
                <strong><?php echo __("Card Pan: ", 'woocommerce-gateway-nexi-xpay') ?></strong> <?php echo $aDetailsOrder['pan'] ?> <br>
                <?php
    };
    if (isset($aDetailsOrder['_scadenza_pan']) && $aDetailsOrder['_scadenza_pan'] != '') {
        ?>
                <strong><?php echo  __("Expiry date: ", 'woocommerce-gateway-nexi-xpay') ?></strong> <?php echo $aDetailsOrder['_scadenza_pan'] ?> <br>
                <?php
    }; ?>
            </p>
        </div>

        <div class="order_data_column">
            <h3><?php echo __("Transaction Detail", 'woocommerce-gateway-nexi-xpay') ?></h3>
            <p>
                <strong><?php if (isset($aDetailsOrder['_data_pagamento']) && $aDetailsOrder['_data_pagamento'] != '') {
        echo __("Date: ", 'woocommerce-gateway-nexi-xpay') ?></strong> <?php echo $aDetailsOrder['_data_pagamento']?> <?php
    }; ?> <br>
                <strong><?php if (isset($aDetailsOrder['_importo']) && $aDetailsOrder['_importo'] != '') {
        echo __("Amount: ", 'woocommerce-gateway-nexi-xpay') ?></strong> <?php echo $aDetailsOrder['_importo']?> <?php
    }; ?><br>
                <strong><?php if (isset($aDetailsOrder['codTrans']) && $aDetailsOrder['codTrans'] != '') {
        echo __("Transaction code: ", 'woocommerce-gateway-nexi-xpay') ?></strong> <?php echo $aDetailsOrder['codTrans']?> <?php
    }; ?><br>
                <strong><?php if (isset($aDetailsOrder['esito']) && $aDetailsOrder['esito'] != '') {
        echo __("Result: ", 'woocommerce-gateway-nexi-xpay') ?></strong> <?php echo $aDetailsOrder['esito']?> <?php
    }; ?><br>
                <strong><?php if (isset($aDetailsOrder['messaggio']) && $aDetailsOrder['messaggio'] != '') {
        echo __("Message: ", 'woocommerce-gateway-nexi-xpay') ?></strong> <?php echo $aDetailsOrder['messaggio'] . '<br>'?> <?php
    }; ?>
                <strong><?php if (isset($aDetailsOrder['num_contratto']) && $aDetailsOrder['num_contratto'] != '') {
        ?>
                <?php echo __("# Contract: ", 'woocommerce-gateway-nexi-xpay') ?></strong> <?php echo $aDetailsOrder['num_contratto'] ?> <br>
                <?php
    } ?>
              <strong>  <?php if ($aInfoBO['report'][0]['dettaglio'][0]['stato']) {
        ?>
                <?php echo __("Status: ", 'woocommerce-gateway-nexi-xpay') ?> </strong><?php echo $aInfoBO['report'][0]['dettaglio'][0]['stato'] ?> <br>
                <?php
    } ?>
            </p>
        </div>
    </div>
    <?php if (count($aInfoBO['report'][0]['dettaglio'][0]['operazioni']) > 0) {
        ?>
        <h3><?php echo __("Accounting Operations", 'woocommerce-gateway-nexi-xpay') ?></h3>
        <div class="woocommerce_subscriptions_related_orders">
            <table>
                <thead>
                    <tr>
                        <th><?php echo __("Op. Kind", 'woocommerce-gateway-nexi-xpay') ?></th>
                        <th><?php echo __("Amount", 'woocommerce-gateway-nexi-xpay') ?></th>
                        <th><?php echo __("Currency", 'woocommerce-gateway-nexi-xpay') ?></th>
                        <th><?php echo __("Status", 'woocommerce-gateway-nexi-xpay') ?></th>
                        <th><?php echo __("Date", 'woocommerce-gateway-nexi-xpay') ?></th>
                        <th><?php echo __("User", 'woocommerce-gateway-nexi-xpay') ?></th>

                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($aInfoBO['report'][0]['dettaglio'][0]['operazioni'] as $operazione) {
            ?>
                        <tr>
                            <td><?php echo $operazione['tipoOperazione'] ?></td>
                            <td><?php echo number_format(($operazione['importo'] / 100), 2, ",", ".") ?></td>
                            <td><?php echo $operazione['divisa'] ?></td>
                            <td><?php echo $operazione['stato'] ?></td>
                            <td><?php $oData = new DateTime($operazione['dataOperazione']);
            echo $oData->format("d/m/Y H:i:s") ?></td>
                            <td><?php echo $operazione['utente'] ?></td>
                        </tr>

                    <?php
        } ?>
                </tbody>
            </table>
        </div>
    <?php
    } ?>

</div>
<?php
}; ?>
