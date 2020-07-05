<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$loginAjax = (bool) $prefix;
$prefix = $prefix ? 'nasa_' : '';

$nasa_keyUserName = $prefix . 'username';
$nasa_keyPass = $prefix . 'password';
$nasa_keyEmail = $prefix . 'email';
$nasa_keyLogin = $prefix . 'login';
$nasa_keyRememberme = $prefix . 'rememberme';

$nasa_keyRegUsername = $prefix . 'reg_username';
$nasa_keyRegEmail = $prefix . 'reg_email';
$nasa_keyRegPass = $prefix . 'reg_password';
$nasa_keyRegEmail2 = $prefix . 'email_2';
$nasa_keyReg = $prefix . 'register';

$nasa_register = get_option('woocommerce_enable_myaccount_registration') == 'yes' ? true : false;
?>

<div class="row" id="<?php echo esc_attr($prefix); ?>customer_login">
    <div class="large-12 columns <?php echo esc_attr($prefix); ?>login-form">
        <h2 class="nasa-form-title"><?php esc_html_e('Great to have you back !', 'elessi-theme'); ?></h2>
        <form method="post" class="login">
            <?php do_action('woocommerce_login_form_start'); ?>

            <p class="form-row form-row-wide">
                <span>
                    <label for="<?php echo esc_attr($nasa_keyUserName); ?>" class="inline-block left rtl-right">
                        <?php esc_html_e('Username or email', 'elessi-theme'); ?> <span class="required">*</span>
                    </label>

                    <label for="<?php echo esc_attr($nasa_keyRememberme); ?>" class="inline-block right rtl-left">
                        <input name="<?php echo esc_attr($nasa_keyRememberme); ?>" type="checkbox" id="<?php echo esc_attr($nasa_keyRememberme); ?>" value="forever" /> <?php esc_html_e('Remember', 'elessi-theme'); ?>
                    </label>
                </span>
                <input type="text" class="input-text" name="<?php echo esc_attr($nasa_keyUserName); ?>" id="<?php echo esc_attr($nasa_keyUserName); ?>" />
            </p>
            <p class="form-row form-row-wide">
                <span>
                    <label for="<?php echo esc_attr($nasa_keyPass); ?>" class="inline-block left rtl-right">
                        <?php esc_html_e('Password', 'elessi-theme'); ?> <span class="required">*</span>
                    </label>
                    <a class="lost_password inline-block right rtl-left" href="<?php echo esc_url(wc_lostpassword_url()); ?>"><?php esc_html_e('Lost?', 'elessi-theme'); ?></a>
                </span>
                
                <input class="input-text" type="password" name="<?php echo esc_attr($nasa_keyPass); ?>" id="<?php echo esc_attr($nasa_keyPass); ?>" />
            </p>

            <?php do_action('woocommerce_login_form'); ?>

            <p class="form-row row-submit">
                <input type="hidden" id="woocommerce-login-wpnonce" name="_wpnonce" value="<?php echo wp_create_nonce('woocommerce-login'); ?>" />
                <?php echo wp_referer_field(true); ?>
                <input type="submit" class="button" name="<?php echo esc_attr($nasa_keyLogin); ?>" value="<?php esc_attr_e('SIGN IN TO YOUR ACCOUNT', 'elessi-theme'); ?>" />
            </p>

            <?php do_action('woocommerce_login_form_end'); ?>
        </form>
        
            <p class="nasa-switch-form">
                <?php esc_html_e('New here? ', 'elessi-theme'); ?>
                <a class="nasa-switch-register" href="javascript:void(0);">
                    <?php esc_html_e('Create an account', 'elessi-theme'); ?>
                </a>
            </p>
    </div>

        <div class="large-12 columns <?php echo esc_attr($prefix); ?>register-form">

            <h2 class="nasa-form-title"><?php esc_html_e('Great to see you here !', 'elessi-theme'); ?></h2>
            <form method="post" class="register">
                
                <?php do_action('woocommerce_register_form_start'); ?>
                <?php if (get_option('woocommerce_registration_generate_username') === 'no') : ?>

                    <p class="form-row form-row-wide">
                        <label for="<?php echo esc_attr($nasa_keyRegUsername); ?>">
                            <?php esc_html_e('Username', 'elessi-theme'); ?> <span class="required">*</span>
                        </label>
                        <input type="text" class="input-text" name="<?php echo esc_attr($nasa_keyUserName); ?>" id="<?php echo esc_attr($nasa_keyRegUsername); ?>" value="<?php echo !empty($_POST[$nasa_keyUserName]) ? esc_attr($_POST[$nasa_keyUserName]) : ''; ?>" />
                    </p>

                <?php endif; ?>

                <p class="form-row form-row-wide">
                    <label for="<?php echo esc_attr($nasa_keyRegEmail); ?>" class="left rtl-right">
                        <?php esc_html_e('Email address', 'elessi-theme'); ?> <span class="required">*</span>
                    </label>
                    
                    <input type="email" class="input-text" name="<?php echo esc_attr($nasa_keyEmail); ?>" id="<?php echo esc_attr($nasa_keyRegEmail); ?>" value="<?php echo !empty($_POST[$nasa_keyEmail]) ? esc_attr($_POST[$nasa_keyEmail]) : ''; ?>" />
                </p>

                <p class="form-row form-row-wide">
                    <label for="<?php echo esc_attr($nasa_keyRegPass); ?>" class="left rtl-right">
                        <?php esc_html_e('Password', 'elessi-theme'); ?> <span class="required">*</span>
                    </label>
                    
                    <input type="password" class="input-text" name="<?php echo esc_attr($nasa_keyPass); ?>" id="<?php echo esc_attr($nasa_keyRegPass); ?>" value="<?php echo !empty($_POST['password']) ? esc_attr($_POST['password']) : ''; ?>" />
                </p>

                <div style="left:-999em; position:absolute;">
                    <label for="trap"><?php esc_html_e('Anti-spam', 'elessi-theme'); ?></label>
                    <input type="text" name="<?php echo esc_attr($nasa_keyRegEmail2); ?>" id="trap" tabindex="-1" />
                </div>

                <?php do_action('woocommerce_register_form'); ?>
                <?php do_action('register_form'); ?>

                <p class="form-row">
                    <input type="hidden" id="woocommerce-register-wpnonce" name="_wpnonce" value="<?php echo wp_create_nonce('woocommerce-register'); ?>" />
                    <?php echo wp_referer_field(false); ?>
                    <input type="submit" class="button" name="<?php echo esc_attr($nasa_keyReg); ?>" value="<?php esc_attr_e('SETUP YOUR ACCOUNT', 'elessi-theme'); ?>" />
                </p>

                <?php do_action('woocommerce_register_form_end'); ?>

            </form>
            
            <p class="nasa-switch-form">
                <?php esc_html_e('Already got an account? ', 'elessi-theme'); ?>
                <a class="nasa-switch-login" href="javascript:void(0);">
                    <?php esc_html_e('Sign in here', 'elessi-theme'); ?>
                </a>
            </p>
        </div>
</div>