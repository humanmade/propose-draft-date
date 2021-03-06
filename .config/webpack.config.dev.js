/**
 * This file defines the development and DevServer build configuration.
 */
const { externals, helpers, presets } = require( '@humanmade/webpack-helpers' );

const { choosePort, cleanOnExit, filePath } = helpers;

const TSConfigPathsPlugin = require( 'tsconfig-paths-webpack-plugin' );

// Helper method to generate DevServer public paths.
const cwdRelativePublicPath = ( path, port ) => {
	const cwdRelativePath = path.replace( process.cwd(), '' );
	return `https://localhost:${port}${cwdRelativePath}/`;
};

// Clean up manifests on exit.
cleanOnExit( [
	filePath( 'build/asset-manifest.json' ),
] );

const config = {
	externals,
	resolve: {
		plugins: [
			new TSConfigPathsPlugin( { configFile: './tsconfig.json' } ),
		],
		extensions: [ '.js', '.jsx', '.ts', '.tsx' ],
	},
	entry: {
		'propose-draft-date': filePath( 'src/editor.tsx' ),
	},
	output: {
		path: filePath( 'build' ),
	},
};

if ( process.argv[1].indexOf( 'webpack-dev-server' ) !== -1 ) {
	// Webpack DevServer is running: autodetect & bind to an open port.
	module.exports = choosePort( 9090 ).then( ( port ) => presets.development( {
		...config,
		devServer: {
			https: true,
			port,
		},
		output: {
			...config.output,
			publicPath: cwdRelativePublicPath( config.output.path, port ),
		},
	} ) );
} else {
	// Dev-mode static file build is being run from within this project.
	module.exports = presets.development( config );
}
