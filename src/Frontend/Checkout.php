<?php
/**
 * Created by Netivo for netivo
 * User: manveru
 * Date: 22.12.2025
 * Time: 11:45
 */

namespace Netivo\Module\WooCommerce\Present\Frontend;

use Netivo\Module\WooCommerce\Present\Admin\ProductEditor;
use Netivo\Module\WooCommerce\Present\Product\ProductManager;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	exit;
}

class Checkout {

	const CHECKBOX_ID = 'present_packing_requested';

	public function __construct() {
		add_action( 'woocommerce_review_order_before_shipping', [ $this, 'display_present_packing_checkbox' ] );
		add_action( 'woocommerce_cart_calculate_fees', [ $this, 'add_present_packing_fee' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue script to trigger checkout update when checkbox changes.
	 */
	public function enqueue_scripts() {
		if ( ! is_checkout() ) {
			return;
		}

		wp_add_inline_script( 'wc-checkout', "
			jQuery( function( $ ) {
				$( 'form.checkout' ).on( 'change', 'input[name=" . self::CHECKBOX_ID . "]', function() {
					$( 'body' ).trigger( 'update_checkout' );
				} );
			} );
		" );
	}

	/**
	 * Check if any product in cart is available for present packing.
	 *
	 * @return bool
	 */
	public function is_present_packing_available() {
		if ( ! WC()->cart ) {
			return false;
		}

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product_id = $cart_item['product_id'];
			if ( 'yes' === get_post_meta( $product_id, ProductEditor::META_KEY, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Display the checkbox before shipping row in checkout.
	 */
	public function display_present_packing_checkbox() {
		if ( ! $this->is_present_packing_available() ) {
			return;
		}

		$checked    = isset( $_POST['post_data'] ) ? parse_str( $_POST['post_data'], $post_data ) : [];
		$is_checked = isset( $post_data[ self::CHECKBOX_ID ] );

		if ( ! isset( $post_data ) && isset( WC()->session ) ) {
			$is_checked = WC()->session->get( self::CHECKBOX_ID );
		}

		echo '<tr class="present-packing-checkbox">
				<td colspan="2">';

		woocommerce_form_field( self::CHECKBOX_ID, [
			'type'    => 'checkbox',
			'class'   => [ 'form-row-wide' ],
			'label'   => get_option( 'present_packing_name', __( 'Pakowanie na prezent', 'netivo' ) ),
			'default' => $is_checked ? 1 : 0,
		], $is_checked ? 1 : 0 );

		echo '</td></tr>';
	}

	/**
	 * Add fee if checkbox is checked.
	 *
	 * @param \WC_Cart $cart
	 */
	public function add_present_packing_fee( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		if ( ! $this->is_present_packing_available() ) {
			return;
		}

		$is_checked = false;
		if ( isset( $_POST['post_data'] ) ) {
			parse_str( $_POST['post_data'], $post_data );
			$is_checked = isset( $post_data[ self::CHECKBOX_ID ] );
			WC()->session->set( self::CHECKBOX_ID, $is_checked );
		} elseif ( isset( WC()->session ) ) {
			$is_checked = WC()->session->get( self::CHECKBOX_ID );
		}

		if ( $is_checked ) {
			$name       = get_option( 'present_packing_name', __( 'Pakowanie na prezent', 'netivo' ) );
			$price_type = get_option( 'present_packing_price_type', 'fixed' );
			$price_val  = (float) get_option( 'present_packing_price_value', 0 );

			if ( 'percentage' === $price_type ) {
				$fee = $cart->get_subtotal() * ( $price_val / 100 );
			} else {
				$fee = $price_val;
			}

			if ( $fee > 0 ) {
				$cart->add_fee( $name, $fee );
			}
		}
	}
}
