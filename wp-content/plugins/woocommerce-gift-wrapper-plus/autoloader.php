<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WCGWP_Autoloader' ) ) :

	class WCGWP_Autoloader {

		/**
		 * Autoloader constructor
		 *
		 * @param string $prefix
		 * @param string $abspath
		 */
		public function __construct() {
			spl_autoload_register( array( $this, 'autoloader' ) );
		}
			
		/**
		 * Autoloader
		 *
		 * @param string $class_name
		 */
		public function autoloader( $class_name ) {
			if ( ! $this->class_belongs_to_plugin( $class_name ) ) {
				return;
			}

			$path = $this->get_classes_directory() . $this->get_class_path( $class_name ) . '.php';
			if ( file_exists( $path ) ) {
				require_once $path;
			}
		}
				
		/**
		 * Does class belong to plugin (includes 'WWPDF'?)
		 *
		 * @param string $class_name
		 *
		 * @return bool
		 */
		protected function class_belongs_to_plugin( $class_name ) {
			if ( 0 !== strpos( $class_name, 'WC_Gift_Wrapper' ) ) {
				return false;
			}
			return true;	
		}

		/**
		 * Get class path
		 *
		 * @param string $class_name
		 *
		 * @return string
		 */
		protected function get_class_path( $class_name ) {
			return str_replace( '_', '-', strtolower( $class_name ) );
		}

		/**
		 * Get classes directory
		 *
		 * @return string
		 */
		protected function get_classes_directory() {
			return dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR;
		}

	}

endif;
		