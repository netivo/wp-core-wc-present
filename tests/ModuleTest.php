<?php

namespace Netivo\Module\WooCommerce\Present\Tests;

use Brain\Monkey;
use Netivo\Module\WooCommerce\Present\Module;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_constructor_initializes_product_manager() {
		Monkey\Functions\expect( 'is_admin' )
			->andReturn( false );

		// We expect the ProductManager constructor to be called.
		// Since we can't easily mock the 'new' operator for classes in the same namespace without extra tools,
		// we verify that the 'init' hook (from ProductManager) is added.

		new Module();

		$this->assertTrue( has_action( 'init' ) !== false );
	}

	public function test_constructor_initializes_admin_when_in_admin() {
		Monkey\Functions\expect( 'is_admin' )
			->andReturn( true );

		// When in admin, both ProductManager and Admin should be initialized.
		// Admin initializes Settings and ProductEditor.
		// Settings adds 'woocommerce_get_sections_products' filter.

		new Module();

		$this->assertTrue( has_action( 'init' ) !== false );
		$this->assertTrue( has_filter( 'woocommerce_get_sections_products' ) !== false );
	}
}
