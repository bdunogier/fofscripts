<?php
/**
 * Tests the FOFSong directory rename feature
 */
include dirname( __FILE__ ) . '/lib/autoload.php';

$songs = new FOFSongIterator( getcwd() );
foreach( $songs as $song )
{
	$newName = "{$song->artist} - {$song->name}";
	$song->directory->rename( $newName );
	break;
}
?>