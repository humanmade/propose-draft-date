<?php
/**
 * Set up the testing framework.
 *
 * @package propose-draft-date
 */

declare( strict_types=1 );

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Now call the bootstrap method of WP Mock.
WP_Mock::setUsePatchwork( true );
WP_Mock::bootstrap();

// Load in our custom files here, soon.
// Load the base class for endpoint tests.
require_once __DIR__ . '/inc/api/class-endpoint-test.php';
