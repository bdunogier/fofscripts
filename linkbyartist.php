<?php
/**
 * Analyses a FOF song and gets tracks & difficulties
 */
include dirname( __FILE__ ) . '/lib/autoload.php';

$input = new ezcConsoleInput();

$dryRunOption = $input->registerOption( new ezcConsoleOption( 'd', 'dry-run', ezcConsoleInput::TYPE_NONE, false ) );
$quietOption = $input->registerOption( new ezcConsoleOption( 'q', 'quiet', ezcConsoleInput::TYPE_NONE, false ) );
$helpOption = $input->registerOption( new ezcConsoleOption( 'h', 'help' ) );
$verboseOption = $input->registerOption( new ezcConsoleOption( 'v', 'verbose', ezcConsoleInput::TYPE_NONE, false ) );

$input->argumentDefinition = new ezcConsoleArguments();
$input->argumentDefinition[0] = new ezcConsoleArgument( "artist" );
$input->argumentDefinition[0]->mandatory = true;
$input->argumentDefinition[0]->shorthelp = "The artist to search for";

$output = new ezcConsoleOutput();
$output->formats->info->color = 'blue';

$output->formats->error->color = 'red';
$output->formats->error->style = array( 'bold' );

$output->formats->fatal->color = 'red';
$output->formats->fatal->style = array( 'bold', 'underlined' );
$output->formats->fatal->bgcolor = 'black';

try
{
	$input->process();
}
catch ( ezcConsoleArgumentMandatoryViolationException $e )
{
	if ( $helpOption->value === true )
	{
		$output->outputText( $input->getHelpText( "Auto-link by artist name" ) );
		exit;
	}
	else
	{
		$output->outputLine( "No arguments given", "fatal" );
		$output->outputText( $input->getHelpText( "Auto-link by artist name" ) );
		exit( 1 );
	}
}
catch ( ezcConsoleException $e )
{
	die( $e->getMessage() );
}

if ( $input->argumentDefinition["artist"]->value === null)
{
	$output->outputLine( "The <artist> argument is mandatory", 'fatal' );
	$output->outputText( $input->getHelpText( "Auto-link by artist name" ), 'fatal' );
	exit;
}

var_dump( $verboseOption->value );

$it = new FOFSongIterator( '/home/xbmc/fof/songs/Packs' );
$it->addFilter( 'artist', $input->argumentDefinition["artist"]->value );
foreach ( $it as $song )
{
	$linkName = $song->name;
	if ( file_exists( $linkName ) and ( $verboseOption->value === true ) )
	{
		$output->outputLine( "$linkName already exists", "info" );
	}
	else
	{
		$path = $song->directory->getPathname();
		if ( $quietOption !== false)
			$output->outputLine( "$linkName => $path" );
		if ( $dryRunOption->value !== true )
			symlink( $path, $linkName );
	}
}
?>