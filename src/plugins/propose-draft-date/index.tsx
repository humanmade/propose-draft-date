/**
 * External dependencies.
 */
import React from 'react';

/**
 * WordPress dependencies.
 */
import { PluginPostStatusInfo } from '@wordpress/edit-post';

/**
 * Internal dependencies.
 */
import PostUnscheduledCheck from '../../components/post-unscheduled-check';
import ProposeDatePanel from '../../components/proposed-date-panel';

export const name = 'propose-draft-date';

export const settings = {
	render: function () {
		return (
			<PluginPostStatusInfo>
				<PostUnscheduledCheck>
					<ProposeDatePanel />
				</PostUnscheduledCheck>
			</PluginPostStatusInfo>
		);
	},
};
