/**
 * This file defines the production build configuration
 */
const {
	externals,
	helpers,
	plugins,
	presets,
} = require( '@humanmade/webpack-helpers' );

const { filePath } = helpers;
const {
	clean,
	manifest,
} = plugins;

const TSConfigPathsPlugin = require( 'tsconfig-paths-webpack-plugin' );

module.exports = presets.production( {
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
		filename: '[name].[contenthash].js',
		chunkFilename: '[name].[contenthash].js',
	},
	plugins: [
		clean( [ filePath( 'build' ) ] ),
		manifest( {
			publicPath: 'build/',
		} ),
	],
} );
