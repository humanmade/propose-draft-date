<?php
/**
 * Validate functions in the meta namespace behaves correctly.
 *
 * @package propose-draft-date
 */

declare( strict_types=1 );

namespace ProposeDraftDate\Tests\Meta;

use ProposeDraftDate\Meta;
use WP_UnitTestCase;

/**
 * Test the Meta namespace.
 */
class Test_Meta extends WP_UnitTestCase {
	/**
	 * Validate that the authentication callback correctly permits the right roles
	 * to be able to set this meta.
	 *
	 * @dataProvider data_date_proposal_auth_callback
	 *
	 * @param string $role       The role to test.
	 * @param string $authorized Whether the provided role should be authorized.
	 */
	public function test_date_proposal_auth_callback( string $role, string $authorized ) : void {
		if ( empty( $role ) ) {
			wp_set_current_user( 0 );
		} else {
			$user_id = self::factory()->user->create( [ 'role' => $role ] );
			wp_set_current_user( $user_id );
		}
		$this->assertEquals( $authorized, Meta\allow_proposed_date() );
	}

	/**
	 * Data provider for test_date_proposal_auth_callback.
	 *
	 * @return array {
	 *     @type array {
	 *         @type string $role       The role to test.
	 *         @type string $authorized Whether the provided role should be authorized.
	 *     }
	 * }
	 */
	public function data_date_proposal_auth_callback() : array {
		return [
			[ '',              false ],
			[ 'subscriber',    false ],
			[ 'contributor',   true ],
			[ 'author',        true ],
			[ 'editor',        true ],
			[ 'administrator', true ],
		];
	}

	/**
	 * @dataProvider data_sanitize_proposed_date
	 *
	 * @param string $input    The incoming meta value to sanitize.
	 * @param string $expected Expected result after sanitization.
	 */
	public function test_sanitize_proposed_date( $input, $expected ) : void {
		$sanitized = Meta\sanitize_proposed_date( $input );
		$this->assertEquals( $expected, $sanitized );
	}

	/**
	 * Data provider for test_sanitize_proposed_date.
	 *
	 * @return array {
	 *     @type array {
	 *         @type string $input    The incoming meta value to sanitize.
	 *         @type string $expected Expected result after sanitization.
	 *     }
	 * }
	 */
	public function data_sanitize_proposed_date() : array {
		return [
			[ 'Diamonds on the soles of her shoes', '' ],
			[ '2020-06-06T10:17:34', '2020-06-06 10:17:34' ],
			[ "2020-06-06 %%%ABABAB 10:25:56 %A%A%ABBB", '2020-06-06 10:25:56' ],
		];
	}

	/**
	 * Test the get_proposed_date helper function.
	 *
	 * @dataProvider data_get_proposed_date
	 *
	 * @param string      $post_status   The status of the post object to test
	 * @param string|null $meta_value    The value of the meta to set on the post
	 * @param string|null $expected_date The expected return value.
	 */
	public function test_get_proposed_date( string $post_status, $meta_value, $expected_date ) : void {
		$post_id = self::factory()->post->create( [ 'post_status' => $post_status ] );
		if ( $meta_value !== null ) {
			$this->assertInternalType(
				'integer',
				update_post_meta( $post_id, Meta\PROPOSED_DATE_META_KEY, $meta_value )
			);
		}
		$this->assertEquals( $expected_date, Meta\get_proposed_date( $post_id ) );
	}

	/**
	 * Validate the getter function works the same when passed a full post object.
	 *
	 * @dataProvider data_get_proposed_date
	 *
	 * @param string      $post_status   The status of the post object to test
	 * @param string|null $meta_value    The value of the meta to set on the post
	 * @param string|null $expected_date The expected return value.
	 */
	public function test_get_proposed_date_with_post_object( string $post_status, $meta_value, $expected_date ) : void {
		$post_id = self::factory()->post->create( [ 'post_status' => $post_status ] );
		if ( $meta_value !== null ) {
			$this->assertInternalType(
				'integer',
				update_post_meta( $post_id, Meta\PROPOSED_DATE_META_KEY, $meta_value )
			);
		}
		$this->assertEquals( $expected_date, Meta\get_proposed_date( get_post( $post_id ) ) );
	}

	/**
	 * Data provider for test_get_proposed_date().
	 *
	 * @return array {
	 *     @type array {
	 *         @type string      $post_status     The status of the post object to test
	 *         @type string|null $meta_value      The value of the meta to set on the post
	 *         @type string|null $expected_date The expected return value.
	 *     }
	 * }
	 */
	public function data_get_proposed_date() {
		return [
			[ 'draft',   null,                  null ],
			[ 'draft',   '',                    null ],
			[ 'draft',   '      ',              null ],
			[ 'publish', '2020-06-06 12:07:12', '' ],
			[ 'future',  '2020-06-06 12:07:12', '' ],
			[ 'draft',   '2020-06-06 12:07:12', '2020-06-06 12:07:12' ],
			[ 'pending', '2020-06-06 12:07:12', '2020-06-06 12:07:12' ],
		];
	}
}
