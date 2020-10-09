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
	isFloating: boolean,
	postStatus: string;
	hasPublishAction: boolean;
	isPublished: boolean;
}

export const PostUnscheduledCheck: FC<PostUnscheduledCheckProps> = ( {
	children,
	hasPublishAction,
	isFloating,
	postStatus,
	isPublished,
} ) => {
	/**
	 * Filters Floating Status of the post.
	 *
	 * @param {Boolean} isFloating Default Floating.
	 */
	isFloating = applyFilters( 'proposed.date.unscheduled.is.floating', isFloating );

	if ( isPublished || hasPublishAction || ! isFloating ) {
		return null;
	}

	/**
	 * Filter whether the proposed date UI should be shown for a given post status.
	 *
	 * @param {String[]} statuses List of statuses supporting Proposed Date UI.
	 */
	const supportedStatuses = applyFilters( 'proposed.date.supported.statuses', [ 'auto-draft', 'draft', 'future' ] );
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
 * posts and the current post is not already scheduled or published.
 */
const ConnectedPostUnscheduledCheck: FC = ( { children } ) => {
	const props = useSelect( ( select ) => {
		const {
			getCurrentPost,
			isCurrentPostPublished,
			isEditedPostDateFloating,
			getEditedPostAttribute,
		} = select( 'core/editor' );
		return {
			hasPublishAction: Boolean( getCurrentPost()?._links?.['wp:action-publish'] ) || false,
			isFloating: isEditedPostDateFloating(),
			postStatus: getEditedPostAttribute( 'status' ),
			isPublished: isCurrentPostPublished(),
		};
	} );

	return (
		<PostUnscheduledCheck { ...props }>{ children }</PostUnscheduledCheck>
	);
};

export default ConnectedPostUnscheduledCheck;
