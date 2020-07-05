<?php
/**
 * Wishlist page template
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Wishlist
 */
if (!defined('YITH_WCWL_PREMIUM')) {
    global $woocommerce, $nasa_opt;
    
    $countItems = count($wishlist_items);
    $classTitle = 'nasa-tit-wishlist nasa-sidebar-tit';
    $classTitle .= $countItems <= 0 ? ' text-center' : '';

    remove_filter('woocommerce_loop_add_to_cart_link', array('YITH_WCWL_UI', 'alter_add_to_cart_button'));
    ?>
    

    <?php do_action('yith_wcwl_before_wishlist_form', $wishlist_meta); ?>

    <h3 class="<?php echo esc_attr($classTitle); ?>">
        <?php echo esc_html__('Wishlist', 'elessi-theme'); ?>
    </h3>
    
   <form id="yith-wcwl-form" action="<?php echo esc_attr( $form_action ); ?>" method="post" class="woocommerce yith-wcwl-form wishlist-fragment" data-fragment-options="<?php echo esc_attr( json_encode( $fragment_options ) ); ?>">
        
        <?php do_action('yith_wcwl_before_wishlist', $wishlist_meta); ?>
        
        <!-- WISHLIST TABLE -->
        <table class="shop_table wishlist_table" data-pagination="<?php echo esc_attr($pagination); ?>" data-per-page="<?php echo esc_attr($per_page); ?>" data-page="<?php echo esc_attr($current_page); ?>" data-id="<?php echo $wishlist_id; ?>" data-token="<?php echo $wishlist_token ?>">
            <tbody>
                <?php if ($countItems > 0) :
                    foreach ($wishlist_items as $item) :
                        global $product;
                        $product = wc_get_product($item['prod_id']);

                        if ($product !== false && $product->exists()) :
                            $productId = $product->get_id();
                            ?>
                            <tr class="nasa-tr-wishlist-item" id="yith-wcwl-row-<?php echo (int) $productId; ?>" data-row-id="<?php echo (int) $productId; ?>">
                                <td class="product-wishlist-info">
                                    <div class="wishlist-item-warper nasa-relative">
                                        <div class="row wishlist-item">
                                            <div class="image-wishlist large-3 small-3 columns padding-left-0">
                                                <a href="<?php echo esc_url(get_permalink(apply_filters('woocommerce_in_cart_product', $productId))); ?>">
                                                    <?php echo ($product->get_image('full')); ?>
                                                </a>
                                            </div>

                                            <div class="info-wishlist large-8 small-7 columns padding-right-0">
                                                <div class="row">
                                                    <div class="large-12 columns nasa-wishlist-title">
                                                        <a href="<?php echo esc_url(get_permalink(apply_filters('woocommerce_in_cart_product', $productId))); ?>">
                                                            <?php echo apply_filters('woocommerce_in_cartproduct_obj_title', $product->get_name(), $product); ?>
                                                        </a>
                                                    </div>

                                                    <div class="wishlist-price large-12 columns">
                                                        <?php
                                                        if ($show_price) :?>
                                                            <span class="price"><?php echo ($product->get_price_html()); ?></span>
                                                        <?php
                                                        endif;

                                                        if ($show_stock_status) :

                                                            $availability = $product->get_availability();
                                                            $stock_status = $availability['class'];

                                                            if ($stock_status == 'out-of-stock') :
                                                                $stock_status = "Out";
                                                                echo '<span class="wishlist-out-of-stock">' . esc_html__(' - Esaurito', 'elessi-theme') . '</span>';
                                                            else :
                                                                $stock_status = "In";
                                                                echo '<span class="wishlist-in-stock">' . esc_html__(' - Disponibile', 'elessi-theme') . '</span>';
                                                            endif;

                                                        endif; ?>
                                                    </div>

							<?php do_action( 'yith_wcwl_table_before_product_cart', $item, $wishlist ); ?>

							<!-- Date added -->
							<?php
							if ( $show_dateadded && $item->get_date_added() ) :
								// translators: date added label: 1 date added.
								echo '<span class="dateadded">' . esc_html( sprintf( __( 'Added on: %s', 'yith-woocommerce-wishlist' ), $item->get_date_added_formatted() ) ) . '</span>';
							endif;
							?>

							<?php do_action( 'yith_wcwl_table_product_before_add_to_cart', $item, $wishlist ); ?>

							<!-- Add to cart button -->
							<?php $show_add_to_cart = apply_filters( 'yith_wcwl_table_product_show_add_to_cart', $show_add_to_cart, $item, $wishlist ); ?>
							<?php if ( $show_add_to_cart && isset( $stock_status ) && 'out-of-stock' !== $stock_status ) : ?>
								<?php woocommerce_template_loop_add_to_cart( array( 'quantity' => $show_quantity ? $item->get_quantity() : 1 ) ); ?>
							<?php endif ?>

							<?php do_action( 'yith_wcwl_table_product_after_add_to_cart', $item, $wishlist ); ?>

							<!-- Change wishlist -->
							<?php $move_to_another_wishlist = apply_filters( 'yith_wcwl_table_product_move_to_another_wishlist', $move_to_another_wishlist, $item, $wishlist ); ?>
							<?php if ( $move_to_another_wishlist && $available_multi_wishlist && count( $users_wishlists ) > 1 ) : ?>
								<?php if ( 'select' === $move_to_another_wishlist_type ) : ?>
									<select class="change-wishlist selectBox">
										<option value=""><?php esc_html_e( 'Move', 'yith-woocommerce-wishlist' ); ?></option>
										<?php
										foreach ( $users_wishlists as $wl ) :
											// phpcs:ignore Generic.Commenting.DocComment
											/**
											 * @var $wl \YITH_WCWL_Wishlist
											 */
											if ( $wl->get_token() === $wishlist_token ) {
												continue;
											}
											?>
											<option value="<?php echo esc_attr( $wl->get_token() ); ?>">
												<?php echo sprintf( '%s - %s', esc_html( $wl->get_formatted_name() ), esc_html( $wl->get_formatted_privacy() ) ); ?>
											</option>
										<?php
										endforeach;
										?>
									</select>
								<?php else : ?>
									<a href="#move_to_another_wishlist" class="move-to-another-wishlist-button" data-rel="prettyPhoto[move_to_another_wishlist]">
										<?php echo esc_html( apply_filters( 'yith_wcwl_move_to_another_list_label', __( 'Move to another list &rsaquo;', 'yith-woocommerce-wishlist' ) ) ); ?>
									</a>
								<?php endif; ?>

								<?php do_action( 'yith_wcwl_table_product_after_move_to_another_wishlist', $item, $wishlist ); ?>

							<?php endif; ?>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
									
									
                                <?php if ($is_user_owner) : ?>
                                    <td class="product-remove">
							<div>
								<a href="<?php echo esc_url( add_query_arg( 'remove_from_wishlist', $item->get_product_id() ) ); ?>" class="remove remove_from_wishlist remove_from_wishlist" title="<?php echo esc_html( apply_filters( 'yith_wcwl_remove_product_wishlist_message_title', __( 'Remove this product', 'yith-woocommerce-wishlist' ) ) ); ?>" >&times;</a>
							</div>
						</td>
                                <?php endif; ?>
                            </tr>
                        <?php endif;
                    endforeach;
                else: ?>
                    <tr class="pagination-row">
						<img class="logocarrello" src="https://winefully.com/wp-content/uploads/2019/11/logo_home.png">
                        <td class="wishlist-empty"><p class="empty"><?php esc_html_e('Nessun prodotto nella wishlist.', 'elessi-theme') ?><a href="javascript:void(0);" class="button nasa-sidebar-return-shop"><?php echo esc_html__('TORNA AL NEGOZIO', 'elessi-theme'); ?></a></p></td>
                    </tr>
                <?php
                endif;

                if (!empty($page_links)) : ?>
                    <tr>
                        <td colspan="6"><?php echo ($page_links); ?></td>
                    </tr>
                <?php endif ?>
            </tbody>

        </table>

        <?php wp_nonce_field('yith_wcwl_edit_wishlist_action', 'yith_wcwl_edit_wishlist'); ?>

        <?php if ($wishlist_meta['is_default'] != 1) : ?>
            <input type="hidden" value="<?php echo esc_attr($wishlist_meta['wishlist_token']); ?>" name="wishlist_id" id="wishlist_id" />
        <?php endif; ?>

        <?php do_action('yith_wcwl_wishlist_after_wishlist_content', $var); ?>
        
    </form>


    <?php
} else {
    if (function_exists('yith_wcwl_get_template')) {
        echo '<div class="nasa_yith_wishlist_premium-wrap">';
        echo '<div id="yith-wcwl-messages"></div>';
        yith_wcwl_get_template('wishlist-' . $template_part . '.php', $atts);
        echo '</div>';
    }
}
