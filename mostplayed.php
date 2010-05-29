#!/usr/bin/php
<?php
include 'lib/autoload.php';
$out = new ezcConsoleOutput();

try {
	$songs = new FOFSongIterator( getcwd() );
} catch( Exception $e ) {
	echo "Exception: " . $e->getMessage() . "\n";
}

$out->outputLine( "MOST PLAYED SONGS");

$songs->addFilter( 'count', array( '>=', 5 ) );
foreach( $songs as $idx => $song )
{
	$rang = $idx + 1;
	echo "{$rang}. {$song} ({$song->count})\n";
}
?>