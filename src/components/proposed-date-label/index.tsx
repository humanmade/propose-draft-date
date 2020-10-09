/**
 * External dependencies.
 */
import React, { FC } from 'react';

/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';
import { useSelect } from '@wordpress/data';
import { applyFilters } from '@wordpress/hooks';

/**
 * Internal dependencies.
 */
import useExperimentalSettings from '../../hooks/use-experimental-settings';
import useMeta from '../../hooks/use-meta';

/**
 * Functional component to render a proposed date label.
 */
export const ProposedDateLabel: FC<{
	date?: string;
	proposedDate?: string;
	isFloating: boolean;
}> = ( { date, proposedDate, isFloating } ) => {
	/**
	 * Permit overriding "floating date" status of the post with a filter.
	 *
	 * @param {Boolean} isFloating Post's original Floating status.
	 */
	const filteredIsFloating = applyFilters( 'proposed_date/is_floating', isFloating );

	const { dateFormat } = useExperimentalSettings( ( settings ) => ( {
		dateFormat: `${ settings.formats.date } ${ settings.formats.time }`,
	} ) );

	let dateLabel = __( 'Immediately' );
	if ( date && ! filteredIsFloating ) {
		dateLabel = dateI18n( dateFormat, date );
	} else if ( filteredIsFloating && proposedDate ) {
		dateLabel = dateI18n( dateFormat, proposedDate );
	}

	/**
	 * Filters the text which displays the proposed date in the Document sidebar.
	 *
	 * @param {String} dateLabel    The string to display when showing the date.
	 * @param {String} proposedDate Proposed date meta value, if present.
	 * @param {String} date         Current post date.
	 */
	return applyFilters( 'proposed_date/date_label', dateLabel, proposedDate, date );
};

/**
 * Render a label showing either the actual post date, if scheduled, the
 * proposed date, if present; or else show "Immediately".
 */
export default function<FC> () {
	const [ proposedDate ] = useMeta<string>( 'proposed_publish_date' );
	const { date, isFloating } = useSelect( ( select ) => ( {
		date: select( 'core/editor' ).getEditedPostAttribute( 'date' ),
		isFloating: select( 'core/editor' ).isEditedPostDateFloating(),
	} ) );

	return (
		<ProposedDateLabel
			date={ date }
			proposedDate={ proposedDate }
			isFloating={ isFloating }
		/>
	);
}
