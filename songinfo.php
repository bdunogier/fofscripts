#!/usr/bin/php
<?php
include 'lib/autoload.php';
$out = new ezcConsoleOutput();

try {
	$song = new FOFSong( getcwd() );
} catch( Exception $e ) {
	echo "Exception: " . $e->getMessage() . "\n";
}

$out->outputLine( $song->name . " by " . $song->artist );
$out->outputLine( "Scores: " );
foreach ( array( 'easy', 'medium', 'hard', 'amazing' ) as $difficulty )
{
	if ( isset( $song->scores->$difficulty  ) )
	{
		$scores = $song->scores->$difficulty;
		$out->outputLine( "* "  . $scores->difficultyName );
		foreach( $scores->scores as $idx => $score )
		{
			$rank = $idx+1;
			$out->outputLine( "{$rank}. {$score->player}: {$score->score} ({$score->stars}*)" );
		}
	}
}
?>