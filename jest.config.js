module.exports = {
	preset: '@wordpress/jest-preset-default',
	roots: [
		'<rootDir>/src',
	],
	testMatch: [
		'**/?(*.)+(spec|test).+(ts|tsx|js|jsx)',
	],
	transform: {
		'^.+\\.(ts|tsx)?$': 'ts-jest',
	},
	globals: {
		'ts-jest': {
			diagnostics: {
				warnOnly: true,
			},
		},
	},
};
