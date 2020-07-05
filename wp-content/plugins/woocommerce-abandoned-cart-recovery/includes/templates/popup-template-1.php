<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 26-06-19
 * Time: 2:27 PM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="wacv-modal" class="wacv-modal-get-email template-1" style="display: none;">
	<div class="wacv-modal-wrapper">
		<div class="wacv-modal-content">
			<?php if ( ! $param['info_require'] ) : ?>
				<span class="wacv-close-popup">&times;</span>
			<?php endif; ?>
			<div class="wacv-container">
				<div class="wacv-get-email-title">
					<?php echo esc_html( stripslashes( $param['title_popup'] ) ) ?>
				</div>
				<div class="wacv-get-email-sub-title">
					<?php echo esc_html( stripslashes( $param['sub_title_popup'] ) ) ?>
				</div>

				<?php if ( $param['email_field'] ) { ?>
					<div class="wacv-email-invalid-notice" style="color:red;">
						<?php echo esc_html( stripslashes( $param['invalid_email'] ) ) ?>
					</div>
					<div>
						<input type="text" class="wacv-popup-input-email" placeholder="Email">
					</div>
				<?php } ?>

				<?php if ( $param['phone_field'] ) { ?>
					<div class="wacv-phone-number-invalid-notice" style="color:red;">
						<?php echo esc_html( stripslashes( $param['invalid_phone'] ) ) ?>
					</div>
					<div>
						<input type="text" class="wacv-popup-input-phone-number"
						       placeholder="Phone number: +1234567890">
					</div>
				<?php } ?>

				<div>
					<?php if ( $param['app_id'] && $param['app_secret'] && $param['user_token'] && ( $param['single_page'] || $param['shop_page'] || $param['cart_page'] || $param['front_page'] ) ) {
						echo '<div class="fb-messenger-checkbox-container"></div>';
					} ?>
				</div>

				<div class="wacv-get-email-btn-group">
					<button type="button" class="wacv-get-email-btn wacv-add-to-cart-btn wacv-btn-first">
						<?php echo esc_html( stripslashes( $param['add_to_cart_btn'] ) ) ?>
					</button>
				</div>
				<?php do_action( 'wacv_popup_footer' ); ?>
			</div>
		</div>
	</div>
</div>
