<?php
/**
 * Created by Netivo for netivo
 * User: manveru
 * Date: 19.12.2025
 * Time: 15:00
 *
 */

namespace Netivo\Module\WooCommerce\Present;

use Netivo\Module\WooCommerce\Present\Admin\Admin;
use Netivo\Module\WooCommerce\Present\Product\ProductManager;
use Netivo\Module\WooCommerce\Present\Frontend\Checkout;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	exit;
}

class Module {


	public function __construct() {
		new ProductManager();
		new Checkout();

		if ( is_admin() ) {
			new Admin();
		}
	}

	/**
	 * Retrieves the file system path of the module directory.
	 *
	 * @return false|string|null Returns the absolute path to the module directory if it exists,
	 *                           false if the path cannot be resolved, or null if the file does not exist.
	 */
	public static function get_module_path(): false|string|null {
		$file = realpath( __DIR__ . '/../' );
		if ( file_exists( $file ) ) {
			return $file;
		}

		return null;
	}
}