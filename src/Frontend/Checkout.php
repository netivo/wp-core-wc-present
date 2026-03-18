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
		add_action( 'woocommerce_checkout_update_order_review', [ $this, 'update_session_on_checkout' ] );
		add_action( 'woocommerce_cart_calculate_fees', [ $this, 'add_present_packing_fee' ] );
		add_action( 'woocommerce_checkout_order_processed', [ $this, 'add_present_product_to_order' ], 10, 3 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Update session with checkbox value during checkout update.
	 *
	 * @param string $post_data
	 */
	public function update_session_on_checkout( $post_data ) {
		parse_str( $post_data, $data );
		$is_checked = isset( $data[ self::CHECKBOX_ID ] );
		WC()->session->set( self::CHECKBOX_ID, $is_checked );
	}

	/**
	 * Calculate the price for present packing.
	 *
	 * @param \WC_Cart $cart
	 *
	 * @return float
	 */
	private function calculate_price( $cart ) {
		$price_type = get_option( 'present_packing_price_type', 'fixed' );
		$price_val  = (float) get_option( 'present_packing_price_value', 0 );

		$price_val = apply_filters( 'netivo/present/price', $price_val, $price_type );

		if ( 'percentage' === $price_type ) {
			return $cart->get_subtotal() * ( $price_val / 100 );
		}

		return $price_val;
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

		$is_checked = false;
		if ( isset( $_POST['post_data'] ) ) {
			parse_str( $_POST['post_data'], $post_data );
			$is_checked = isset( $post_data[ self::CHECKBOX_ID ] );
		} elseif ( isset( WC()->session ) ) {
			$is_checked = WC()->session->get( self::CHECKBOX_ID );
		}

		$price = $this->calculate_price( WC()->cart );
		$label = get_option( 'present_packing_name', __( 'Pakowanie na prezent', 'netivo' ) );

		if ( $price > 0 ) {
			$label .= ' (' . wc_price( $price ) . ')';
		}

		echo '<tr class="present-packing-checkbox">
				<td colspan="2">';

		woocommerce_form_field( self::CHECKBOX_ID, [
			'type'    => 'checkbox',
			'class'   => [ 'form-row-wide' ],
			'label'   => $label,
			'default' => $is_checked ? 1 : 0,
		], $is_checked ? 1 : 0 );

		echo '</td></tr>';
	}

	/**
	 * Add fee to cart if checkbox is checked.
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
			$name  = get_option( 'present_packing_name', __( 'Pakowanie na prezent', 'netivo' ) );
			$price = $this->calculate_price( $cart );

			if ( $price > 0 ) {
				$cart->add_fee( $name, $price );
			}
		}
	}

	/**
	 * Add the present product to the order after it is created.
	 *
	 * @param int $order_id
	 * @param array $posted_data
	 * @param \WC_Order $order
	 */
	public function add_present_product_to_order( $order_id, $posted_data, $order ) {
		if ( ! $order instanceof \WC_Order ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! $order ) {
			return;
		}

		$is_checked = false;
		if ( isset( WC()->session ) ) {
			$is_checked = WC()->session->get( self::CHECKBOX_ID );
		}

		if ( ! $is_checked || ! $this->is_present_packing_available() ) {
			return;
		}

		$present_product_id = (int) get_option( ProductManager::PRODUCT_ID_OPTION );
		if ( ! $present_product_id ) {
			return;
		}

		$product = wc_get_product( $present_product_id );
		if ( ! $product ) {
			return;
		}

		$price = $this->calculate_price( WC()->cart );
		$name  = get_option( 'present_packing_name', __( 'Pakowanie na prezent', 'netivo' ) );

		// Remove existing fees with the same name if any (to avoid double charging if it was added as fee)
		foreach ( $order->get_fees() as $fee_id => $fee ) {
			if ( $fee->get_name() === $name ) {
				$order->remove_item( $fee_id );
			}
		}

		$item = new \WC_Order_Item_Product();
		$item->set_product( $product );
		$item->set_quantity( 1 );
		$item->set_subtotal( $price );
		$item->set_total( $price );

		$order->add_item( $item );
		$order->calculate_totals();
		$order->save();
	}
}
