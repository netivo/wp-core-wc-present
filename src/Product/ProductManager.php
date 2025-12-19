<?php
/**
 * Created by Netivo for netivo
 * User: manveru
 * Date: 19.12.2025
 * Time: 15:15
 */

namespace Netivo\Module\WooCommerce\Present\Product;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	exit;
}

class ProductManager {

	const PRODUCT_ID_OPTION = 'present_packing_product_id';

	public function __construct() {
		add_action( 'init', [ $this, 'maybe_create_product' ] );
		add_filter( 'woocommerce_product_is_visible', [ $this, 'hide_product_from_frontend' ], 10, 2 );
		add_action( 'pre_get_posts', [ $this, 'hide_product_from_admin' ] );
	}

	/**
	 * Create the present product if it doesn't exist.
	 */
	public function maybe_create_product() {
		$product_id = get_option( self::PRODUCT_ID_OPTION );

		if ( $product_id && get_post( $product_id ) ) {
			return;
		}

		$product = new \WC_Product_Simple();
		$product->set_name( get_option( 'present_packing_name', __( 'Pakowanie na prezent', 'netivo' ) ) );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'hidden' );
		$product->set_price( 0 );
		$product->set_regular_price( 0 );
		$product->save();

		update_option( self::PRODUCT_ID_OPTION, $product->get_id() );
	}

	/**
	 * Update the connected product based on settings.
	 */
	public static function sync_product() {
		$product_id = get_option( self::PRODUCT_ID_OPTION );
		if ( ! $product_id ) {
			return;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		$name       = get_option( 'present_packing_name' );
		$price_type = get_option( 'present_packing_price_type' );
		$price_val  = get_option( 'present_packing_price_value' );

		if ( $name ) {
			$product->set_name( $name );
		}

		if ( 'fixed' === $price_type ) {
			$product->set_regular_price( $price_val );
			$product->set_price( $price_val );
		} else {
			// If percentage, we might want to keep price at 0 or handle it differently.
			// The requirement says: "Update the product title and price (if type is fixed)"
			$product->set_regular_price( 0 );
			$product->set_price( 0 );
		}

		$product->save();
	}

	/**
	 * Hide product from front-end listings.
	 *
	 * @param bool $visible
	 * @param int $product_id
	 *
	 * @return bool
	 */
	public function hide_product_from_frontend( $visible, $product_id ) {
		$present_product_id = (int) get_option( self::PRODUCT_ID_OPTION );
		if ( $present_product_id && $product_id === $present_product_id ) {
			return false;
		}

		return $visible;
	}

	/**
	 * Hide product from admin listings.
	 *
	 * @param \WP_Query $query
	 */
	public function hide_product_from_admin( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || 'product' !== $query->get( 'post_type' ) ) {
			return;
		}

		$product_id = (int) get_option( self::PRODUCT_ID_OPTION );
		if ( ! $product_id ) {
			return;
		}

		$post__not_in = $query->get( 'post__not_in' );
		if ( ! is_array( $post__not_in ) ) {
			$post__not_in = [];
		}

		$post__not_in[] = $product_id;
		$query->set( 'post__not_in', $post__not_in );
	}
}
