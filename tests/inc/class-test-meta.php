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
	 * Set the current WordPress user to a new user of a specific role.
	 *
	 * @param string $role The role to use for the current user.
	 *
	 * @return void Sets current user and returns.
	 */
	private function setRole( string $role ) {
		$user_id = self::factory()->user->create( [ 'role' => $role ] );
		wp_set_current_user( $user_id );
	}

	public function test_deny_date_proposal_as_anonymous() : void {
		wp_set_current_user( 0 );
		$this->assertEquals( false, Meta\allow_proposed_date() );
	}

	public function test_deny_date_proposal_as_subscriber() : void {
		$this->setRole( 'subscriber' );
		$this->assertEquals( false, Meta\allow_proposed_date() );
	}

	public function test_allow_date_proposal_as_contributor() : void {
		$this->setRole( 'contributor' );
		$this->assertEquals( true, Meta\allow_proposed_date() );
	}

	public function test_allow_date_proposal_as_author() : void {
		$this->setRole( 'author' );
		$this->assertEquals( true, Meta\allow_proposed_date() );
	}

	public function test_sanitize_valid_date_string() : void {
		$sanitized = Meta\sanitize_proposed_date( '2020-06-06T10:17:34' );
		$this->assertEquals( '2020-06-06 10:17:34', $sanitized );
	}

	public function test_sanitize_invalid_date_string() : void {
		$sanitized = Meta\sanitize_proposed_date( 'Diamonds on the soles of her shoes' );
		$this->assertEquals( '', $sanitized );
	}

	public function test_sanitize_unsanitary_string() : void {
		$sanitized = Meta\sanitize_proposed_date( "2020-06-06 %%%ABABAB 10:25:56 %A%A%ABBB" );
		$this->assertEquals( '2020-06-06 10:25:56', $sanitized );
	}

	public function test_get_proposed_date_no_meta_set() : void {
		$post_id = self::factory()->post->create();
		$date = Meta\get_proposed_date( $post_id );
		$this->assertNull( $date );
	}

	public function test_get_proposed_date_empty_string_set() : void {
		$post_id = self::factory()->post->create();
		update_post_meta( $post_id, Meta\PROPOSED_DATE_META_KEY, '' );
		$date = Meta\get_proposed_date( $post_id );
		$this->assertNull( $date );
	}

	public function test_get_proposed_date_meta() : void {
		$post_id = self::factory()->post->create();
		$this->assertInternalType(
			'integer',
			update_post_meta( $post_id, Meta\PROPOSED_DATE_META_KEY, '2020-06-06 10:47' )
		);
		$this->assertEquals(
			'',
			get_post_meta( $post_id, Meta\PROPOSED_DATE_META_KEY, true )
		);
		$date = Meta\get_proposed_date( $post_id );
		$this->assertEquals( '2020-06-06 10:47', $date );
	}
}
