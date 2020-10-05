/**
 * External dependencies.
 */
import React, { FC } from 'react';

/**
 * WordPress dependencies.
 */
import { useSelect } from '@wordpress/data';

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
	if ( isPublished || hasPublishAction || ! [ 'draft', 'future' ].includes( postStatus ) ) {
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
