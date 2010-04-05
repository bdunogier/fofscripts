<?php
/**
 * Tests the FOFSong directory rename feature
 */
include dirname( __FILE__ ) . '/lib/autoload.php';

$songs = new FOFSongIterator( getcwd() );
foreach( $songs as $song )
{
	echo (string)$song . "\n";
	foreach( $song->files as $file )
	{
		if ( !$file->isDot() )
			echo "* " . $file->getBasename() . "\n";
	}
	break;
}
?>