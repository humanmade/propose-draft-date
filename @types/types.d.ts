declare module 'luna-react';

interface KeyedObject {
	// eslint-disable-next-line @typescript-eslint/no-explicit-any
	[key: string]: any;
}

interface StringListDictionary {
	[key: string]: string[];
}

// See https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/
interface EditComponentProps<Attributes = KeyedObject> {
	// Some keys are known, others may be injected with select.
	// eslint-disable-next-line @typescript-eslint/no-explicit-any
	[key: string]: any | {
		attributes: Attributes;
		className?: string;
		clientId: string;
		isSelected: boolean;
		name: string;
		setAttributes: ( attributes: Attributes ) => void;
	};
}

interface MediaObject {
	alt: string;
	caption: string;
	id: number;
	url: string;
}

interface ShortcodeAttributes {
	named: KeyedObject; // key-value pairs
	numeric: string[];
}

interface Shortcode {
	content: string; // e.g. [fin_hide from="2019-03-22"]
	index: number;
	shortcode: {
		attrs: ShortcodeAttributes;
		content: string;
		tag: string;
		type: string;
	};
}

interface FormatDefinition {
	attributes: KeyedObject;
	type: string;
	unregisteredAttributes: KeyedObject;
}

// Define a type usable as the custom implementation of a jest Mock.
interface StubFn {
	// eslint-disable-next-line @typescript-eslint/no-explicit-any
	( ...args: any ): any;
}
