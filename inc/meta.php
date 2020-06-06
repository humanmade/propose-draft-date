<?php
/**
 * Define plugin-managed meta values.
 *
 * @package propose-draft-date
 */

declare( strict_types=1 );

namespace ProposeDraftDate\Meta;

/**
 * Connect namespace functions to actions & hooks.
 */
function setup() : void {
		add_action( 'init', __NAMESPACE__ . '\\register_meta' );
}

/**
 * Authorize any user who can edit posts (e.g. a Contributor) to set a
 * proposed publish date.
 *
 * @return bool Whether the current user can set the proposed date meta value.
 */
function propose_date_auth_callback() : bool {
	return current_user_can( 'edit_posts' );
}

/**
 * Sanitize a string and cast it to a UTC date, or return an empty string if
 * it cannot be parsed as a date string.
 *
 * @param string $input Incoming unsanitized meta value.
 *
 * @return string UTC date string, or empty string.
 */
function sanitize_date_string( string $input ) : string {
	$input = trim( sanitize_text_field( $input ) );

	if ( empty( $input ) ) {
		return '';
	}

	return get_gmt_from_date( $input );
}

/**
 * Register meta values used to store block data.
 */
function register_meta(): void {
	$default_post_types = [ 'post', 'page' ];
	$supported_post_types = apply_filters( 'proposed_date_supported_post_types', $default_post_types );
	foreach ( $supported_post_types as $post_type ) {
		register_post_meta(
			$post_type,
			'proposed_publish_date',
			[
				'description' => __( 'A proposed date on which to publish a post type object.', 'propose-draft-date' ),
				'single' => true,
				'type' => 'string',
				'show_in_rest' => true,
				'sanitize_callback' => __NAMESPACE__ . '\\sanitize_date_string',
				'auth_callback' => __NAMESPACE__ . '\\propose_date_auth_callback',
			]
		);
	}
}
