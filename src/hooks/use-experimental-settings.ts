/**
 * WordPress dependencies
 */
import { __experimentalGetSettings } from '@wordpress/date';

export default function useExperimentalSettings(
	callback: ( settings: KeyedObject ) => KeyedObject,
) {
	const settings: KeyedObject = __experimentalGetSettings();

	return callback( settings );
}
