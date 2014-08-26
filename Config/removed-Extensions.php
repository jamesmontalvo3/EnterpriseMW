<?php

require_once __DIR__ . '/../Includes/JSCMOD_Extensions.php';
$ext = new JSCMOD_Extensions();


// load extensions based on whether they're enabled in extensions.json
foreach ( $ext->getEnabledExtensions() as $ext_name => $ext_info ) {

	require_once $ext->extensions_dir . "/$ext_name/$ext_name.php";

	if ( isset($ext_info['callback']) )
		call_user_func( $ext_info['callback'] );

}


// ParserFunctions
$wgPFEnableStringFunctions = true;

// Cite
$wgCiteEnablePopups = true;

// WhoIsWatching
$wgPageShowWatchingUsers = true;



// SemanticMediaWiki
$smwgQMaxSize = 5000;
 
// AdminLinks
$wgGroupPermissions['sysop']['adminlinks'] = true;

// FIXME: Why do we have this?
$wgWhitelistRead = array('Special:UserLogin');
$wgShowExceptionDetails = true;

// BatchUserRights
$wgBatchUserRightsGrantableGroups = array(
	'Viewer',
	'Contributor'
);

// SemanticResultFormats
$srfgFormats = array('calendar', 'timeline', 'exhibit', 'eventline', 'tree', 'oltree', 'ultree', 'sum');

// HeaderTabs
$htEditTabLink = false;
$htRenderSingleTab = true;

// WikiEditor
$wgDefaultUserOptions['usebetatoolbar'] = 1; // Enables use of WikiEditor by default but 
$wgDefaultUserOptions['usebetatoolbar-cgd'] = 1; // but users can disable in preferences
$wgDefaultUserOptions['wikieditor-publish'] = 1; // displays publish button
$wgDefaultUserOptions['wikieditor-preview'] = 1; // Displays the Preview and Changes tabs

// ApprovedRevs
$egApprovedRevsAutomaticApprovals = false;