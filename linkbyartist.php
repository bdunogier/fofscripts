<?php
/**
 * Analyses a FOF song and gets tracks & difficulties
 */
include dirname( __FILE__ ) . '/lib/autoload.php';

$input = new ezcConsoleInput();

$dryRunOption = $input->registerOption( new ezcConsoleOption( 'd', 'dry-run', ezcConsoleInput::TYPE_NONE, false ) );
$quietOption = $input->registerOption( new ezcConsoleOption( 'q', 'quiet', ezcConsoleInput::TYPE_NONE, false ) );
$helpOption = $input->registerOption( new ezcConsoleOption( 'h', 'help' ) );

$input->argumentDefinition = new ezcConsoleArguments();
$input->argumentDefinition[0] = new ezcConsoleArgument( "artist" );
$input->argumentDefinition[0]->mandatory = false;
$input->argumentDefinition[0]->shorthelp = "The artist to search for";

try
{
	$input->process();
}
catch ( ezcConsoleException $e )
{
	die( $e->getMessage() );
}

if ( $helpOption->value === true )
{
	echo $input->getHelpText( "Auto-link by artist name" );
	exit;
}

if ( $input->argumentDefinition["artist"]->value === null)
{
	echo "The <artist> argument is mandatory\n";
	echo $input->getHelpText( "Auto-link by artist name" );
	exit;
}

$it = new FOFSongIterator( '/home/xbmc/fof/songs/Packs' );
$it->addFilter( 'artist', $input->argumentDefinition["artist"]->value );
foreach ( $it as $song )
{
	$linkName = $song->name;
	if ( !file_exists( $linkName ) )
	{
		$path = $song->directory->getPathname();
		if ( $quietOption !== false)
			echo $path . " => " . $linkName. "\n";
		if ( $dryRunOption->value !== true )
			symlink( $path, $linkName );
	}
}
?>