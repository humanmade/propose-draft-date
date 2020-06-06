/**
 * Dynamically locate, load & register all Editor Blocks & Plugins.
 *
 * Entry point for the "editor.js" bundle.
 */
import { autoloadPlugins } from 'block-editor-hmr';

import './editor.scss';

const acceptContextModules = ( context: KeyedObject, loadModules: () => KeyedObject ): void => {
	if ( module.hot ) {
		module.hot.accept( context.id, loadModules );
	}
};

// Load all plugin files.
autoloadPlugins( {
	getContext: () => require.context( './plugins', true, /index\.tsx?$/ ),
}, acceptContextModules );
