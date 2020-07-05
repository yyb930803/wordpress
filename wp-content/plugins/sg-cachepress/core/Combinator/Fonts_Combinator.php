<?php
namespace SiteGround_Optimizer\Combinator;

use SiteGround_Optimizer\Helper\Helper;
use SiteGround_Optimizer\Front_End_Optimization\Front_End_Optimization;

/**
 * SG Fonts Combinator main plugin class
 */
class Fonts_Combinator extends Abstract_Combinator {

	/**
	 * Dir where the we will store the Google fonts css.
	 *
	 * @since 5.3.6
	 *
	 * @var string|null Path to fonts dir.
	 */
	public $fonts_dir = 'google-fonts';

	/**
	 * Regex parts.
	 *
	 * @since 5.3.4
	 *
	 * @var array Google Fonts regular expression
	 */
	public $regex_parts = array(
		'~', // The php quotes.
		'<link', // Match the opening part of link tags.
		'(?:\s+(?:(?!href\s*=\s*)[^>])+)?', // Negative lookahead aserting the regex does not match href attribute.
		'(?:\s+href\s*=\s*(?P<quotes>[\'|"]))', // Match the href attribute followed by single or double quotes. Create a `quotes` group, so we can use it later.
		'(', // Open the capturing group for the href value.
			'(?:https?:)?', // Match the protocol, which is optional. Sometimes the fons is added. without protocol i.e. //fonts.googleapi.com/css.
			'\/\/fonts\.googleapis\.com\/css', // Match that the href value is google font link.
			'(?:(?!(?P=quotes)).)+', // Match anything in the href attribute until the closing quote.
		')', // Close the capturing group.
		'(?P=quotes)', // Match the closing quote.
		'(?:\s+.*?)?', // Match anything else after the href tag.
		'[>]', // Until the closing tag if found.
		'~', // The php quotes.
		'ims',
	);

	/**
	 * The singleton instance.
	 *
	 * @since 5.5.2
	 *
	 * @var The singleton instance.
	 */
	private static $instance;

	/**
	 * The constructor.
	 *
	 * @since 5.5.2
	 */
	public function __construct() {
		parent::__construct();
		self::$instance = $this;
	}

	/**
	 * Get the singleton instance.
	 *
	 * @since 5.5.2
	 *
	 * @return  The singleton instance.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Combine the google fonts.
	 *
	 * @since  5.3.4
	 *
	 * @param  string $html The page html.
	 *
	 * @return string       Modified html with combined Google font.
	 */
	public function run( $html ) {
		// Get fonts if any.
		$fonts = $this->get_items( $html );
		// Bail if there are no fonts or if there is only one font.
		if ( empty( $fonts ) ) {
			return $html;
		}

		$_fonts = $fonts;

		// The methods that should be called to combine the fonts.
		$methods = array(
			'parse_fonts', // Parse fonts.
			'beautify', // Beautify and remove duplicates.
			'implode_pieces', // Beautify and remove duplicates.
			'get_combined_css', // Get combined css.
		);

		foreach ( $methods as $method ) {
			$_fonts = call_user_func( array( $this, $method ), $_fonts );
		}

		$html = str_replace( '</head>', $_fonts . '</head>', $html );

		// Remove old fonts.
		foreach ( $fonts as $font ) {
			$html = str_replace( $font[0], '', $html );
		}

		return $html;
	}

	/**
	 * Parse and get Google fonts details.
	 *
	 * @since  5.3.4
	 *
	 * @param  array $fonts Google fonts found in the page html.
	 *
	 * @return array        Google fonts details.
	 */
	public function parse_fonts( $fonts ) {
		$parts = array(
			'fonts'  => array(),
			'subset' => array(),
		);

		foreach ( $fonts as $font ) {
			// Decode the entities.
			$url   = html_entity_decode( $font[2] );
			// Parse the url and get the query string.
			$query_string = wp_parse_url( $url, PHP_URL_QUERY );

			// Bail if the query string is empty.
			if ( ! isset( $query_string ) ) {
				return;
			}

			// Parse the query args.
			$parsed_font = wp_parse_args( $query_string );

			$parts['fonts'][] = $parsed_font['family'];

			// Add subset to collection.
			if ( isset( $parsed_font['subset'] ) ) {
				$parts['subset'][] = $parsed_font['subset'];
			}
		}

		return $parts;
	}

	/**
	 * Convert all special chars, htmlentities and remove duplicates.
	 *
	 * @since  5.3.4
	 *
	 * @param  array $parts The Google font details.
	 *
	 * @return arrray        Beatified font details.
	 */
	public function beautify( $parts ) {
		// URL encode & convert characters to HTML entities.
		$parts = array_map( function( $item ) {
			return array_map(
				'rawurlencode',
				array_map(
					'htmlentities',
					$item
				)
			);
		}, $parts);

		// Remove duplicates.
		return array_map(
			'array_filter',
			array_map(
				'array_unique',
				$parts
			)
		);
	}

	/**
	 * Implode Google fonts and subsets, so they can be used in combined tag.
	 *
	 * @since  5.3.4
	 *
	 * @param  array $fonts Font deatils.
	 *
	 * @return array        Imploaded fonts and subsets.
	 */
	public function implode_pieces( $fonts ) {
		return array(
			'fonts'   => implode( '%7C', $fonts['fonts'] ),
			'subsets' => implode( ',', $fonts['subset'] ),
		);
	}

	/**
	 * Combine Google fonts in one tag
	 *
	 * @since  5.3.4
	 *
	 * @param  array $fonts Fonts data.
	 *
	 * @return string        Combined tag.
	 */
	public function get_combined_css( $fonts ) {
		$display = apply_filters( 'sgo_google_fonts_display', 'swap' );
		// Combined url for Google fonts.
		$url = 'https://fonts.googleapis.com/css?family=' . $fonts['fonts'] . '&subset=' . $fonts['subsets'] . '&display=' . $display;
		// Build the combined tag in case the css is missing or the request fail.
		$combined_tag = '<link rel="stylesheet" data-provider="sgoptimizer" href="' . $url . '" />';

		// Get the fonts css.
		$css = $this->get_external_file_content( $url, 'css', 'fonts' );

		// Return the combined tag if the css is empty.
		if ( false === $css ) {
			return $combined_tag;
		}

		// Return combined tag if AMP plugin is active.
		if (
			( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) ||
			( function_exists( 'ampforwp_is_amp_endpoint' ) && ampforwp_is_amp_endpoint() )
		) {
			return $combined_tag;
		}

		// Return the inline css.
		return '<style type="text/css">' . $css . '</style>';
	}
}
