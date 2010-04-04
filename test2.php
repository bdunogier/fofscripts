<?php
/**
 * Tests the FOFSongIterator
 */
include dirname( __FILE__ ) . '/lib/autoload.php';

$songs = new FOFSongIterator( getcwd() );
$songs->addFilter( 'artist', 'Muse' );
foreach( $songs as $song )
{
	echo "$song (".$song->directory->path.")\n";
}
?>