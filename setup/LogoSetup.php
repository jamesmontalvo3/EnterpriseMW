<?php

if ( php_sapi_name() !== 'cli' ) {
	echo "This script is only accessible via command line";
	exit();
}

echo "This script generates logo and favicon for a particular group\n";
echo "What group would you like to generate?: ";
$group = fgets(STDIN);
$group = trim($group);

$path = dirname(__DIR__) . "\\Groups\\$group";

if ( ! is_dir($path) ) {
	echo "The $group directory was not found ($path)";
	exit();
}

if ( ! is_file($path . "/original.png") ) {
	echo "The original image \"original.png\" was not found in the $group directory";
	exit();
}

chdir( $path );

echo "Creating logo...\n";
echo shell_exec( "convert original.png -resize 160x160 logo.png" );

echo "Creating favicon...\n";
echo shell_exec( "convert logo.png  -bordercolor white -border 0 " .
	"( -clone 0 -resize 16x16 ) " .
	"( -clone 0 -resize 24x24 ) " .
	"( -clone 0 -resize 32x32 ) " .
	"( -clone 0 -resize 48x48 ) " .
	"( -clone 0 -resize 64x64 ) " .
	"( -clone 0 -resize 72x72 ) " .
	"( -clone 0 -resize 128x128 ) " .
	"-delete 0 -alpha off -colors 256 favicon.ico" );
 