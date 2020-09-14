/**
 * External dependencies.
 */
import React, { FC, useCallback } from 'react';

/**
 * WordPress dependencies.
 */
import { DateTimePicker } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies.
 */
import useExperimentalSettings from '../../hooks/use-experimental-settings';
import useMeta from '../../hooks/use-meta';

/**
 * Render a date picker for a proposed date that calls the provided update
 * callback when called.
 *
 * (Adapted from Gutenberg's date component.)
 */
export const PostSchedule: FC<{
	date?: string;
	is12HourTime: boolean;
	onChangeDate: ( date: string ) => void;
}> = ( {
	date,
	onChangeDate,
	is12HourTime = false,
} ) => {
	const onChange = useCallback( ( newDate: string ) => {
		onChangeDate( newDate );
		if ( document?.activeElement ) {
			( document.activeElement as HTMLElement ).blur();
		}
	}, [ onChangeDate ] );

	return (
		<DateTimePicker
			key="date-time-picker"
			currentDate={ date }
			onChange={ onChange }
			is12Hour={ is12HourTime }
		/>
	);
};

/**
 * Render a date picker that sets a meta key for a proposed date when changed.
 * If a date is already present, do nothing.
 */
const PostScheduleWrapper: FC = ( { children } ) => {
	const [ proposedDate, updateProposedDate ] = useMeta<string>( 'proposed_publish_date' );

	// To know if the current timezone is a 12 hour time with look for "a" in the time format
	// We also make sure this a is not escaped by a "/"
	const { is12HourTime } = useExperimentalSettings( ( settings ) => ( {
		is12HourTime: /a(?!\\)/i.test(
			settings.formats.time
				.toLowerCase() // Test only the lower case a.
				.replace( /\\\\/g, '' ) // Replace "//" with empty strings.
				.split( '' )
				.reverse()
				.join( '' ), // Reverse the string and test for "a" not followed by a slash.
		),
	} ) );

	let date = useSelect( ( select ) => select( 'core/editor' ).getEditedPostAttribute( 'date' ) );
	date = proposedDate && proposedDate;

	return (
		<PostSchedule
			date={ date }
			is12HourTime={ is12HourTime }
			onChangeDate={ updateProposedDate }
		>
			{ children }
		</PostSchedule>
	);
};

export default PostScheduleWrapper;
