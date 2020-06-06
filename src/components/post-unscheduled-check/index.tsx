/**
 * External dependencies.
 */
import React, { FC } from 'react';

/**
 * WordPress dependencies.
 */
import { useSelect } from '@wordpress/data';

export interface PostUnscheduledCheckProps {
	isFloating: boolean;
	hasPublishAction: boolean;
	isPublished: boolean;
}

export const PostUnscheduledCheck: FC<PostUnscheduledCheckProps> = ( {
	children,
	hasPublishAction,
	isFloating,
	isPublished,
} ) => {
	if ( isPublished || hasPublishAction || ! isFloating ) {
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
		} = select( 'core/editor' );
		return {
			hasPublishAction: Boolean( getCurrentPost()?._links?.['wp:action-publish'] ) || false,
			isFloating: isEditedPostDateFloating(),
			isPublished: isCurrentPostPublished(),
		};
	} );

	return (
		<PostUnscheduledCheck { ...props }>{ children }</PostUnscheduledCheck>
	);
};

export default ConnectedPostUnscheduledCheck;
