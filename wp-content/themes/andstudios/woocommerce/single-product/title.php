<?php
/**
 * Single Product title
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/title.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.woocommerce.com/document/template-structure/
 * @package    WooCommerce/Templates
 * @version    1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $product;
$titoloformatobottiglia = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'titoloformatobottiglia') : '';
$titoloetichetta = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'titoloetichetta') : '';
$sottotitoloformatobottiglia = (!isset($nasa_opt['enable_specifications']) || $nasa_opt['enable_specifications'] == '1') ? elessi_get_product_meta_value($product->get_id(), 'sottotitoloformatobottiglia') : '';
?>
<div id="outerprodotto" class="titoloprodotto product_title entry-title <?php $attributes = nt_product_attributes();
echo $attributes->tipo;  ?> bottle<?php 
		
		$available_variations = $product->get_available_variations();
    $selectedPrice = '';
    $dump = '';

    foreach ( $available_variations as $variation )
    {
        // $dump = $dump . '<pre>' . var_export($variation['attributes'], true) . '</pre>';

        $isDefVariation=false;
        foreach($product->get_default_attributes() as $key=>$val){
            // $dump = $dump . '<pre>' . var_export($key, true) . '</pre>';
            // $dump = $dump . '<pre>' . var_export($val, true) . '</pre>';
            if($variation['attributes']['attribute_'.$key]==$val){
                $isDefVariation=true;
            }   
        }
        if($isDefVariation){
            $current_variation = $val;         
        }
		
    }
	echo $current_variation;
//  $dump = $dump . '<pre>' . var_export($available_variations, true) . '</pre>';

    
		

// 		foreach ( $product->get_variation_attributes() as $attribute => $terms ) {
// 			foreach( $terms as $index=>$term_slug ){
// 				if ($index == 0) {
// 					  echo '' . $term_slug . '';
// 				 }
				
// 			}
// 		} 
		
		?>">
	<p class="titolocasavinicola">Produttore: <span class="rossocasavinicola"><?php echo wc_get_product_category_list($product->get_id(), ', '); ?></span></p>
	<?php the_title( '<h1 class="product_title entry-title">', '</h1>' ); ?>
</div>