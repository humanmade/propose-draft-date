import React from 'react';
import {
	cleanup,
	render,
	RenderResult,
} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import { addFilter } from '@wordpress/hooks';

import {
	PostUnscheduledCheck,
	PostUnscheduledCheckProps,
} from './index';

const snapshotDidNotRender = '<div />';
const shapshotRendered = `
<div>
  <p>
    Wrapped Content
  </p>
</div>
`;

describe( 'PostUnscheduledCheck', () => {
	let renderWithProps: (
		props: PostUnscheduledCheckProps,
	) => RenderResult;

	beforeEach( () => {
		renderWithProps = ( props ) => render( (
			<PostUnscheduledCheck { ...props }>
				<p>Wrapped Content</p>
			</PostUnscheduledCheck>
		) );
	} );

	afterEach( cleanup );

	it( 'returns null if a post does not have a "floating" date', () => {
		const { container, queryByText } = renderWithProps( {
			isFloating: false,
			postStatus: 'draft',
			hasPublishAction: false,
			isPublished: false,
		} );
		expect( container ).toMatchInlineSnapshot( snapshotDidNotRender );
		expect( queryByText( 'Wrapped Content' ) ).toBeNull();
	} );

	it( 'returns null if a post does not have a supported status', () => {
		const { container, queryByText } = renderWithProps( {
			isFloating: true,
			postStatus: 'not-draft',
			hasPublishAction: false,
			isPublished: false,
		} );
		expect( container ).toMatchInlineSnapshot( snapshotDidNotRender );
		expect( queryByText( 'Wrapped Content' ) ).toBeNull();
	} );

	it( 'returns null if the user is capable of publishing posts', () => {
		const { container, queryByText } = renderWithProps( {
			isFloating: true,
			postStatus: 'draft',
			hasPublishAction: true,
			isPublished: false,
		} );
		expect( container ).toMatchInlineSnapshot( snapshotDidNotRender );
		expect( queryByText( 'Wrapped Content' ) ).toBeNull();
	} );

	it( 'returns null if isPublished is true', () => {
		const { container, queryByText } = renderWithProps( {
			isFloating: true,
			postStatus: 'draft',
			hasPublishAction: false,
			isPublished: true,
		} );
		expect( container ).toMatchInlineSnapshot( snapshotDidNotRender );
		expect( queryByText( 'Wrapped Content' ) ).toBeNull();
	} );

	it( 'returns children if all conditions are satisfied when post status is auto-draft', () => {
		const { container, queryByText } = renderWithProps( {
			isFloating: true,
			postStatus: 'auto-draft',
			hasPublishAction: false,
			isPublished: false,
		} );
		expect( container ).toMatchInlineSnapshot( shapshotRendered );
		expect( queryByText( 'Wrapped Content' ) ).toBeInTheDocument();
	} );

	it( 'returns children if all conditions are satisfied when post status is draft', () => {
		const { container, queryByText } = renderWithProps( {
			isFloating: true,
			postStatus: 'draft',
			hasPublishAction: false,
			isPublished: false,
		} );
		expect( container ).toMatchInlineSnapshot( shapshotRendered );
		expect( queryByText( 'Wrapped Content' ) ).toBeInTheDocument();
	} );

	it( 'returns children if all conditions are satisfied when post status is future', () => {
		const { container, queryByText } = renderWithProps( {
			isFloating: true,
			postStatus: 'future',
			hasPublishAction: false,
			isPublished: false,
		} );
		expect( container ).toMatchInlineSnapshot( shapshotRendered );
		expect( queryByText( 'Wrapped Content' ) ).toBeInTheDocument();
	} );

	it( 'permits "floating" status to be set by filter', () => {
		addFilter( 'proposed_date.is_floating', 'jest', () => true );
		const { container, queryByText } = renderWithProps( {
			isFloating: false,
			postStatus: 'draft',
			hasPublishAction: false,
			isPublished: false,
		} );
		expect( container ).toMatchInlineSnapshot( shapshotRendered );
		expect( queryByText( 'Wrapped Content' ) ).toBeInTheDocument();
	} );
} );
