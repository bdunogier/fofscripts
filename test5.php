<?php
/**
 * Analyses a FOF song and gets tracks & difficulties
 */
include dirname( __FILE__ ) . '/lib/autoload.php';

$it = new FOFSongIterator( getcwd() );
foreach ( $it as $song )
{
    echo (string)$song . "\n";
    foreach( $song->tracks as $track )
    {
        echo "* {$track->type}: " . implode( ', ', $track->difficulties ) . "\n";
    }    
}
?>