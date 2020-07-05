<?php

namespace libphonenumber;

spl_autoload_register( function ( $class ) {
	$prefix   = __NAMESPACE__;
	$base_dir = __DIR__;
	$len      = strlen( $prefix );

	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$relative_class = str_replace( '_', '-', $relative_class );
	$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	} else {
		return;
	}
} );