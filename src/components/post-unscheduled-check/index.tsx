/**
 * External dependencies.
 */
import React, { FC } from 'react';

/**
 * WordPress dependencies.
 */
import { useSelect } from '@wordpress/data';
import { applyFilters } from '@wordpress/hooks';

export interface PostUnscheduledCheckProps {
	postStatus: string;
	hasPublishAction: boolean;
	isPublished: boolean;
}

export const PostUnscheduledCheck: FC<PostUnscheduledCheckProps> = ( {
	children,
	hasPublishAction,
	postStatus,
	isPublished,
} ) => {
	if ( isPublished || hasPublishAction ) {
		return null;
	}

	/**
	 * Filter whether the proposed date UI should be shown for a given post status.
	 *
	 * @param {String[]} statuses List of statuses supporting Proposed Date UI.
	 */
	const supportedStatuses = applyFilters( 'proposed_date_supported_statuses', [ 'auto-draft', 'draft', 'future' ] );
	if ( ! supportedStatuses.includes( postStatus ) ) {
		return null;
	}

	return (
		<>
			{ children }
		</>
	);
};

/**
 * Wrapper component that will only render if the current user cannot schedule
 * posts or published.
 */
const ConnectedPostUnscheduledCheck: FC = ( { children } ) => {
	const props = useSelect( ( select ) => {
		const {
			getCurrentPost,
			isCurrentPostPublished,
			getEditedPostAttribute,
		} = select( 'core/editor' );
		return {
			hasPublishAction: Boolean( getCurrentPost()?._links?.['wp:action-publish'] ) || false,
			postStatus: getEditedPostAttribute( 'status' ),
			isPublished: isCurrentPostPublished(),
		};
	} );

	return (
		<PostUnscheduledCheck { ...props }>{ children }</PostUnscheduledCheck>
	);
};

export default ConnectedPostUnscheduledCheck;
