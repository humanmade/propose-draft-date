import React from 'react';
import {
	cleanup,
	render,
	RenderResult,
} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';

import {
	PostUnscheduledCheck,
	PostUnscheduledCheckProps,
} from './index';

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

	it( 'returns null if a post doe not have a "floating" date', () => {
		const { container, queryByText } = renderWithProps( {
			isFloating: false,
			hasPublishAction: false,
			isPublished: false,
		} );
		expect( container ).toMatchInlineSnapshot( '<div />' );
		expect( queryByText( 'Wrapped Content' ) ).toBeNull();
	} );

	it( 'returns null if the user is capable of publishing posts', () => {
		const { container, queryByText } = renderWithProps( {
			isFloating: true,
			hasPublishAction: true,
			isPublished: false,
		} );
		expect( container ).toMatchInlineSnapshot( '<div />' );
		expect( queryByText( 'Wrapped Content' ) ).toBeNull();
	} );

	it( 'returns null if isPublished is true', () => {
		const { container, queryByText } = renderWithProps( {
			isFloating: true,
			hasPublishAction: false,
			isPublished: true,
		} );
		expect( container ).toMatchInlineSnapshot( '<div />' );
		expect( queryByText( 'Wrapped Content' ) ).toBeNull();
	} );

	it( 'returns children if all conditions are satisfied', () => {
		const { container, queryByText } = renderWithProps( {
			isFloating: true,
			hasPublishAction: false,
			isPublished: false,
		} );
		expect( container ).toMatchSnapshot();
		expect( queryByText( 'Wrapped Content' ) ).toBeInTheDocument();
	} );
} );
