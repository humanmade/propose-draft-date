<?php
/**
 * Control the output of the date to inject the proposal where appropriate.
 *
 * @package propose-draft-date
 */

declare( strict_types=1 );

namespace ProposeDraftDate\Date_Display;

use ProposeDraftDate;
use ProposeDraftDate\Meta;

/**
 * Connect namespace functions to actions & hooks.
 */
function setup() : void {
	add_filter( 'get_the_date', __NAMESPACE__ . '\\filter_get_the_date', 10, 3 );
	add_filter( 'the_date', __NAMESPACE__ . '\\filter_the_date', 10, 2 );
	add_filter( 'get_post_time', __NAMESPACE__ . '\\filter_get_post_time', 10, 2 );
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
	if ( ! ProposeDraftDate\is_post_type_supported( $post->post_type ) ) {
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
