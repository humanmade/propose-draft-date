<?php
/**
 * Validate that key actions are correctly filterable.
 *
 * @package propose-draft-date
 */

declare( strict_types=1 );

namespace ProposeDraftDate\Tests\Filters;

use ProposeDraftDate;
use WP_UnitTestCase;

/**
 * Test the Meta namespace.
 */
class Test_Filters extends WP_UnitTestCase {
	/**
	 * Validate the filterability of the supported post types list.
	 *
	 * @dataProvider data_get_supported_post_types
	 *
	 * @param string[]      $expected        The expected resulting array of post types.
	 * @param callable|null $filter_function Function to filter supported types, or null.
	 * @return void
	 */
	public function test_get_supported_post_types( array $expected, ?callable $filter_function ) : void {
		if ( is_callable( $filter_function ) ) {
			add_filter( 'proposed_date.supported_post_types', $filter_function );
		}

		$this->assertEquals( $expected, ProposeDraftDate\get_supported_post_types() );
	}

	/**
	 * Data provider for test_get_supported_post_types.
	 *
	 * @return array {
	 *     @type array {
	 *         @type string[]  $expected        The expected resulting array of post types.
	 *         @type ?callable $filter_function Function to filter supported types, or null.
	 *     }
	 * }
	 */
	public function data_get_supported_post_types() : array {
		return [
			[
				[ 'post', 'page' ],
				null,
			],
			[
				[ 'post', 'page', 'my_cpt' ],
				function( $supported_types ) {
					$supported_types[] = 'my_cpt';
					return $supported_types;
				},
			],
			[
				[ 'custom', 'types', 'only!' ],
				function( $supported_types ) {
					return [ 'custom', 'types', 'only!' ];
				},
			],
		];
	}
}
