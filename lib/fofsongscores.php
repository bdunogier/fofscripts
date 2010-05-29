<?php
/**
 * This file contains the FOFSongScores class.
 */

/**
 * A frets on fire song scores set
 *
 * @doc http://fretsonfire.wikidot.com/song-ini-values
 */
class FOFSongScores
{
    public function __construct( $scoresString )
    {
    	$scoresArray = pyUncerealizer::parse( $scoresString );

		foreach( $scoresArray as $scoreDifficultiesArray )
		{
			$difficulty = new FOFSongScoresDifficulty( $scoreDifficultiesArray );
			$this->difficulties[$difficulty->difficulty] = $difficulty;
		}
    }

	public function __get( $property )
	{
		switch( $property )
		{
			case 'easy':
				return $this->difficulties[FOFSongScoresDifficulty::difficultyEasy];
				break;
			case 'medium':
				return $this->difficulties[FOFSongScoresDifficulty::difficultyMedium];
				break;
			case 'hard':
				return $this->difficulties[FOFSongScoresDifficulty::difficultyHard];
				break;
			case 'amazing':
				return $this->difficulties[FOFSongScoresDifficulty::difficultyAmazing];
				break;

			default:
				throw new ezcBasePropertyNotFoundException( $property );
		}
	}

	public function __isset( $property )
	{
		switch( $property )
		{
			case 'easy':
				return isset( $this->difficulties[FOFSongScoresDifficulty::difficultyEasy] );
				break;
			case 'medium':
				return isset( $this->difficulties[FOFSongScoresDifficulty::difficultyMedium] );
				break;
			case 'hard':
				return isset( $this->difficulties[FOFSongScoresDifficulty::difficultyHard] );
				break;
			case 'amazing':
				return isset( $this->difficulties[FOFSongScoresDifficulty::difficultyAmazing] );
				break;

			default:
				return false;
		}
	}

	public $difficulties = array();
}

class FOFSongScoresDifficulty
{

	public function __construct( $difficultyArray )
	{
		if ( count( $difficultyArray ) != 2 || !is_array( $difficultyArray[1] ) )
			// @todo Need a parameter exception class
			throw new Exception( "Not a valid difficulty array" );

		$this->difficulty = $difficultyArray[0];

		foreach( $difficultyArray[1] as $entry )
		{
			$this->scores[] = new FOFSongScoreEntry( $entry );
		}
	}

	public function __get( $property )
	{
		if ( $property == 'difficultyName' )
			return $this->difficulties[$this->difficulty];
	}

	/**
	 * Difficulty match map
	 * @var array
	 */
	const difficultyEasy    = 3;
	const difficultyMedium  = 2;
	const difficultyHard    = 1;
	const difficultyAmazing = 0;

	private $difficulties = array(
		self::difficultyEasy => 'easy',
		self::difficultyMedium => 'medium',
		self::difficultyHard => 'hard',
		self::difficultyAmazing => 'amazing',
	);

	/**
	 * Scores for this difficulty
	 * @var array( FOFSongScoreEntry )
	 */
	public $scores = array();

	public $difficulty;
}

class FOFSongScoreEntry
{
	public function __construct( $entryArray )
	{
		$this->score = $entryArray[0];
		$this->stars = $entryArray[1];
		$this->player = $entryArray[2];
		$this->hash = $entryArray[3];
	}

	public $stars;
	public $score;
	public $player;
	public $hash;
}
?>