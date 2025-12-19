<?php

namespace Netivo\Module\WooCommerce\Present\Tests;

use Brain\Monkey;
use Netivo\Module\WooCommerce\Present\Product\ProductManager;
use PHPUnit\Framework\TestCase;

class ProductManagerTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_constructor_adds_hooks() {
		new ProductManager();

		$this->assertTrue( has_action( 'init' ) !== false );
		$this->assertTrue( has_filter( 'woocommerce_product_is_visible' ) !== false );
		$this->assertTrue( has_action( 'pre_get_posts' ) !== false );
	}

	public function test_maybe_create_product_exists() {
		$manager = new ProductManager();

		Monkey\Functions\expect( 'get_option' )
			->with( ProductManager::PRODUCT_ID_OPTION )
			->andReturn( 123 );

		Monkey\Functions\expect( 'get_post' )
			->with( 123 )
			->andReturn( (object) [ 'ID' => 123 ] );

		// Should return early and not create product
		$this->assertNull( $manager->maybe_create_product() );
	}

	public function test_hide_product_from_frontend() {
		$manager = new ProductManager();

		Monkey\Functions\expect( 'get_option' )
			->with( ProductManager::PRODUCT_ID_OPTION )
			->andReturn( 123 );

		// Same ID, should be hidden
		$this->assertFalse( $manager->hide_product_from_frontend( true, 123 ) );

		// Different ID, should be visible
		$this->assertTrue( $manager->hide_product_from_frontend( true, 456 ) );
	}

	public function test_hide_product_from_admin() {
		$manager = new ProductManager();

		Monkey\Functions\expect( 'is_admin' )->andReturn( true );
		Monkey\Functions\expect( 'get_option' )
			->with( ProductManager::PRODUCT_ID_OPTION )
			->andReturn( 123 );

		$query = \Mockery::mock( 'WP_Query' );
		$query->shouldReceive( 'is_main_query' )->andReturn( true );
		$query->shouldReceive( 'get' )->with( 'post_type' )->andReturn( 'product' );
		$query->shouldReceive( 'get' )->with( 'post__not_in' )->andReturn( [ 10, 20 ] );
		$query->shouldReceive( 'set' )->with( 'post__not_in', [ 10, 20, 123 ] )->once();

		$manager->hide_product_from_admin( $query );
		$this->assertTrue( true ); // Avoid risky test
	}
}
