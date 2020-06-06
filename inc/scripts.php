<?php
/**
 * Register editor JS script.
 */
declare( strict_types=1 );

namespace ProposeDraftDate\Scripts;

use ProposeDraftDate\AssetLoader;

const EDITOR_BUNDLE_HANDLE = 'propose-draft-date';

/**
 * Connect namespace functions to actions & hooks.
 */
function setup() : void {
	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueueBlockEditorAssets' );
}

/**
 * Return the expected path of the asset-manifest.json file.
 *
 * @return string
 */
function manifest_file_path() : string {
	$pluginPath = trailingslashit( plugin_dir_path( dirname( __FILE__ ) ) );
	return $pluginPath . 'build/asset-manifest.json';
}

/**
 * Enqueue editor-only assets based on the generated `asset-manifest.json` file.
 */
function enqueueBlockEditorAssets() : void {
	AssetLoader\enqueue_asset(
		manifest_file_path(),
		'propose-draft-date.js',
		[
			'handle'  => EDITOR_BUNDLE_HANDLE,
			'scripts' => [
				'wp-blocks',
				'wp-components',
				'wp-compose',
				'wp-data',
				'wp-edit-post',
				'wp-editor',
				'wp-element',
				'wp-i18n',
				'wp-plugins',
				'wp-rich-text',
				'wp-shortcode',
				'wp-url',
			],
		]
	);
}
