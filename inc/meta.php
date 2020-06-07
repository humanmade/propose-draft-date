<?php
/**
 * Register, get and set the meta value for storing proposed date string.
 *
 * @package propose-draft-date
 */

declare( strict_types=1 );

namespace ProposeDraftDate\Meta;

use ProposeDraftDate;

const PROPOSED_DATE_META_KEY = 'proposed_publish_date';

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
function allow_proposed_date() : bool {
	return current_user_can( 'edit_posts' );
}

/**
 * Sanitize a string and cast it to a date, or return an empty string if it
 * cannot be parsed as a date string.
 *
 * @param string $input Incoming unsanitized meta value.
 *
 * @return string MySQL-format date string, or empty string.
 */
function sanitize_proposed_date( string $input ) : string {
	$date_data = rest_get_date_with_gmt( trim( sanitize_text_field( $input ) ) );

	if ( empty( $date_data ) ) {
		return '';
	}

	return $date_data[0];
}

/**
 * Helper function to get the proposed date for a given post.
 *
 * Returns null if post is scheduled or published, or if no date has been proposed.
 *
 * @param int|WP_Post $post The post object or ID for which to get the proposed date.
 *
 * @return string|null The proposed date, or null if it is irrelevant or unavailable.
 */
function get_proposed_date( $post ) : ?string {
	if ( in_array( get_post_status( $post ), [ 'publish', 'future' ], true ) ) {
		return null;
	}

	$post_id = $post->ID ?? $post;
	// return (string) $post_id;
	$proposed_date = (string) get_post_meta( $post_id, PROPOSED_DATE_META_KEY, true );

	if ( empty( trim( $proposed_date ) ) ) {
		return null;
	}

	return $proposed_date;
}

/**
 * Register meta values used to store block data.
 */
function register_meta(): void {
	$supported_post_types = ProposeDraftDate\get_supported_post_types();
	foreach ( $supported_post_types as $post_type ) {
		register_post_meta(
			$post_type,
			PROPOSED_DATE_META_KEY,
			[
				'description'       => __( 'A proposed date on which to publish a post type object.', 'propose-draft-date' ),
				'single'            => true,
				'type'              => 'string',
				'show_in_rest'      => true,
				'sanitize_callback' => __NAMESPACE__ . '\\sanitize_proposed_date',
				'auth_callback'     => __NAMESPACE__ . '\\allow_proposed_date',
			]
		);
	}
}
