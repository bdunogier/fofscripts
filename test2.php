<?php
/**
 * Tests the FOFSongIterator
 */
include 'lib/fofsong.php';
include 'lib/fofsongiterator.php';

$songs = new FOFSongIterator( getcwd() );
$songs->addFilter( 'artist', 'Muse' );
foreach( $songs as $song )
{
	echo "$song\n";
}
?>