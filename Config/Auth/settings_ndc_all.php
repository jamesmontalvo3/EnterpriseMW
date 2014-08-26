<?php

// see http://www.mediawiki.org/wiki/Manual:Hooks/SpecialPage_initList
// and http://www.mediawiki.org/w/Manual:Special_pages
// and http://lists.wikimedia.org/pipermail/mediawiki-l/2009-June/031231.html
// disable login and logout functions for all users
function LessSpecialPages(&$list) {
		unset( $list['Userlogout'] );
		unset( $list['Userlogin'] );
		return true;
}
$wgHooks['SpecialPage_initList'][]='LessSpecialPages';
 
// http://www.mediawiki.org/wiki/Extension:Windows_NTLM_LDAP_Auto_Auth
// remove login and logout buttons for all users
function StripLogin(&$personal_urls, &$wgTitle) {  
		unset( $personal_urls["login"] );
		unset( $personal_urls["logout"] );
		unset( $personal_urls['anonlogin'] );
		return true;
}
$wgHooks['PersonalUrls'][] = 'StripLogin';

// for all types, no creating accounts, viewing or editing for anonymous users...because
// there should not be any anonymous users. Everyone should automatically be logged in
// with their network username, regardless of whether they are allowed to view/edit.
$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['read'] = false;
$wgGroupPermissions['*']['edit'] = false;
