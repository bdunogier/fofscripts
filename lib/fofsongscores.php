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
    /**
     * Constructor
     *
     * @param string $scoresString
     * @param string $extScoresString
     */
	public function __construct( $scoresString, $extScoresString )
    {
    	$scoresArray = pyUncerealizer::parse( $scoresString );
    	$extScoresArray = pyUncerealizer::parse( $extScoresString );

		$mergedScoresArray = self::mergeScoresArrays( $scoresArray, $extScoresArray );
		foreach( $mergedScoresArray as $scoreDifficultiesArray )
		{
			$difficulty = new FOFSongScoresDifficulty( $scoreDifficultiesArray );
			$this->difficulties[$difficulty->difficulty] = $difficulty;
		}
    }

	/**
	 * Merges the scores and scores_ext array in one
	 *
	 * @param array $scoresArray
	 * @param array $extScoresArray
	 * @return array
	 */
	public function mergeScoresArrays( $scoresArray, $extScoresArray )
	{
		// difficulty layer
		if ( count( $scoresArray ) != count( $extScoresArray ) )
			throw new Exception( "Invalid score array" );

		foreach( $scoresArray as $difficulty => $subArray )
		{
			$difficultySubentry = $subArray[0];
			$subScoresArray = $subArray[1];
			foreach( $subScoresArray as $scoreKey => $scoreEntry )
			{
				// we skip the first item, same hash
				foreach( array_slice( $extScoresArray[$difficulty][1][$scoreKey], 1 ) as $entry )
				{
					$scoresArray[$difficulty][1][$scoreKey][] = $entry;
				}
			}
		}
		return $scoresArray;
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
		$this->score = $entryArray[self::IDX_SCORE];
		$this->stars = $entryArray[self::IDX_STARS];
		$this->player = $entryArray[self::IDX_PLAYER];
		$this->hash = $entryArray[self::IDX_HASH];

		$this->notesOk = (int)$entryArray[self::IDX_NOTES_OK];
		$this->notesTotal = (int)$entryArray[self::IDX_NOTES_TOTAL];
		$this->perfect = ( $entryArray[self::IDX_STARS_EXT] == 6 );
		$this->streak = ( $entryArray[self::IDX_STREAK] == 6 );

		$this->percentage = round( ( $this->notesOk / $this->notesTotal ) * 100, 2 );
	}

	public $stars;
	public $score;
	public $player;
	public $hash;
	public $notesOk;
	public $notesTotal;
	public $streak;
	public $perfect;
	public $percentage;

	const IDX_SCORE = 0;
	const IDX_STARS = 1;
	const IDX_PLAYER = 2;
	const IDX_HASH = 3;
	const IDX_STARS_EXT = 4;
	const IDX_NOTES_OK = 5;
	const IDX_NOTES_TOTAL = 6;
	const IDX_STREAK = 7;
	const IDX_ORIGIN = 8;
}
?>