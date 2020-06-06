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
	isFloating: boolean;
	proposedDate?: string;
}> = ( { date, isFloating, proposedDate } ) => {
	const { dateFormat } = useExperimentalSettings( ( settings ) => ( {
		dateFormat: `${ settings.formats.date } ${ settings.formats.time }`,
	} ) );

	if ( date && ! isFloating ) {
		return dateI18n( dateFormat, date );
	}

	if ( isFloating && proposedDate ) {
		return dateI18n( dateFormat, proposedDate );
	}

	return __( 'Immediately' );
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
			isFloating={ isFloating }
			proposedDate={ proposedDate }
		/>
	);
}
