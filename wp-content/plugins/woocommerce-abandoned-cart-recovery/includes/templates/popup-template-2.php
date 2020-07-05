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

<div id="wacv-modal" class="wacv-modal-get-email template-2" style="display: none;">
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
				<div
					class="wacv-email-invalid-notice"><?php echo esc_html( stripslashes( $param['invalid_email'] ) ) ?></div>
				<div class="wacv-exe-group">
					<input type="text" class="wacv-popup-input-email" placeholder="Email">
					<button type="button" class="wacv-get-email-btn wacv-add-to-cart-btn wacv-btn-first">
						<?php echo esc_html( stripslashes( $param['add_to_cart_btn'] ) ) ?>
					</button>
				</div>
				<div>
					<?php if ( $param['app_id'] && $param['app_secret'] && ( $param['single_page'] || $param['shop_page'] || $param['cart_page'] || $param['front_page'] ) ) {
						$html = '<div class="fb-messenger-checkbox-container"></div>';
						echo $html;
					} ?>
				</div>
				<?php do_action( 'wacv_popup_footer' ); ?>
			</div>
		</div>
	</div>
</div>