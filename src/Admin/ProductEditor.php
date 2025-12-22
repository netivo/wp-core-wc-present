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
		add_filter( 'product_type_options', [ $this, 'add_product_type_options' ] );
		add_action( 'woocommerce_process_product_meta', [ $this, 'save_present_packing_checkbox' ] );
	}

	/**
	 * Add option to product type options (next to Virtual/Downloadable).
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function add_product_type_options( $options ) {
		$options['present_packing_enabled'] = [
			'id'            => self::META_KEY,
			'wrapper_class' => 'show_if_simple show_if_variable',
			'label'         => __( 'Możliwość pakowania na prezent', 'netivo' ),
			'description'   => __( 'Zaznacz, jeśli produkt może być zapakowany na prezent.', 'netivo' ),
			'default'       => 'no',
		];

		return $options;
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
