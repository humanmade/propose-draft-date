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
	proposedDate?: string;
}> = ( { date, proposedDate } ) => {
	const { dateFormat } = useExperimentalSettings( ( settings ) => ( {
		dateFormat: `${ settings.formats.date } ${ settings.formats.time }`,
	} ) );

	if ( proposedDate ) {
		return dateI18n( dateFormat, proposedDate );
	}

	if ( date ) {
		return dateI18n( dateFormat, date );
	}

	return __( 'Immediately' );
};

/**
 * Render a label showing either the actual post date, if scheduled, the
 * proposed date, if present; or else show "Immediately".
 */
export default function<FC> () {
	const [ proposedDate ] = useMeta<string>( 'proposed_publish_date' );
	const { date, postStatus } = useSelect( ( select ) => ( {
		date: select( 'core/editor' ).getEditedPostAttribute( 'date' )
	} ) );

	return (
		<ProposedDateLabel
			date={ date }
			proposedDate={ proposedDate }
		/>
	);
}
