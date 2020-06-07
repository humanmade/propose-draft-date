<?php
/**
 * Orchestrate the main logic of the plugin.
 *
 * @package propose-draft-date
 */

declare( strict_types=1 );

namespace ProposeDraftDate;

/**
 * Connect namespace functions to actions & hooks.
 */
function setup() : void {
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
 * Helper method to determine whether a given post status is considered to have
 * a "floating" publication date (i.e. whether it would publish "immediately").
 *
 * @param string $post_status Status to check for floatiness.
 * @return bool Whether the provided status has a floating date.
 */
function status_has_floating_date( string $post_status ) : bool {
	return in_array( $post_status, get_post_stati( [ 'date_floating' => true ] ), true );
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
	// Skip all not-yet-saved posts (which cannot have meta yet), and skip any
	// post that does not support the proposed date meta field.
	if ( empty( $post_id ) || ! is_post_type_supported( $data['post_type'] ) ) {
		return $data;
	}

	$proposed_date = Meta\get_proposed_date( $post_id );

	// If there is no pending date, we have nothing further to do.
	if ( empty( $proposed_date ) ) {
		return $data;
	}

	$existing_post = get_post( $post_id );
	$old_status = $existing_post->post_status;

	// Determine whether the circumstances warrant applying the proposed date.
	$accept_proposal = (
		// Do not override date on a post that already had an explicit date.
		status_has_floating_date( $old_status )
	) && (
		// No update is needed at this time if new status is still "date floating".
		! status_has_floating_date( $data['post_status'] )
	) && (
		// Post must not yet be scheduled.
		$existing_post->post_date_gmt === '0000-00-00 00:00:00'
	);

	/**
	 * Filter whether a proposed date should be applied to a post being saved in
	 * the database.
	 *
	 * @param bool    $accept_proposal Whether, given the situation, a proposal should be accepted.
	 * @param string  $new_status      New post status.
	 * @param string  $old_status      Previous post status.
	 * @param WP_Post $post            The post being updated as it exists prior to the update.
	 */
	$accept_proposal = apply_filters( 'proposed_date_should_apply_proposal', $accept_proposal, $data['post_status'], $old_status, $existing_post );

	if ( ! $accept_proposal ) {
		return $data;
	}

	// Validate the proposed date. This logic is duplicated from wp_insert_post.
	$mm         = substr( $proposed_date, 5, 2 );
	$jj         = substr( $proposed_date, 8, 2 );
	$aa         = substr( $proposed_date, 0, 4 );
	$valid_date = wp_checkdate( $mm, $jj, $aa, $proposed_date );
	if ( ! $valid_date ) {
		trigger_error(
			sprintf(
				'did not apply invalid proposed date string "%s" for post %d',
				esc_attr( $proposed_date ),
				esc_attr( $post_id )
			)
		);
		return $data;
	}

	// Apply the validated proposal to the post.
	$data['post_date'] = $proposed_date;
	$data['post_date_gmt'] = get_gmt_from_date( $proposed_date );

	// Schedule meta to be removed from the updated post, once saved.
	add_action( 'edit_post', function( $updated_post_id ) use ( $post_id ) {
		if ( $updated_post_id === $post_id ) {
			delete_post_meta( $post_id, Meta\PROPOSED_DATE_META_KEY );
		}
	}, 10, 1 );

	return $data;
}
