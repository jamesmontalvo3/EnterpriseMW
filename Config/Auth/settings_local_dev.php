<?php

$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['read'] = false;
$wgGroupPermissions['*']['edit'] = false;

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
