<?php
/**
 * Register and enqueue files declared in JSON webpack asset manifests.
 *
 * @package propose-draft-date
 */

// We aren't getting remote files, disable warning preferring wp_remote_get.
// phpcs:disable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

declare( strict_types=1 );

namespace ProposeDraftDate\AssetLoader;

/**
 * Attempt to load a file at the specified path and parse its contents as JSON.
 *
 * @param string $path The path to the JSON file to load.
 *
 * @return array|null
 */
function load_asset_manifest( $path ) : ?array {
	// Avoid repeatedly opening & decoding the same file.
	static $manifests = [];

	if ( isset( $manifests[ $path ] ) ) {
		return $manifests[ $path ];
	}

	if ( ! file_exists( $path ) ) {
		// Check one level up for a committed manifest file.
		$manifest_file = basename( $path );
		$path = dirname( $path, 2 ) . '/' . $manifest_file;

		// Fail out if that manifest does not exist either.
		if ( ! file_exists( $path ) ) {
			return null;
		}
	}

	$contents = file_get_contents( $path );

	if ( empty( $contents ) ) {
		return null;
	}

	$manifests[ $path ] = json_decode( $contents, true );

	return $manifests[ $path ];
}

/**
 * Attempt to extract a specific value from an asset manifest file.
 *
 * @param string $manifest_path File system path for an asset manifest JSON file.
 * @param string $asset        Asset to retrieve within the specified manifest.
 *
 * @return string|null
 */
function get_manifest_resource( string $manifest_path, string $asset ) : ?string {
	$dev_assets = load_asset_manifest( $manifest_path );

	if ( ! isset( $dev_assets[ $asset ] ) ) {
		return null;
	}

	return $dev_assets[ $asset ];
}

/**
 * Helper function to naively check whether or not a given URI is a CSS resource.
 *
 * @param string $uri A URI to test for CSS-ness.
 * @return boolean Whether that URI points to a CSS file.
 */
function is_css( string $uri ) : bool {
	return preg_match( '/\.css(\?.*)?$/', $uri ) === 1;
}

/**
 * Attempt to register a particular script bundle from a manifest.
 *
 * @param string $manifest_path File system path for an asset manifest JSON file.
 * @param string $target_asset  Asset to retrieve within the specified manifest.
 * @param array  $options {
 *     @type string $handle  Handle to use when enqueuing the style/script bundle.
 *                           Required.
 *     @type array  $scripts Script dependencies. Optional.
 *     @type array  $styles  Style dependencies. Optional.
 * }
 */
function register_asset( string $manifest_path, string $target_asset, array $options = [] ) : void {
	$defaults = [
		'scripts' => [],
		'styles' => [],
	];
	$options = wp_parse_args( $options, $defaults );

	$asset_uri = get_manifest_resource( $manifest_path, $target_asset );

	error_log( 'asset uri: ' . (string) $asset_uri );

	if ( empty( $asset_uri ) ) {
		// TODO: Consider warning in the console if the asset could not be found.
		// (Failure should be allowed for CSS files; they are not exported in dev.)
		return;
	}

	// Reconcile static asset builds relative to the plugin directory.
	if ( strpos( $asset_uri, '//' ) === false ) {
		$asset_uri = trailingslashit( plugin_dir_url( __DIR__ ) ) . $asset_uri;
	}

	error_log( $asset_uri );

	if ( is_css( $asset_uri ) ) {
		wp_register_style(
			$options['handle'],
			$asset_uri,
			$options['styles'],
			$asset_uri
		);
	} else {
		wp_register_script(
			$options['handle'],
			$asset_uri,
			$options['scripts'],
			$asset_uri,
			true
		);
	}
}

/**
 * Attempt to register and then enqueue a particular script bundle from a manifest.
 *
 * @param string $manifest_path File system path for an asset manifest JSON file.
 * @param string $target_asset  Asset to retrieve within the specified manifest.
 * @param array  $options {
 *     @type string $handle  Handle to use when enqueuing the style/script bundle.
 *                           Required.
 *     @type array  $scripts Script dependencies. Optional.
 *     @type array  $styles  Style dependencies. Optional.
 * }
 */
function enqueue_asset( string $manifest_path, string $target_asset, array $options = [] ) : void {
	register_asset( $manifest_path, $target_asset, $options );

	// $target_asset will share a filename extension with the enqueued asset.
	if ( is_css( $target_asset ) ) {
		wp_enqueue_style( $options['handle'] );
	} else {
		wp_enqueue_script( $options['handle'] );
	}
}
