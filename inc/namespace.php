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
	// add_action( 'transition_post_status', __NAMESPACE__ . '\\apply_proposed_date_on_publish', 10, 3 );
	add_filter( 'wp_insert_post_data', __NAMESPACE__ . '\\apply_proposed_date_before_insert', 10, 3 );
}

/**
 * Return a filtered list of post types supporting this meta value.
 *
 * @return array Array of post type names.
 */
function get_supported_post_types() : array {
	$default_post_types = [ 'post', 'page' ];
	/**
	 * Filter post types supporting the proposed date feature.
	 *
	 * @param array $post_types Array of post types supporting proposed dates. Defaults to "post" and "page".
	 */
	return apply_filters( 'proposed_date_supported_post_types', $default_post_types );
}

/**
 * Determine whether the proposed date feature can be used with a given post type.
 *
 * @param string $post_type Post type to check.
 *
 * @return boolean Whether this post type accepts a proposed date meta value.
 */
function is_post_type_supported( string $post_type ) : bool {
	return in_array( $post_type, get_supported_post_types(), true );
}

/**
 * Given an post date, format string, and source post, conditionally return
 * the proposed date from the post's meta values instead.
 *
 * @param int|string  $date     Formatted date. Can be an int if format is 'U'.
 * @param string      $format   PHP date format string.
 * @param int|WP_Post $post     The post object or ID.
 *
 * @return int|string Filtered post date.
 */
function maybe_return_meta_date( $date, string $format, $post ) {
	if ( ! is_post_type_supported( $post->post_type ) ) {
		return $date;
	}
	$proposed_date = Meta\get_proposed_date( $post );
	if ( ! empty( $proposed_date ) ) {
		return wp_date( $format, strtotime( $proposed_date ) );
	}

	return $date;
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
	return maybe_return_meta_date( $the_date, $format, $post );
}

/**
 * Filters the time a post was written.
 *
 * @param string      $the_time The formatted time.
 * @param string      $format   Format to use for retrieving the time the post was written.
 *                              Accepts 'G', 'U', or PHP date format value specified
 *                              in 'time_format' option. Default empty.
 * @param int|WP_Post $post     WP_Post object or ID.
 */
// return apply_filters( 'get_the_time', $the_time, $format, $post );

/**
 * Undocumented function
 *
 * @param string $the_date The formatted date string.
 * @param string $format   PHP date format. Defaults to 'date_format' option
 *                         if not specified.
 *
 * @return string
 */
function filter_the_date( string $the_date, string $format ) : string {
	return maybe_return_meta_date( $the_date, $format, get_post() );
}

/**
 * Filters the localized time a post was written.
 *
 * @todo This does not currently handle the $gmt parameter.
 *
 * @param int|string $time   The formatted time. Can be an int if format is 'U'.
 * @param string     $format Format to use for retrieving the time the post was written.
 *                           Accepts 'G', 'U', or PHP date format. Default 'U'.
 * @param bool       $gmt    Whether to retrieve the GMT time. Default false.
 *
 * @return int|string Filtered value.
 */
function filter_get_post_time( $time, string $format ) {
	return maybe_return_meta_date( $time, $format, get_post() );
}

/**
 * Helper method to determine whether a given post status is considered to have
 * a "floating" publication date (i.e. whether it would publish "immediately").
 *
 * @param string $post_status Status to check for floatiness.
 * @return bool Whether the provided status has a floating date.
 */
function status_has_floating_date( string $post_status ) : bool {
	return in_array( $post_status, get_post_stati( array( 'date_floating' => true ) ), true );
}

/**
 * Filters slashed post data just before it is inserted into the database.
 *
 *
 * @param array $data                An array of slashed, sanitized, and processed post data.
 * @param array $postarr             An array of sanitized (and slashed) but otherwise unmodified post data.
 * @param array $unsanitized_postarr An array of slashed yet *unsanitized* and unprocessed post data as
 *                                   originally passed to wp_insert_post().
 *
 * @return array Filtered slashed, sanitized, and processed post data.
 */
function apply_proposed_date_before_insert( array $data, $postarr, $unsanitized_postarr ) : array {
	$post_id = $postarr['ID'] ?? '';
	// error_log( 'wp_insert_post_data: ' . print_r( $data, true ) );
	// error_log( (string) $data['ID'] ?? 'empty' );
	// error_log( (string) $postarr['ID'] );
	// Skip all not-yet-saved posts (which cannot have meta yet), and skip any
	// post that does not support the proposed date meta field.
	if ( empty( $post_id ) || ! is_post_type_supported( $data['post_type'] ) ) {
		return $data;
	}

	error_log( print_r( $data, true ) );

	$proposed_date = Meta\get_proposed_date( $post_id );

	// If there is no pending date, we have nothing further to do.
	if ( empty( $proposed_date ) ) {
		return $data;
	}

	$existing_post = get_post( $post_id );
	$old_status = $existing_post->post_status;

	error_log( 'Existing post: ' . print_r( [
		'post_date' => $existing_post->post_date,
		'post_date_gmt' => $existing_post->post_date_gmt,
	], true ) );

	// Determine whether, given the existing and incoming post data, we should
	// apply the pending date.
	$accept_proposal = (
		// If previous status was not floating-date, do not override the date.
		status_has_floating_date( $old_status )
	) && (
		// No update is needed at this time if new status is still floating-date.
		! status_has_floating_date( $data['post_status'] )
	) && (
		// Post must not be explicitly scheduled.
		$existing_post->post_date_gmt === '0000-00-00 00:00:00'
	);

	/**
	 * Filter whether a proposed date should be applied to a post being saved in
	 * the database (providing that a date proposal is available).
	 *
	 * @param bool    $accept_proposal Whether, given the situation, a proposal should be accepted.
	 * @param string  $new_status      New post status.
	 * @param string  $old_status      Previous post status.
	 * @param WP_Post $post            The post being updated as it exists prior to the update.
	 */
	$accept_proposal = apply_filters( 'proposed_date_should_apply_proposal', $accept_proposal, $data['post_status'], $old_status, $existing_post );

	error_log( 'Filtered accept_proposal: ' . ( $accept_proposal ? 'true' : 'false' ) );

	if ( ! $accept_proposal ) {
		return $data;
	}

	// This logic duplicated from wp_insert_post.
	// Validate the proposed date.
	$mm         = substr( $proposed_date, 5, 2 );
	$jj         = substr( $proposed_date, 8, 2 );
	$aa         = substr( $proposed_date, 0, 4 );
	$valid_date = wp_checkdate( $mm, $jj, $aa, $proposed_date );
	if ( ! $valid_date ) {
		trigger_error(
			sprintf(
				'did not apply invalid proposed date string "%s" for post %d',
				$proposed_date,
				$post_id
			)
		);
		return $data;
	}
	$data['post_date'] = $proposed_date;
	$data['post_date_gmt'] = get_gmt_from_date( $proposed_date );

	// Schedule meta to be removed from the updated post, once saved.
	add_action( 'edit_post', function( $updated_post_id, $post ) use ( $post_id ) {
		if ( $updated_post_id !== $post_id ) {
			return;
		}
		delete_post_meta( $post_id, Meta\PROPOSED_DATE_META_KEY );
	}, 10, 2 );

	return $data;
}

/**
 * If a post with a proposed date is published, conditionally promote the
 * proposed date to be the actual date of the post.
 *
 * @param string $new_status New post status.
 * @param string $old_status Previous post status.
 * @param WP_Post $post
 *
 * @return int|void Returns are for testing purposes only.
 */
function apply_proposed_date_on_publish( string $new_status, string $old_status, WP_Post $post ) : void {
	if ( ! is_post_type_supported( $post->post_type ) ) {
		return;
	}

	error_log( 'Transitioning  ' . $post->post_title . ' from ' . $old_status . ' to ' . $new_status );

	// Heuristic check to determine if a post is becoming scheduled in some way.
	$accept_proposal = (
		! in_array( $old_status, [ 'publish', 'private', 'future' ], true )
	) && (
		in_array( $new_status, [ 'publish', 'private', 'future' ], true )
	);
	error_log( 'a: ' . ( $accept_proposal  ? 'true' : 'false' ) );

	error_log( print_r( $post, true ) );

	// A proposed date should never override an explicitly-set date.
	if ( $post->post_date_gmt !== '0000-00-00 00:00:00' ) {
		$accept_proposal = false;
	}
	error_log( 'b: ' . ( $accept_proposal  ? 'true' : 'false' ) );

	/**
	 * Filter whether a proposed date should be applied to a transitioning post
	 * (if a date proposal is available).
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
	error_log( 'c: ' . $proposed_date );
	if ( empty( $proposed_date ) ) {
		return;
	}

	$date_data = rest_get_date_with_gmt( $proposed_date );
	if ( empty( $date_data ) ) {
		return;
	}

	// Apply the proposed date to the post.
	list( $date, $date_gmt ) = $date_data;
	error_log( 'dates: ' . $date . '; ' . $date_gmt );

	global $wpdb;
	$wpdb->update(
		$wpdb->posts,
		[
			'post_date'     => $date,
			'post_date_gmt' => $date_gmt,
		],
		[ 'ID' => $post->ID ]
	);

	// Remove meta from updated post.
	delete_post_meta( $post->ID, Meta\PROPOSED_DATE_META_KEY );
}
