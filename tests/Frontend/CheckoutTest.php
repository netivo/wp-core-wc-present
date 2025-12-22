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
		$this->assertTrue( has_action( 'woocommerce_checkout_update_order_review' ) !== false );
		$this->assertTrue( has_action( 'woocommerce_cart_calculate_fees' ) !== false );
		$this->assertTrue( has_action( 'woocommerce_checkout_create_order' ) !== false );
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

	public function test_add_present_packing_fee() {
		$checkout = new Checkout();
		$cart     = WC()->cart;

		Monkey\Functions\when( 'is_admin' )->justReturn( false );
		Monkey\Functions\when( 'get_post_meta' )->justReturn( 'yes' );
		Monkey\Functions\when( 'get_option' )->alias( function ( $key, $default = false ) {
			if ( $key === 'present_packing_name' ) {
				return 'Pakowanie na prezent';
			}
			if ( $key === 'present_packing_price_type' ) {
				return 'fixed';
			}
			if ( $key === 'present_packing_price_value' ) {
				return 10.0;
			}

			return $default;
		} );

		// Mock is_present_packing_available prerequisites
		$cart_item = [ 'product_id' => 123 ];
		$cart->shouldReceive( 'get_cart' )->andReturn( [ $cart_item ] );

		// Mock session
		WC()->session->shouldReceive( 'get' )->with( Checkout::CHECKBOX_ID )->andReturn( true );

		$cart->shouldReceive( 'add_fee' )->with( 'Pakowanie na prezent', 10.0 )->once();

		$checkout->add_present_packing_fee( $cart );
		$this->assertTrue( true );
	}

	public function test_add_present_product_to_order() {
		$checkout = new Checkout();
		$order    = \Mockery::mock( 'WC_Order' );
		$data     = [];

		Monkey\Functions\when( 'is_admin' )->justReturn( false );
		Monkey\Functions\when( 'get_post_meta' )->justReturn( 'yes' );
		Monkey\Functions\when( 'get_option' )->alias( function ( $key, $default = false ) {
			if ( $key === 'present_packing_product_id' ) {
				return 999;
			}
			if ( $key === 'present_packing_price_type' ) {
				return 'fixed';
			}
			if ( $key === 'present_packing_price_value' ) {
				return 10.0;
			}
			if ( $key === 'present_packing_name' ) {
				return 'Pakowanie na prezent';
			}

			return $default;
		} );

		// Mock is_present_packing_available prerequisites
		$cart_item = [ 'product_id' => 123 ];
		WC()->cart->shouldReceive( 'get_cart' )->andReturn( [ $cart_item ] );

		// Mock session
		WC()->session->shouldReceive( 'get' )->with( Checkout::CHECKBOX_ID )->andReturn( true );

		// Mock product
		$product = \Mockery::mock( 'WC_Product' );
		Monkey\Functions\when( 'wc_get_product' )->justReturn( $product );

		// Mock order methods
		$order->shouldReceive( 'get_fees' )->andReturn( [] );
		$order->shouldReceive( 'add_item' )->once();
		$order->shouldReceive( 'calculate_totals' )->once();
		$order->shouldReceive( 'save' )->once();

		$checkout->add_present_product_to_order( $order, $data );
		$this->assertTrue( true );
	}
}
