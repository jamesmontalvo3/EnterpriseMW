<?php

class EnterpriseMW {

	static public $IP;
	
	public function __construct ( $groupName, $authType, $options ) {
	
		$this->groupName = $groupName;
		$this->authType = $authType;
		
		$this->setDefaultOptions( $options );
	
		$this->initialSetup();
		
		$this->defaultSettings();

		$this->requireAuthSettings();
	}
	
	public function setExtensionIP ($path) {
		$this->IP = $path;
		return $path;
	}
	
	public function getExtensionIP () {
		return $this->IP;
	}
	
	protected function setDefaultOptions ( $providedOptions ) {
	
		$this->server = $providedOptions['server'] ? $providedOptions['server'] : $_SERVER['SERVER_NAME'];
		$this->replyEmailServer = $providedOptions['replyEmailServer'] ? $providedOptions['replyEmailServer'] : $_SERVER['SERVER_NAME'];
		
		$this->debug = ( $providedOptions['debug'] === true ) ? true : false;
	
	}
	
	public function initialSetup () {
		
		// development: error reporting
		if ( $this->debug ) {

			// turn error logging on
			error_reporting( -1 );
			ini_set( 'display_errors', 1 );
			ini_set( 'log_errors', 1 );
			
			// Output errors to log file
			ini_set( 'error_log', __DIR__ . '/php.log' );

			// MediaWiki Debug Tools
			$GLOBALS['wgShowExceptionDetails'] = true;
			$GLOBALS['wgDebugToolbar'] = true;
			$GLOBALS['wgShowDebug'] = true;

		}
		// production: no error reporting
		else {

			error_reporting(0);
			ini_set("display_errors", 0);

		}
	
		$this->groupPathName = str_replace( ' ', '', $this->groupName );

		$GLOBALS['wgSitename'] = $this->groupPathName . ' Wiki';
		$GLOBALS['wgMetaNamespace'] = str_replace( ' ', '_', $GLOBALS['wgSitename'] );

		$GLOBALS['wgEmergencyContact'] = str_replace( ' ', '-', $GLOBALS['wgSitename'] ) . '@' . $this->replyEmailServer;
		$GLOBALS['wgPasswordSender'] = $GLOBALS['wgEmergencyContact'];
		


		## The URL base path to the directory containing the wiki;
		## defaults for all runtime URL paths are based off of this.
		## For more information on customizing the URLs please see:
		## http://www.mediawiki.org/wiki/Manual:Short_URL
		$GLOBALS['wgScriptPath']       = '/wiki/' . $this->groupPathName;
		$GLOBALS['wgScriptExtension']  = '.php';


		## The relative URL path to the skins directory
		$GLOBALS['wgStylePath']        = $GLOBALS['wgScriptPath'] . '/skins';


		## The relative URL path to the logo.  Make sure you change this from the default,
		## or else you'll overwrite your logo when you upgrade!
		$logoPath = 
			$GLOBALS['wgScriptPath'] . '/extensions/JSCMOD/Groups/'
			. $this->groupPathName . '/';
		$GLOBALS['wgLogo']           = $logoPath . 'logo.png';
		$GLOBALS['wgFavicon']        = $logoPath . 'favicon.ico';
		$GLOBALS['wgAppleTouchIcon'] = $logoPath . 'apple-touch-icon.png';
		

		$extensionIP = $this->IP;

		// require_once "$extensionIP/Config/Extensions.php";

		## The following included script gets programmatically modified 
		## during backup operations to set read-only prior to backup and
		## unset when backup is complete
		include "$extensionIP/Config/wgReadOnly.php";

		$GLOBALS['wgHooks']['BeforePageDisplay'][] = 'EnterpriseMW::onBeforePageDisplay';

		$EnterpriseMWResourceTemplate = array(
			'localBasePath' => __DIR__ . '/modules',
			'remoteExtPath' => 'EnterpriseMW/modules',
		);

		$GLOBALS['wgResourceModules'] += array(

			'ext.enterprise.mw.base' => $EnterpriseMWResourceTemplate + array(
				'styles' => 'base/EnterpriseMW.css',
				'scripts' => array(
					'base/EnterpriseMW.js',
				),
			),

		);
		
	}
	
	

	public function requireAuthSettings () {

		$auth_types = array(
			'local_dev',
			'ndc_closed',
			'ndc_openview',
			'ndc_open',	
		);

		if ( ! $this->authType ) {
			throw new MWException( 'Use of Extension:EnterpriseMW requires an auth type be set.' );
			$this->authType = 'error';
		} else if ( ! in_array($this->authType, $auth_types) ) {
			throw new MWException( 'Unsupported auth type set. See Extension:EnterpriseMW.' ); 
			$this->authType = 'error';
		}

		require_once $this->IP . '/Config/Auth/settings_' . $this->authType . '.php';
	}
	
	// static public function addJSandCSS ( $out ) {
		// global $wgScriptPath;
		// // $out->addScriptFile( $wgScriptPath .'/resources/session.min.js' );
		// $out->addScriptFile( $wgScriptPath .'/extensions/JSCMOD/Lib/JSCMOD.js' );

		// $out->addLink( array(
			// 'rel' => 'stylesheet',
			// 'type' => 'text/css',
			// 'media' => "screen",
			// 'href' => "$wgScriptPath/extensions/JSCMOD/Lib/JSCMOD.css"
		// ) );
		
		// return true;
	// }
	
	/**
	* Handler for BeforePageDisplay hook.
	* @see http://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	* @param $out OutputPage object
	* @param $skin Skin being used.
	* @return bool true in all cases
	*/
	static function onBeforePageDisplay( $out, $skin ) {
		
		$out->addModules( array( 'ext.jscmod.base' ) );

		return true;
	}

	
	
	
	
	
	
	
	
	
	
	
	private $extensions;
	public $extensionsDirectory;

	protected function loadExtensions () {
		
		if ( ! file_exists( __DIR__ . '/extensions.json' ) ) {
			return false;
		}
		
		$this->extensions = json_decode( 
			file_get_contents( __DIR__ . '/extensions.json' ),
			true
		);
		
		if ( ! is_array( $this->extensions ) ) {
			return false;
		}
		if ( count( $this->extensions ) === 0 ) {
			return false;
		}
		
		// this file is in .../extensions/EnterpriseMW. Doing dirname(__DIR__)
		// returns .../extensions
		$this->extensionsDirectory = dirname(__DIR__);
		

		// load extensions based on whether they're enabled in extensions.json
		foreach ( $this->getEnabledExtensions() as $ext_name => $ext_info ) {

			require_once $this->extensionsDirectory . "/$ext_name/$ext_name.php";

			if ( isset($ext_info['callback']) )
				call_user_func( $ext_info['callback'] );

		}

		// ParserFunctions
		$GLOBALS['wgPFEnableStringFunctions'] = true;

		// Cite
		$GLOBALS['wgCiteEnablePopups'] = true;

		// WhoIsWatching
		$GLOBALS['wgPageShowWatchingUsers'] = true;

		// SemanticMediaWiki
		$GLOBALS['smwgQMaxSize'] = 5000;
		 
		// AdminLinks
		$GLOBALS['wgGroupPermissions']['sysop']['adminlinks'] = true;

		// FIXME: Why do we have this?
		$GLOBALS['wgWhitelistRead'] = array('Special:UserLogin');
		$GLOBALS['wgShowExceptionDetails'] = true;

		// BatchUserRights
		$GLOBALS['wgBatchUserRightsGrantableGroups'] = array(
			'Viewer',
			'Contributor'
		);

		// SemanticResultFormats
		$GLOBALS['srfgFormats'] = array('calendar', 'timeline', 'exhibit', 'eventline', 'tree', 'oltree', 'ultree', 'sum');

		// HeaderTabs
		$GLOBALS['htEditTabLink'] = false;
		$GLOBALS['htRenderSingleTab'] = true;

		// WikiEditor
		$GLOBALS['wgDefaultUserOptions']['usebetatoolbar'] = 1; // Enables use of WikiEditor by default but 
		$GLOBALS['wgDefaultUserOptions']['usebetatoolbar-cgd'] = 1; // but users can disable in preferences
		$GLOBALS['wgDefaultUserOptions']['wikieditor-publish'] = 1; // displays publish button
		$GLOBALS['wgDefaultUserOptions']['wikieditor-preview'] = 1; // Displays the Preview and Changes tabs

		// ApprovedRevs
		$GLOBALS['egApprovedRevsAutomaticApprovals'] = false;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	######### Updater Methods
	######### was in JSCMOD_Extensions class
	
	
	// initiates or updates extensions
	// does not delete extensions if they're disabled
	public function updateExtensions () {
				
		foreach( $this->extensions as $ext_name => $ext_info ) {

			if ( ! $this->isExtensionEnabled( $ext_name ) ) {
				continue;
			}
			
			$ext_dir = "{$this->extensionsDirectory}/$ext_name";
			
			// Check if extension directory exists, update extension accordingly
			if ( is_dir($ext_dir) ) {
				$this->checkExtensionForUpdates( $ext_name );
			}
			else {
				$this->cloneGitRepo( $ext_name );
			}
			
		}
		
	}
	
	protected function cloneGitRepo ( $ext_name ) {

		echo "\n    CLONING EXTENSION $ext_name\n";
	
		$ext_info = $this->extensions[$ext_name];
	
		// change working directory to main extensions directory
		chdir( $this->extensionsDirectory );
		
		// git clone into directory named the same as the extension
		echo shell_exec( "git clone {$ext_info['origin']} $ext_name" );
		
		if ( $ext_info['checkout'] !== 'master' ) {
		
			chdir( "{$this->extensionsDirectory}/$ext_name" );
		
			echo shell_exec( "git checkout " . $ext_info['checkout'] ); 
		
		}
				
	}
	
	protected function checkExtensionForUpdates ( $ext_name ) {
	
		echo "\n    Checking for updates in $ext_name\n";
	
		$ext_info = $this->extensions[$ext_name];
		$ext_dir = "{$this->extensionsDirectory}/$ext_name";
		
		if ( ! is_dir("$ext_dir/.git") ) {
			echo "\nNot a git repository! ($ext_name)";
			return false;	
		}
		
		// change working directory to main extensions directory
		chdir( $ext_dir );
		
		// git clone into directory named the same as the extension
		echo shell_exec( "git fetch origin" );

		$current_sha1 = shell_exec( "git rev-parse --verify HEAD" );
		$fetched_sha1 = shell_exec( "git rev-parse --verify {$ext_info['checkout']}" );
		
		if ($current_sha1 !== $fetched_sha1) {
			echo "\nCurrent commit: $current_sha1";
			echo "\nChecking out new commit: $fetched_sha1\n";
			echo shell_exec( "git checkout {$ext_info['checkout']}" );
		}
		else {
			echo "\nsha1 unchanged, no update required ($current_sha1)";
		}
		
		return true;
	
	}
	
	######### END updater methods
	
	
	
	
	
	
	
	
	
	protected function isExtensionEnabled ( $ext_name ) {
		$ext_info = $this->extensions[$ext_name];
		
		if ( ! isset($ext_info["enable"]) || $ext_info["enable"] === true )
			return true; // enabled if no mention, or if explicitly set to true
		else if ( $this->isDevelopmentEnvironment && $ext_info["enable"] == "dev"  )
			return true;
		else
			return false;
	}

	public function loadExtensions () {
		global $wgVersion;
		foreach( $this->extensions as $ext_name => $ext_info ) {

			if ( ! $this->isExtensionEnabled( $ext_name ) ) {
				continue;
			}

			require_once "{$this->extensionsDirectory}/$ext_name/$ext_name.php";
			
			if ( isset($ext_info['callback']) )
				call_user_function( $ext_info['callback'] );
		}
			
	}
	
	public function getEnabledExtensions () {
	
		$out = array();
		
		foreach ( $this->extensions as $ext_name => $ext_info ) {
		
			if ( $this->isExtensionEnabled($ext_name) )
				$out[$ext_name] = $ext_info;
		
		}
		
		return $out;
	
	}
	
	
	
	
	
	########## Extension setup functions
	
	static public function SMW_Setup () {
		enableSemantics( $this->groupPathName . '.' . $this->server );
	}
	
	static public function UploadWizard_Setup () {
		$GLOBALS['wgExtensionFunctions'][] = function() {
			$GLOBALS['wgUploadNavigationUrl'] = SpecialPage::getTitleFor( 'UploadWizard' )->getLocalURL();
			return true;
		};
	}
	########## End extension-specific functions
	
	
	
	
	
	
	
	
	protected function defaultSettings () {

		$GLOBALS['wgRCShowWatchingUsers'] = true; // shows number of watchers in recent changes
		// $GLOBALS['wgAjaxUploadDestCheck'] = true; // AJAX check for file overwrite pre-upload

		// certain aspects of the JSCMOD install require title=? in query string
		$GLOBALS['wgUsePathInfo'] = false;

		$GLOBALS['wgEnotifUserTalk']      = true; # UPO
		$GLOBALS['wgEnotifWatchlist']     = true; # UPO
		$GLOBALS['wgEmailAuthentication'] = true;

		# MySQL specific settings
		$GLOBALS['wgDBprefix']         = "";

		# MySQL table options to use during installation or update
		$GLOBALS['wgDBTableOptions']   = "ENGINE=InnoDB, DEFAULT CHARSET=binary";

		# Experimental charset support for MySQL 4.1/5.0.
		$GLOBALS['wgDBmysql5'] = false;

		## Shared memory settings
		$GLOBALS['wgMainCacheType']    = CACHE_NONE;
		$GLOBALS['wgMemCachedServers'] = array();


		## Disable all forms of MediaWiki caching
		// TAKEN FROM: http://thinkhole.org/wp/2006/09/13/disabling-caching-in-mediawiki/
		$GLOBALS['wgMainCacheType'] = CACHE_NONE;
		$GLOBALS['wgMessageCacheType'] = CACHE_NONE;
		$GLOBALS['wgParserCacheType'] = CACHE_NONE;
		//$GLOBALS['wgEnableParserCache'] = false;
		$GLOBALS['wgCachePages'] = false;


		## To enable image uploads, make sure the 'images' directory
		## is writable, then set this to true:
		$GLOBALS['wgEnableUploads']  = true;
		#$GLOBALS['wgUseImageMagick'] = true;
		#$GLOBALS['wgImageMagickConvertCommand'] = "/usr/bin/convert";

		# maximum size of an image that will generate a thumbnail. Not sure if larger images will be
		# prevented from being uploaded. If the images already were uploaded, then this number is reduced
		# the wiki will display "error creating thumbnail" in place of the thumbnail.
		$GLOBALS['wgMaxImageArea'] = "100000000";

		// added this... was just allowing images without it...
		$GLOBALS['wgFileExtensions'] = array('png','gif','jpg','jpeg','mpp','pdf','tiff','bmp','docx', 'xlsx', 'pptx','ps','odt','ods','odp','odg','zip');
		$GLOBALS['wgStrictFileExtensions'] = false;

		// remove "this file type may contain malicious code" warning
		$GLOBALS['wgTrustedMediaFormats'][] = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
		$GLOBALS['wgTrustedMediaFormats'][] = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
		$GLOBALS['wgTrustedMediaFormats'][] = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";


		// Due to issues uploading .docx, this variable was changed to the default from
		// MW 1.18. The only change was the removal of 'application/x-opc+zip' from the
		// blacklist.
		$GLOBALS['wgMimeTypeBlacklist'] = array(
			# HTML may contain cookie-stealing JavaScript and web bugs
			'text/html', 'text/javascript', 'text/x-javascript', 'application/x-shellscript',
			# PHP scripts may execute arbitrary code on the server
			'application/x-php', 'text/x-php',
			# Other types that may be interpreted by some servers
			'text/x-python', 'text/x-perl', 'text/x-bash', 'text/x-sh', 'text/x-csh',
			# Client-side hazards on Internet Explorer
			'text/scriptlet', 'application/x-msdownload',
			# Windows metafile, client-side vulnerability on some systems
			'application/x-msmetafile',
		);

			
		# InstantCommons allows wiki to use images from http://commons.wikimedia.org
		$GLOBALS['wgUseInstantCommons']  = false;

		## If you use ImageMagick (or any other shell command) on a
		## Linux server, this will need to be set to the name of an
		## available UTF-8 locale
		$GLOBALS['wgShellLocale'] = "en_US.utf8";

		## If you want to use image uploads under safe mode,
		## create the directories images/archive, images/thumb and
		## images/temp, and make them all writable. Then uncomment
		## this, if it's not already uncommented:
		#$GLOBALS['wgHashedUploadDirectory'] = false;

		## If you have the appropriate support software installed
		## you can enable inline LaTeX equations:
		$GLOBALS['wgUseTeX']           = false;

		## Set $wgCacheDirectory to a writable directory on the web server
		## to make your wiki go slightly faster. The directory should not
		## be publically accessible from the web.
		#$GLOBALS['wgCacheDirectory'] = "$IP/cache";

		# Site language code, should be one of ./languages/Language(.*).php
		$GLOBALS['wgLanguageCode'] = "en";

		## Default skin: you can change the default skin. Use the internal symbolic
		## names, ie 'standard', 'nostalgia', 'cologneblue', 'monobook', 'vector':
		$GLOBALS['wgDefaultSkin'] = "vector";


		# Path to the GNU diff3 utility. Used for conflict resolution.
		// $GLOBALS['wgDiff3'] = "C:/Program Files (x86)/GnuWin/bin/diff3.exe";
		$GLOBALS['wgDiff3'] = 'C:/Program Files (x86)/GnuWin32/bin/diff3.exe';

		# Use external mime detector
		// $GLOBALS['wgMimeDetectorCommand'] = "C:/Program Files (x86)/GnuWin/bin/file.exe -bi";


		# Query string length limit for ResourceLoader. You should only set this if
		# your web server has a query string length limit (then set it to that limit),
		# or if you have suhosin.get.max_value_length set in php.ini (then set it to
		# that value)
		$GLOBALS['wgResourceLoaderMaxQueryLength'] = -1;

		# End of automatically generated settings.
		# Add more configuration options below.

		// allows users to remove the page title.
		$GLOBALS['wgRestrictDisplayTitle'] = false;

		$GLOBALS['wgUseRCPatrol'] = false;
		$GLOBALS['wgUseNPPatrol'] = false;


		#
		#	AUTH SETTINGS
		#
		// get authentication settings
		JSCMOD::requireAuthSettings( $this->authType );


		if ( $this->authType != 'local_dev' ) {

			# any NDC
			require_once $this->IP . "/Includes/Auth.php";
			$GLOBALS['wgAuth'] = new Auth_remoteuser();

			// This is not an auth-setting, but is specific to MOD server configuration
			// On MOD servers can't access the desired "C:\\Windows\TEMP" directory so this location
			// was setup. Alternatively could have used the $IP/images directory, I think.
			$GLOBALS['wgTmpDirectory'] = "d:\PHP\uploadtemp";
			// Note: There is no corresponding value for local auth, since most people
			// can use whatever the default is, or will have to set it explicitly
		}

		// Enable subpages on Main namespace
		$GLOBALS['wgNamespacesWithSubpages'][NS_MAIN] = true;

		// I think this is for web api url caching
		//$GLOBALS['edgCacheTable'] = 'ed_url_cache';
		//$GLOBALS['edgCacheExpireTime'] = 0;

		// opens external links in new window
		$GLOBALS['wgExternalLinkTarget'] = '_blank';

		// added this line to allow linking. specifically to Imagery Online.
		$GLOBALS['wgAllowExternalImages'] = true;
		$GLOBALS['wgAllowImageTag'] = true;



		//$GLOBALS['wgDefaultUserOptions']['useeditwarning'] = 1;
		// disable page edit warning (edit warning affect Semantic Forms)
		$GLOBALS['wgVectorFeatures']['editwarning']['global'] = false;

		//$GLOBALS['wgDefaultUserOptions']['vector-collapsiblenav'] = 1;
			// 'collapsiblenav' => array( 'global' => true, 'user' => true ),
			// 'collapsibletabs' => array( 'global' => true, 'user' => false ),
			// 'editwarning' => array( 'global' => false, 'user' => true ),
			// 'expandablesearch' => array( 'global' => false, 'user' => false ),
			// 'footercleanup' => array( 'global' => false, 'user' => false ),
			// 'simplesearch' => array( 'global' => false, 'user' => true ),


		$GLOBALS['wgDefaultUserOptions']['rememberpassword'] = 1;

		// users watch pages by default (they can override in settings)
		$GLOBALS['wgDefaultUserOptions']['watchdefault'] = 1;
		$GLOBALS['wgDefaultUserOptions']['watchmoves'] = 1;
		$GLOBALS['wgDefaultUserOptions']['watchdeletion'] = 1;

		$GLOBALS['wgEnableMWSuggest'] = true;

		// fixes login issue for some users (login issue fixed in MW version 1.18.1 supposedly)
		$GLOBALS['wgDisableCookieCheck'] = true;

		#Set Default Timezone
		$GLOBALS['wgLocaltimezone'] = "America/Chicago";
		$GLOBALS['oldtz'] = getenv("TZ");
		putenv( 'TZ=' . $GLOBALS['wgLocaltimezone'] );

		$GLOBALS['wgMaxUploadSize'] = 1024*1024*30;
		// $GLOBALS['wgUploadSizeWarning'] = 1024*1024*100;

		$GLOBALS['wgMaxTocLevel'] = 3;

	}
	
}