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

	public function test_constructor_adds_hooks() {
		new ProductEditor();

		$this->assertTrue( has_filter( 'product_type_options' ) !== false );
		$this->assertTrue( has_action( 'woocommerce_process_product_meta' ) !== false );
	}

	public function test_add_product_type_options() {
		$editor  = new ProductEditor();
		$options = [ 'virtual' => [] ];
		$result  = $editor->add_product_type_options( $options );

		$this->assertArrayHasKey( 'present_packing_enabled', $result );
		$this->assertEquals( ProductEditor::META_KEY, $result['present_packing_enabled']['id'] );
		$this->assertEquals( 'Możliwość pakowania na prezent', $result['present_packing_enabled']['label'] );
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
