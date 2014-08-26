<?php

require_once dirname( __FILE__ ) . '/settings_ndc_all.php';

// Auth_remoteuser extension, updated by James Montalvo, blocks remote users
// who are not part of the group defined by $wgAuthRemoteuserViewerGroup
$wgAuthRemoteuserViewerGroup = "Viewer"; // set to false to allow all valid REMOTE_USER to view; set to group name to restrict viewing to particular group
$wgAuthRemoteuserDeniedPage = "Access_Denied"; // redirect non-viewers to this page (namespace below)
$wgAuthRemoteuserDeniedNS = NS_PROJECT; // redirect non-viewers to page in this namespace


$wgGroupPermissions['user']['talk'] = true; 
$wgGroupPermissions['user']['read'] = true;
$wgGroupPermissions['user']['edit'] = false;

// Viewer group is used by the Auth_remoteuser extension to allow only those in
// group "Viewer" to view the wiki. This allows anyone with NDC auth to get to the
// wiki (which auto-creates an account for them), but doesn't allow those users to
// see any of the wiki (besided the "access denied" page and "request access" page)
$wgGroupPermissions['Viewer']['talk'] = true; 
$wgGroupPermissions['Viewer']['read'] = true;
$wgGroupPermissions['Viewer']['edit'] = false;
$wgGroupPermissions['Viewer']['movefile'] = true;

$wgGroupPermissions['Contributor'] = $wgGroupPermissions['user'];
$wgGroupPermissions['Contributor']['edit'] = true;
$wgGroupPermissions['Contributor']['unwatchedpages'] = true;

#
#   CURATORs: people with delete permissions for now
#
$wgGroupPermissions['Curator']['delete'] = true; // Delete pages
$wgGroupPermissions['Curator']['bigdelete'] = true; // Delete pages with large histories
$wgGroupPermissions['Curator']['suppressredirect'] = true; // Not create redirect when moving page
$wgGroupPermissions['Curator']['browsearchive'] = true; // Search deleted pages
$wgGroupPermissions['Curator']['undelete'] = true; // Undelete a page
$wgGroupPermissions['Curator']['deletedhistory'] = true; // View deleted history w/o associated text
$wgGroupPermissions['Curator']['deletedtext'] = true; // View deleted text/changes between deleted revs

#
#   MANAGERs: can edit user rights, plus used in MediaWiki:Approvedrevs-permissions
#   to allow managers to give managers the ability to approve pages (lesson plans, ESOP, etc)
#
$wgGroupPermissions['Manager']['userrights'] = true; // Edit all user rights

