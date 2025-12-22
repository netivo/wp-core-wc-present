<?php

namespace Netivo\Module\WooCommerce\Present\Tests\Frontend;

use Brain\Monkey;
use Netivo\Module\WooCommerce\Present\Frontend\Checkout;
use Netivo\Module\WooCommerce\Present\Admin\ProductEditor;
use PHPUnit\Framework\TestCase;

class CheckoutTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// Mock WC() global
		$wc      = \Mockery::mock( 'WC' );
		$cart    = \Mockery::mock( 'WC_Cart' );
		$session = \Mockery::mock( 'WC_Session' );

		$wc->cart    = $cart;
		$wc->session = $session;

		Monkey\Functions\expect( 'WC' )->andReturn( $wc );
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_constructor_adds_hooks() {
		new Checkout();

		$this->assertTrue( has_action( 'woocommerce_review_order_before_shipping' ) !== false );
		$this->assertTrue( has_action( 'woocommerce_cart_calculate_fees' ) !== false );
		$this->assertTrue( has_action( 'wp_enqueue_scripts' ) !== false );
	}

	public function test_is_present_packing_available_true() {
		$checkout = new Checkout();

		$cart_item = [ 'product_id' => 123 ];
		WC()->cart->shouldReceive( 'get_cart' )->andReturn( [ $cart_item ] );

		Monkey\Functions\expect( 'get_post_meta' )
			->with( 123, ProductEditor::META_KEY, true )
			->andReturn( 'yes' );

		$this->assertTrue( $checkout->is_present_packing_available() );
	}

	public function test_is_present_packing_available_false() {
		$checkout = new Checkout();

		$cart_item = [ 'product_id' => 123 ];
		WC()->cart->shouldReceive( 'get_cart' )->andReturn( [ $cart_item ] );

		Monkey\Functions\expect( 'get_post_meta' )
			->with( 123, ProductEditor::META_KEY, true )
			->andReturn( 'no' );

		$this->assertFalse( $checkout->is_present_packing_available() );
	}
}
