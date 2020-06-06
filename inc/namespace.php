<?php
/**
 * Orchestrate the main logic of the plugin.
 *
 * @package propose-draft-date
 */

declare( strict_types=1 );

namespace ProposeDraftDate;

use WP_Post;

/**
 * Connect namespace functions to actions & hooks.
 */
function setup() : void {
	add_filter( 'get_the_date', __NAMESPACE__ . '\\filter_get_the_date', 10, 3 );
	add_filter( 'the_date', __NAMESPACE__ . '\\filter_the_date', 10, 2 );
	add_filter( 'get_post_time', __NAMESPACE__ . '\\filter_get_post_time', 10, 2 );
	add_action( 'transition_post_status', __NAMESPACE__ . '\\promote_proposed_date_on_publish' );
}

/**
 * If a post is not yet published and has a proposed date set in its post meta,
 * return that proposed date in place of the regular date string.
 *
 * @param string      $the_date The formatted date.
 * @param string      $format   PHP date format. Defaults to 'date_format' option
 *                              if not specified.
 * @param int|WP_Post $post     The post object or ID.
 *
 * @return string Filtered post date.
 */
function filter_get_the_date( string $the_date, string $format, $post ) : string {
	$proposed_date = Meta\get_proposed_date( $post );
	if ( ! empty( $proposed_date ) ) {
		return wp_date( $format, strtotime( $proposed_date ) );
	}

	return $the_date;
}

/**
 * Undocumented function
 *
 * @param string $the_date The formatted date string.
 * @param string $format   PHP date format. Defaults to 'date_format' option
 *                         if not specified.
 *
 * @return string
 */
function filter_the_date( $the_date, $format ) : string {
	return filter_get_the_date( $the_date, $format, get_post() );
}

/**
 * Filters the localized time a post was written.
 *
 * @since 2.6.0
 *
 * @param string $time   The formatted time.
 * @param string $format Format to use for retrieving the time the post was written.
 *                       Accepts 'G', 'U', or PHP date format. Default 'U'.
 * @param bool   $gmt    Whether to retrieve the GMT time. Default false.
 */
function filter_get_post_time( $time, $format ) : string {
	return filter_get_the_date( $time, $format, get_post() );
}

/**
 * If a post with a proposed date is published, conditionally promote the
 * proposed date to be the actual date of the post.
 *
 * @param string $new_status New post status.
 * @param string $old_status Previous post status.
 * @param WP_Post $post
 *
 * @return void
 */
function promote_proposed_date_on_publish( string $new_status, string $old_status, WP_Post $post ) : void {
	// Heuristic check to determine if a post is becoming scheduled in some way.
	$accept_proposal = (
		! in_array( $old_status, [ 'publish', 'private', 'future' ], true )
	) && (
		in_array( $new_status, [ 'publish', 'private', 'future' ], true )
	);

	/**
	 * Filter whether a proposed date should be applied to a transitioning post
	 * if a date proposal is available.
	 *
	 * @param bool    $accept_proposal Whether, given the situation, a proposal should be accepted.
   * @param string  $new_status      New post status.
   * @param string  $old_status      Previous post status.
	 * @param WP_Post $post            The post being transitioned.
	 */
	$accept_proposal = apply_filters( 'proposed_date_should_apply_proposal', $accept_proposal, $new_status, $old_status, $post );

	if ( ! $accept_proposal ) {
		return;
	}

	$proposed_date = Meta\get_proposed_date( $post );
	if ( empty( $proposed_date ) ) {
		return;
	}

	// Apply the proposed date to the post.

	// Remove meta from updated post.
	delete_post_meta( $post->ID, Meta\PROPOSED_DATE_META_KEY );
}
