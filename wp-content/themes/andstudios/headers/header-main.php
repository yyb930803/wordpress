<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta property="og:image" content="https://winefully.com/wp-content/uploads/2020/01/winefully-sito-type.jpg">
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<!-- Google Tag Manager -->

<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':

new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],

j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=

'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);

})(window,document,'script','dataLayer','GTM-TW4WP39');</script>

<!-- End Google Tag Manager -->

<?php if (function_exists('wp_site_icon')) : ?>
    <link rel="shortcut icon" href="<?php echo (isset($nasa_opt['site_favicon']) && $nasa_opt['site_favicon']) ? esc_attr($nasa_opt['site_favicon']) : ELESSI_THEME_URI . '/favicon.ico'; ?>" />
<?php endif; ?>

<?php wp_head(); ?>
	<?php global $post, $product; ?>
<?php if (isset($product)){ ?>
<div itemscope itemtype="http://schema.org/Product">
<meta itemprop="name" content="<?php echo $product->get_title(); ?>">
<meta itemprop="productID" content="<?php echo $product->id; ?>">
<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
<meta itemprop="price" content="<?php echo $product->get_price(); ?>" />
<meta itemprop="priceCurrency" content="<?php echo get_woocommerce_currency(); ?>" />
<link itemprop="availability" href="http://schema.org/<?php echo $product->is_in_stock() ? 'InStock' : 'OutOfStock'; ?>" />
</div>
</div>
<?php } ?>
</head>

<body <?php body_class(); ?>>
	<!-- Google Tag Manager (noscript) -->

<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TW4WP39"

height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

<!-- End Google Tag Manager (noscript) -->
<?php do_action('nasa_theme_before_load'); ?>
<div id="wrapper" class="fixNav-enabled">
<header id="header-content" class="site-header">
	<?php $classes = get_body_class();
if (in_array('woocommerce',$classes)) {
    echo '<div class="spedizionegratuita"><p>SPEDIZIONI GRATIS SEMPRE IN TUTT’ITALIA</p>';
		echo '</div>';
} else {
}
	?>
<?php do_action('nasa_before_header_structure'); ?>
<?php do_action('nasa_header_structure'); ?>
<?php do_action('nasa_after_header_structure'); ?>
</header>

<div id="main-content" class="site-main light">
    