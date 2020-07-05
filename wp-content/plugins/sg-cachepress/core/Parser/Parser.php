<?php
namespace SiteGround_Optimizer\Parser;

use SiteGround_Optimizer\Minifier\Minifier;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Combinator\Css_Combinator;
use SiteGround_Optimizer\Combinator\Js_Combinator;
use SiteGround_Optimizer\Combinator\Fonts_Combinator;

/**
 * Parser functions and main initialization class.
 */
class Parser {

	/**
	 * The constructor.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		if ( ! defined( 'WP_CLI' ) ) {
			// Add the hooks that we will use to combine the css.
			add_action( 'init', array( $this, 'start_bufffer' ) );
			add_action( 'shutdown', array( $this, 'end_buffer' ) );
		}
	}

	/**
	 * Run the parser.
	 *
	 * @since  5.5.2
	 *
	 * @param  string $html The page html.
	 *
	 * @return string $html The modified html.
	 */
	public function run( $html ) {
		if ( Options::is_enabled( 'siteground_optimizer_optimize_html' ) ) {
			$html = Minifier::get_instance()->run( $html );
		}

		if ( Options::is_enabled( 'siteground_optimizer_combine_css' ) ) {
			$html = Css_Combinator::get_instance()->run( $html );
		}

		if ( Options::is_enabled( 'siteground_optimizer_combine_javascript' ) ) {
			$html = Js_Combinator::get_instance()->run( $html );
		}

		if ( Options::is_enabled( 'siteground_optimizer_combine_google_fonts' ) ) {
			$html = Fonts_Combinator::get_instance()->run( $html );
		}


		return $html;
	}

	/**
	 * Start buffer.
	 *
	 * @since  5.5.0
	 */
	public function start_bufffer() {
		if ( \is_user_logged_in() ) {
			return;
		}

		ob_start( array( $this, 'run' ) );
	}

	/**
	 * End the buffer.
	 *
	 * @since  5.5.0
	 */
	public function end_buffer() {
		if ( ob_get_length() ) {
			ob_end_flush();
		}
	}
}
