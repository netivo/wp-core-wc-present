<?php

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

class WC_Order_Item_Product {
	public function set_product( $product ) {
	}

	public function set_quantity( $qty ) {
	}

	public function set_subtotal( $subtotal ) {
	}

	public function set_total( $total ) {
	}
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', sys_get_temp_dir() . '/' );
}

/**
 * Mock WordPress translation functions if they are not defined.
 */
if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}
