/**
 * External dependencies.
 */
import React from 'react';

/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { PanelRow, Dropdown, Button } from '@wordpress/components';

/**
 * Internal dependencies.
 */
import ProposedDateForm from '../proposed-date-form';
import ProposedDateLabel from '../proposed-date-label';

/**
 * Render the complete date-proposal form.
 */
export default function ProposeDatePanel() {
	return (
		<PanelRow className="edit-post-post-schedule">
			<span>{ __( 'Publish' ) }</span>
			<Dropdown
				position="bottom left"
				contentClassName="edit-post-post-schedule__dialog"
				renderToggle={ ( { onToggle, isOpen } ) => (
					<>
						<Button
							className="edit-post-post-schedule__toggle"
							onClick={ onToggle }
							aria-expanded={ isOpen }
							isLink
						>
							<ProposedDateLabel />
						</Button>
					</>
				) }
				renderContent={ () => <ProposedDateForm /> }
			/>
		</PanelRow>
	);
}
