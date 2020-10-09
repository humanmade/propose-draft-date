<?php
/**
 * Set up the testing framework.
 *
 * @package propose-draft-date
 */

declare( strict_types=1 );

$base_dir = dirname( __DIR__ );

// Composer autoloader must be loaded before WP_PHPUNIT__DIR will be available.
require_once $base_dir . '/vendor/autoload.php';

// Give access to tests_add_filter() function.
require '/wp-phpunit/includes/functions.php';

// Load in our custom files.
require $base_dir . '/inc/meta.php';
require $base_dir . '/inc/namespace.php';

// Start up the WP testing environment.
require '/wp-phpunit/includes/bootstrap.php';
