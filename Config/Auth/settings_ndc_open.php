<?php

require_once dirname( __FILE__ ) . '/settings_ndc_all.php';


$wgGroupPermissions['user']['talk'] = true; 
$wgGroupPermissions['user']['read'] = true;
$wgGroupPermissions['user']['edit'] = true;

// Viewer group really only used in settings_ndc_closed.php
// Set to the same as "user"
$wgGroupPermissions['Viewer'] = $wgGroupPermissions['user']; 

// Also same as user, with perhaps some additional privileges
$wgGroupPermissions['Contributor'] = $wgGroupPermissions['user'];
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

