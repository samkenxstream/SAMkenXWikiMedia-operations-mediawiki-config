<?php

// Inline comments are often used for noting the task(s) associated with specific configuration
// and requiring comments to be on their own line would reduce readability for this file
// phpcs:disable MediaWiki.WhiteSpace.SpaceBeforeSingleLineComment.NewLineComment

/**
 * Configuration for the Minerva skin.
 */

return [
	// T210553, T210554
	'wgMinervaPageIssuesNewTreatment' => [
		'default' => [
			"base" => true,
		],
	],

	'wgMinervaDonateLink' => [
		'default' => [
			'base' => true,
		],
	],

	'wgMinervaHistoryInPageActions' => [
		'default' => [
			'base' => false,
			'loggedin' => true,
		],
		// T290812
		'ptwikinews' => [
			'base' => true,
		],
	],

	'wgMinervaAdvancedMainMenu' => [
		'default' => [
			'base' => false,
			'beta' => false,
			'loggedin' => false,
			'amc' => true,
		],
		// T290812
		'ptwikinews' => [
			'base' => true,
		],
	],

	'wgMinervaPersonalMenu' => [
		'default' => [
			'base' => false,
			'beta' => false,
			'loggedin' => false,
			'amc' => true,
		],
		// T290812
		'ptwikinews' => [
			'base' => true,
		],
	],

	'wgMinervaOverflowInPageActions' => [
		'default' => [
			'base' => false,
			'beta' => false,
			'loggedin' => true,
			'amc' => true,
		],
		// T290812
		'ptwikinews' => [
			'base' => true,
		],
	],

	'wgMinervaShowCategories' => [
		'default' => [
			'base' => false,
			'beta' => false,
			'loggedin' => false,
			'amc' => true,
		],
		// T290812
		'ptwikinews' => [
			'base' => true,
		],
	],

	'wgMinervaTalkAtTop' => [
		// Blocked on T54165
		'default' => [
			'base' => false,
			'beta' => false,
			'loggedin' => true,
		],
		'mobile-anon-talk' => [
			'base' => true,
			'beta' => true,
			'loggedin' => true,
		],
		// T290812
		'ptwikinews' => [
			'base' => true,
		],
	],

	'wgMinervaEnableSiteNotice' => [
		'default' => false,
		'closed' => true, // T261357
		'arwiki' => true,
		'bnwiki' => true, // T299529
		'bnwikibooks' => true, // T319317
		'bnwikiquote' => true, // T337683
		'bnwiktionary' => true, // T328630
		'kowiki' => true, // T172630
		'ptwikinews' => true, // T332813
		// WikidataPageBanner extensions must enable for Minerva support.
		// (T254391)
		'testwiki' => true,
		'test2wiki' => true,
		'wikivoyage' => true,
		'ruwikimedia' => true,
		'cawiki' => true,
		'euwiki' => true,
		'glwiki' => true,
		'trwiki' => true,
	],

	// T183665
	'wgMinervaAlwaysShowLanguageButton' => [
		'default' => true,
		'mediawikiwiki' => false,
		'wikidatawiki' => false,
	],
];
