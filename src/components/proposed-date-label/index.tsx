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
	 * Filters Floating Status of the post.
	 *
	 * @param {Boolean} isFloating Default Floating.
	 */
	isFloating = applyFilters( 'proposed.date.label.is.floating', isFloating );

	const { dateFormat } = useExperimentalSettings( ( settings ) => ( {
		dateFormat: `${ settings.formats.date } ${ settings.formats.time }`,
	} ) );
	const defaultLabel = __( 'Immediately' );
	const dateLabel = isFloating
		? ( proposedDate ? dateI18n( dateFormat, proposedDate ) : defaultLabel )
		: ( date ? dateI18n( dateFormat, date ) : defaultLabel );

	/**
	 * Filters Label for proposed Draft label.
	 *
	 * @param {String} dateLabel Proposed Date Label or string `Immediately`.
	 */
	return applyFilters( 'proposed.date.label.date.label', dateLabel );
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
