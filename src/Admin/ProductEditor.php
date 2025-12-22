<?php
/**
 * Created by Netivo for netivo
 * User: manveru
 * Date: 19.12.2025
 * Time: 15:20
 */

namespace Netivo\Module\WooCommerce\Present\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	exit;
}

class ProductEditor {

	const META_KEY = '_present_packing_enabled';

	public function __construct() {
		add_action( 'woocommerce_product_options_type', [ $this, 'add_present_packing_checkbox' ] );
		add_action( 'woocommerce_process_product_meta', [ $this, 'save_present_packing_checkbox' ] );
	}

	/**
	 * Add checkbox to product general options.
	 */
	public function add_present_packing_checkbox() {
		global $post;

		woocommerce_wp_checkbox( [
			'id'          => self::META_KEY,
			'label'       => __( 'Możliwość pakowania na prezent', 'netivo' ),
			'description' => __( 'Zaznacz, jeśli produkt może być zapakowany na prezent.', 'netivo' ),
			'value'       => get_post_meta( $post->ID, self::META_KEY, true ),
		] );
	}

	/**
	 * Save checkbox value.
	 *
	 * @param int $post_id
	 */
	public function save_present_packing_checkbox( $post_id ) {
		$checkbox = isset( $_POST[ self::META_KEY ] ) ? 'yes' : 'no';
		update_post_meta( $post_id, self::META_KEY, $checkbox );
	}
}
