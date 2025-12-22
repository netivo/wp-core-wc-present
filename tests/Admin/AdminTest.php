<?php

namespace Netivo\Module\WooCommerce\Present\Tests\Admin;

use Brain\Monkey;
use Netivo\Module\WooCommerce\Present\Admin\Admin;
use PHPUnit\Framework\TestCase;

class AdminTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_constructor_initializes_settings_and_product_editor() {
		// Admin constructor initializes Settings and ProductEditor.
		// Settings adds 'woocommerce_get_sections_products' filter.
		// ProductEditor adds 'woocommerce_product_options_type' action.

		new Admin();

		$this->assertTrue( has_filter( 'woocommerce_get_sections_products' ) !== false );
		$this->assertTrue( has_action( 'woocommerce_product_options_type' ) !== false );
	}
}
