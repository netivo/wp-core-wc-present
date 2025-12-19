<?php
/**
 * Created by Netivo for netivo
 * User: manveru
 * Date: 19.12.2025
 * Time: 15:00
 *
 */

namespace Netivo\Module\WooCommerce\Present\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	exit;
}

class Admin {

	public function __construct() {
		new Settings();
		new ProductEditor();
	}

}