<?php
/**
 * Plugin Name: Propose Draft Date
 * Description: Permit contributing authors to suggest a date for a draft post which takes effect on publish.
 * Version: 0.3.2
 * Author: Human Made
 * Author URI: https://www.humanmade.com
 * License:     GPL-2.0+
 */
declare( strict_types=1 );

namespace ProposeDraftDate;

// Register custom meta values.
require_once __DIR__ . '/inc/meta.php';
Meta\setup();

// Load scripts & styles.
require_once __DIR__ . '/inc/asset-loader.php';
require_once __DIR__ . '/inc/scripts.php';
Scripts\setup();

//Set up the meta-to-date handoff logic.
require_once __DIR__ . '/inc/namespace.php';
setup();

// Customize the display of the dates within the site.
require_once __DIR__ . '/inc/date-display.php';
Date_Display\setup();
