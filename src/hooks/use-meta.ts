import { useCallback } from 'react';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Hook to read the value of a specific meta key.
 *
 * @param metaKey The string key of the meta value to read.
 */
export function useMetaValue<MetaType>( metaKey: string ): MetaType {
	const meta = useSelect( ( select ) => select( 'core/editor' ).getEditedPostAttribute( 'meta' ) );
	return meta ? meta[metaKey] : undefined;
}

/**
 * Hook to read and/or update the value of a specific meta key.
 *
 * @param metaKey The string key of the meta value to read or modify.
 * @returns Array of the meta value, and a setter dispatch method for that value.
 */
export default function useMeta<MetaType>(
	metaKey: string,
): [ MetaType, ( value: MetaType|null ) => void ] {
	const metaValue = useMetaValue<MetaType>( metaKey );

	const { editPost } = useDispatch( 'core/editor' );
	const updateMeta = useCallback( ( value: MetaType|null ): void => {
		editPost( {
			meta: {
				[metaKey]: value,
			},
		} );
	}, [ editPost, metaKey ] );

	return [ metaValue, updateMeta ];
}
