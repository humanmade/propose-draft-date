<?php
/**
 * PHPUnit "hello world".
 *
 * @package propose-draft-date
 */

declare( strict_types=1 );

namespace ProposeDraftDate\Tests\Inc;

use WP_Mock;

/**
 * Verify the test suite is running properly.
 */
class Test_Test_Suite extends WP_Mock\Tools\TestCase {
	public function setUp() : void {
		WP_Mock::setUp();
	}

	public function tearDown() : void {
		WP_Mock::tearDown();
	}

	public function testTestsAreRunning() : void {
		$this->assertTrue( true );
	}
}
