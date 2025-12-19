<?php

namespace Netivo\Module\WooCommerce\Present\Tests\Admin;

use Brain\Monkey;
use Netivo\Module\WooCommerce\Present\Admin\Settings;
use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_constructor_adds_filters() {
		new Settings();

		$this->assertTrue( has_filter( 'woocommerce_get_sections_products' ) !== false );
		$this->assertTrue( has_filter( 'woocommerce_get_settings_products' ) !== false );
		$this->assertTrue( has_action( 'woocommerce_update_options_products_present_packing' ) !== false );
	}

	public function test_add_section() {
		$settings = new Settings();
		$sections = [ 'other' => 'Other' ];
		$result   = $settings->add_section( $sections );

		$this->assertArrayHasKey( 'present_packing', $result );
		$this->assertEquals( 'Pakowanie na prezent', $result['present_packing'] );
	}

	public function test_add_settings() {
		$settings = new Settings();

		// Test other section
		$result = $settings->add_settings( [ 'initial' ], 'other' );
		$this->assertEquals( [ 'initial' ], $result );

		// Test our section
		$result = $settings->add_settings( [], 'present_packing' );
		$this->assertIsArray( $result );
		$this->assertNotEmpty( $result );

		$ids = array_column( $result, 'id' );
		$this->assertContains( 'present_packing_name', $ids );
		$this->assertContains( 'present_packing_price_type', $ids );
		$this->assertContains( 'present_packing_price_value', $ids );
	}

	public function test_save_settings() {
		$settings = new Settings();

		Monkey\Functions\expect( 'woocommerce_update_options' )
			->once();

		Monkey\Functions\expect( 'get_option' )
			->andReturn( 'some_val' );

		// Mock ProductManager::sync_product dependency
		Monkey\Functions\expect( 'wc_get_product' )
			->andReturn( null );

		$settings->save_settings();

		// If it reaches here without error, and woocommerce_update_options was called, it's good enough.
		$this->assertTrue( true );
	}
}
