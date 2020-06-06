<?php
/**
 * Set up the testing framework.
 *
 * @package propose-draft-date
 */

declare( strict_types=1 );

// Composer autoloader must be loaded before WP_PHPUNIT__DIR will be available.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Give access to tests_add_filter() function.
require '/wp-phpunit/includes/functions.php';

// Load in our custom files.
require dirname( dirname( __FILE__ ) ) . '/inc/meta.php';

// Start up the WP testing environment.
require '/wp-phpunit/includes/bootstrap.php';

