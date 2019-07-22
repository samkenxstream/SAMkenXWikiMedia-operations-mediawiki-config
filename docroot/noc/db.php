<?php

$format = isset( $_GET['format'] ) && $_GET['format'] === 'json' ? 'json' : 'html';

if ( $format === 'json' ) {
	error_reporting( 0 );
} else {
	// Verbose error reporting
	error_reporting( E_ALL );
	ini_set( 'display_errors', 1 );
}

// Default to eqiad but allow limited other DCs to be specified with ?dc=foo.
$allowedDCs = [
	'codfw' => 'db-codfw.php',
	'eqiad' => 'db-eqiad.php',
];
$dbConfigFileName = ( isset( $_GET['dc'] ) && isset( $allowedDCs[$_GET['dc']] ) )
	? $allowedDCs[$_GET['dc']]
	: $allowedDCs['eqiad'];

// Mock vars needed by db-*.php (normally set by CommonSettings.php)
$wgDBname = null;
$wgDBuser = null;
$wgDBpassword = null;
$wgSecretKey = null;
$wmfMasterDatacenter = null;

// Mock vars needed by db-*.php and InitialiseSettings.php (normally set by MediaWiki)
require_once __DIR__ . '/../../tests/Defines.php';

// Load the actual db vars
require_once __DIR__ . "/../../wmf-config/{$dbConfigFileName}";

class WmfClusters {
	private $clusters;

	/**
	 * @return array
	 */
	public function getNames() {
		global $wgLBFactoryConf;
		return array_keys( $wgLBFactoryConf['sectionLoads'] );
	}

	/**
	 * @param string $clusterName
	 * @return array
	 */
	public function getReadOnly( $clusterName ) {
		global $wgLBFactoryConf;
		return $wgLBFactoryConf['readOnlyBySection'][$clusterName] ?? false;
	}

	/**
	 * @param string $clusterName
	 * @return array
	 */
	public function getHosts( $clusterName ) {
		global $wgLBFactoryConf;
		return array_keys( $wgLBFactoryConf['sectionLoads'][$clusterName] );
	}

	/**
	 * @param string $clusterName
	 * @return string
	 */
	public function getLoads( $clusterName ) {
		global $wgLBFactoryConf;
		return $wgLBFactoryConf['sectionLoads'][$clusterName];
	}

	/**
	 * @param string $clusterName
	 * @return string
	 */
	public function getGroupLoads( $clusterName ) {
		global $wgLBFactoryConf;
		return $wgLBFactoryConf['groupLoadsBySection'][$clusterName];
	}

	/**
	 * @param string $clusterName
	 * @return array
	 */
	public function getDBs( $clusterName ) {
		global $wgLBFactoryConf;
		$ret = [];
		foreach ( $wgLBFactoryConf['sectionsByDB'] as $db => $cluster ) {
			if ( $cluster == $clusterName ) {
				$ret[] = $db;
			}
		}
		return $ret;
	}

	/**
	 * @param string $db
	 */
	public function getServer( $db ) {
		static $canonicalServers;
		if ( $canonicalServers === null ) {
			// Mock variable to capture the property assignment
			global $wgConf;
			$wgConf = new stdClass();
			require_once __DIR__ . '/../../wmf-config/InitialiseSettings.php';
			$canonicalServers = $wgConf->settings['wgCanonicalServer'];
		}
		if ( isset( $canonicalServers[$db] ) ) {
			// If the wiki is special or otherwise has an explicit server name, use it.
			$server = $canonicalServers[$db];
		} else {
			// Try the tag defaults (from db suffix to wgConf tag)
			$suffixes = [
				'wiki' => 'wikipedia',
				'wiktionary' => 'wiktionary',
				'wikiquote' => 'wikiquote',
				'wikibooks' => 'wikibooks',
				'wikiquote' => 'wikiquote',
				'wikinews' => 'wikinews',
				'wikisource' => 'wikisource',
				'wikiversity' => 'wikiversity',
				'wikimedia' => 'wikimedia',
				'wikivoyage' => 'wikivoyage',
			];
			foreach ( $suffixes as $suffix => $tag ) {
				if ( substr( $db, -strlen( $suffix ) ) === $suffix ) {
					$lang = substr( $db, 0, -strlen( $suffix ) );
					$server = strtr( $canonicalServers[$tag], '$lang', $lang );
					break;
				}
			}
		}
		return $server;
	}

	/**
	 * @param string $clusterName
	 */
	public function htmlFor( $clusterName ) {
		print "<strong>Hosts</strong><br>";
		foreach ( $this->getHosts( $clusterName ) as $host ) {
			print "<code>$host</code> ";
		}
		print '<br><strong>Loads</strong>:<br>';
		foreach ( $this->getLoads( $clusterName ) as $host => $load ) {
			print "$host => $load<br>";
		}
		print '<br><strong>Databases</strong>:<br>';
		if ( $clusterName == 'DEFAULT' ) {
			print 'Any wiki not hosted on the other clusters.<br>';
		} else {
			foreach ( $this->getDBs( $clusterName ) as $i => $db ) {
				print "$db";
				// labtestweb seems unresponsive, avoid crawlers hitting it
				if ( $i === 0 && $db !== 'labtestwiki' ) {
					// Use format=xml because it's cheap to generate and view
					// and browsers tend to render it nicely.
					// (json is hard to read by default, jsonfm is slower)
					$replagUrl = $this->getServer( $db ) . '/w/api.php?format=xml&action=query&meta=siteinfo&siprop=dbrepllag&sishowalldb=1';
					print ' (replag: <a href="' . htmlspecialchars( $replagUrl ) . '">mw-api</a> &bull;';
					print ' <a href="https://dbtree.wikimedia.org/">dbtree</a>)';
				}
				echo '<br>';
			}
		}
	}
}

$clusters = new WmfClusters();

if ( $format === 'json' ) {
	$data = [];
	foreach ( $clusters->getNames() as $name ) {
		$data[$name] = [
			'hosts' => $clusters->getHosts( $name ),
			'loads' => $clusters->getLoads( $name ),
			'groupLoads' => $clusters->getGroupLoads( $name ),
			'dbs' => $clusters->getDBs( $name ),
			'readOnly' => $clusters->getReadOnly( $name ),
		];
	}
	header( 'Content-Type: application/json; charset=utf-8' );
	echo json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	exit;
}

?><!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Wikimedia database configuration</title>
	<link rel="stylesheet" href="css/base.css">
	<style>
	h2 { font-weight: normal; }
	code { color: #000; background: #f9f9f9; border: 1px solid #ddd; border-radius: 2px; padding: 1px 4px; }
	main { display: flex; flex-wrap: wrap; }
	nav li { float: left; list-style: none; border: 1px solid #eee; padding: 1px 4px; margin: 0 1em 1em 0; }
	section { flex: 1; min-width: 300px; border: 1px solid #eee; padding: 0 1em; margin: 0 1em 1em 0; }
	main, footer { clear: both; }
	section:target { border-color: orange; }
	section:target h2 { background: #ffe; }
	</style>
</head>
<body>
<?php

// Generate navigation links
print '<nav><ul>';
$tab = 0;
foreach ( $clusters->getNames() as $name ) {
	$tab++;
	print '<li><a href="#tabs-' . $tab . '">Cluster ' . htmlspecialchars( $name ) . '</a></li>';
}
print '</ul></nav><main>';

// Generate content sections
$tab = 0;
foreach ( $clusters->getNames() as $name ) {
	$tab++;
	print "<section id=\"tabs-$tab\"><h2>Cluster <strong>" . htmlspecialchars( $name ) . '</strong></h2>';
	print $clusters->htmlFor( $name ) . '</section>';
}
print '</main>';
print '<footer>Automatically generated based on <a href="./conf/highlight.php?file='. htmlspecialchars( $dbConfigFileName ) . '">';
print 'wmf-config/' . htmlspecialchars( $dbConfigFileName ) . '</a></footer>'
?>
</body>
</html>
