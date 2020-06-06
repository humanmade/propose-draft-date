import {
	renderHook,
	act,
} from '@testing-library/react-hooks';
import {
	useDispatch,
	useSelect,
} from '@wordpress/data';

import useMeta from './use-meta';

jest.mock( '@wordpress/data', () => ( {
	useDispatch: jest.fn(),
	useSelect: jest.fn(),
} ) );

const mockOnce = ( mockedFn: Function, fn: StubFn ): void => {
	( mockedFn as jest.Mock ).mockImplementationOnce( fn );
};

describe( 'useMetaFallbacks', () => {
	it( 'returns the value of requested meta value in the store', () => {
		mockOnce( useSelect, () => ( {
			metaKey: 'meta value',
		} ) );
		mockOnce( useDispatch, () => ( {
			editPost: jest.fn(),
		} ) );

		const { result } = renderHook( () => useMeta<string>( 'metaKey' ) );
		expect( result.current[0] ).toBe( 'meta value' );
	} );

	it( 'returns undefined if the meta is not set', () => {
		mockOnce( useSelect, () => ( {
			irrelevantMetaKey: 'meta value',
		} ) );
		mockOnce( useDispatch, () => ( {
			editPost: jest.fn(),
		} ) );

		const { result } = renderHook( () => useMeta<string>( 'metaKey' ) );
		expect( result.current[0] ).toBe( undefined );
	} );

	it( 'returns a method that updates the meta value', () => {
		mockOnce( useSelect, () => ( {
			metaKey: 'meta value',
		} ) );
		const editPost = jest.fn();
		mockOnce( useDispatch, () => ( {
			editPost,
		} ) );

		const { result } = renderHook( () => useMeta<string>( 'metaKey' ) );
		const updateMeta = result.current[1];
		expect( updateMeta ).toBeInstanceOf( Function );

		act( () => updateMeta( 'new value' ) );

		expect( editPost ).toHaveBeenCalledWith( {
			meta: {
				metaKey: 'new value',
			},
		} );
		expect( editPost ).toHaveBeenCalledTimes( 1 );

		act( () => updateMeta( null ) );

		expect( editPost ).toHaveBeenCalledWith( {
			meta: {
				metaKey: null,
			},
		} );
		expect( editPost ).toHaveBeenCalledTimes( 2 );
	} );
} );
