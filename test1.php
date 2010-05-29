<?php
/**
 * Basic FOFSong test
 */
include 'lib/fofsong.php';

$song = new FOFSong( 'test/acdcts' );
echo "Song: {$song->name} by {$song->artist}\n";
?>