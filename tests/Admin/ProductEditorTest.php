<?php

namespace Netivo\Module\WooCommerce\Present\Tests\Admin;

use Brain\Monkey;
use Netivo\Module\WooCommerce\Present\Admin\ProductEditor;
use PHPUnit\Framework\TestCase;

class ProductEditorTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_constructor_adds_actions() {
		new ProductEditor();

		$this->assertTrue( has_action( 'woocommerce_product_options_type' ) !== false );
		$this->assertTrue( has_action( 'woocommerce_process_product_meta' ) !== false );
	}

	public function test_add_present_packing_checkbox() {
		$editor = new ProductEditor();

		global $post;
		$post = (object) [ 'ID' => 123 ];

		Monkey\Functions\expect( 'get_post_meta' )
			->with( 123, ProductEditor::META_KEY, true )
			->andReturn( 'yes' );

		Monkey\Functions\expect( 'woocommerce_wp_checkbox' )
			->once()
			->with( \Mockery::on( function ( $args ) {
				return $args['id'] === ProductEditor::META_KEY && $args['value'] === 'yes';
			} ) );

		ob_start();
		try {
			$editor->add_present_packing_checkbox();
		} finally {
			$output = ob_get_clean();
		}

		$this->assertStringNotContainsString( 'options_group', $output );
	}

	public function test_save_present_packing_checkbox() {
		$editor  = new ProductEditor();
		$post_id = 123;

		// Test checked
		$_POST[ ProductEditor::META_KEY ] = 'yes';
		Monkey\Functions\expect( 'update_post_meta' )
			->once()
			->with( $post_id, ProductEditor::META_KEY, 'yes' )
			->andReturn( true );

		$editor->save_present_packing_checkbox( $post_id );
		$this->assertTrue( true ); // Avoid risky test

		// Test unchecked
		unset( $_POST[ ProductEditor::META_KEY ] );
		Monkey\Functions\expect( 'update_post_meta' )
			->once()
			->with( $post_id, ProductEditor::META_KEY, 'no' )
			->andReturn( true );

		$editor->save_present_packing_checkbox( $post_id );
		$this->assertTrue( true ); // Avoid risky test
	}
}
