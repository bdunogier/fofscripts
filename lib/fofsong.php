<?php
/**
 * This file contains the FOFSong class.
 */

/**
 * A frets on fire song
 *
 * @doc http://fretsonfire.wikidot.com/song-ini-values
 *
 * @property $artist
 * @property string album
 * @property string genre
 * @property string eighthnote_hopo
 * @property string version
 * @property string year
 * @property string lyrics
 * @property string icon
 * @property string name
 * @property string tags
 * @property string diff_guitar
 * @property string diff_drums
 * @property string diff_bass
 * @property string diff_band
 * @property string unlock_id
 * @property string unlock_require
 * @property string unlock_text
 * @property string cassettecolor
 * @property string count
 * @property string scores
 * @property string scores_ext
 * @property string tutorial
 * @property string delay
 * @property string frets
 * @property string version
 * @property string year
 * @property string genre
 * @property string loading_phrase
 * @property string hopofreq
 * @property string video
 * @property string video_start_time
 * @property string video_end_time
 * @property string preview_start_time
 * @property string cover
 * @property string background
 * @property string force_background
 * @property string hopo
 * @property string diff_vocals
 * @property string scores_bass
 * @property string scores_coop
 * @property string scores_lead
 */
class FOFSong
{
    public function __construct( $directory )
    {
        if ( !file_exists( $directory ) )
            throw new Exception( "Directory '$directory' could not be found" );
        if ( !is_readable( $directory ) )
            throw new Exception( "Directory '$directory' is not readable" );
        $this->songINI = $directory . DIRECTORY_SEPARATOR . "song.ini";
        if ( !file_exists( $this->songINI ) )
            throw new Exception( "Directory '$directory' does not contain a song.ini file" );

        $this->strMethod = self::STR_METHOD_SIMPLE;

		$this->directoryPath = $directory;
       	$this->parseINIFile();
    }

    public function __set( $property, $value )
    {
        if ( !in_array( $property, $this->INIProperties ) )
        {
            if ( $this->debug === true )
            	throw new Exception( "Unknown FOFSong property '$property'" );
        	else
        		return void;
        }
        switch( $property )
        {
        	case 'scores':
        	// case 'scores_ext':
        		$value = new FOFSongScores( $value );
        		break;
        }
		$this->INIData[$property] = $value;
    }

    public function __get( $property )
    {
        // directory: the FOFSongDirectory for this song
		if ( $property == 'directory' )
        {
        	if ( !isset( $privateData['directory'] ) )
        	{
        		$privateData['directory'] = new FOFSongDirectory( $this->directoryPath );
        	}
        	return $privateData['directory'];
        }
    	// files: a files iterator for the song's files
    	elseif( $property == 'files' )
    	{
    		if ( !isset( $privateData['files'] ) )
    		{
    			$privateData['files'] = new DirectoryIterator( $this->directoryPath );
    		}
    		return $privateData['files'];
    	}
        elseif ( $property == 'tracks' )
        {
            return $this->getTracks();
        }
		if ( !in_array( $property, $this->INIProperties ) )
            throw new Exception( "Unknown FOFSong property '$property'" );

        if ( !isset( $this->INIData[$property] ) )
            return false;
        else
            return $this->INIData[$property];
    }

    /**
     * Parses a FoF INI file
     *
     * @see $this->songINI
     */
    private function parseINIFile()
    {
        $data = array();
        if ( !$lines = @file( $this->songINI ) )
            throw new Exception( "Error parsing '$iniFile'" );
        if ( trim( $lines[0] ) != "[song]" )
            throw new Exception( "file doesn't contain a [song] header" );
        array_shift( $lines );
        foreach( $lines as $line )
        {
            if ( preg_match( '#^([^ ]+) = "?(.*)"?$#', trim( $line ), $matches ) )
            {
                $attribute = strtolower( $matches[1] );
                $this->$attribute = $matches[2];
            }
        }
    }

	public function __toString()
	{
		return "$this->artist: $this->name";
	}

	/**
     * This has to be called after values have been changed in order to save them
     */
    public function save()
    {
        $fp = fopen( $this->songINI, 'w' );
        fputs( $fp, "[song]\r\n" );
        foreach( $this->INIData as $INIKey => $INIValue )
        {
            fputs( $fp, sprintf( "%s = %s\r\n", trim( $INIKey ), trim( $INIValue ) ) );
        }
        fclose( $fp );
        return true;
    }

    private function getTracks()
    {
        if ( !isset( $privateData['tracks'] ) )
        {
            $fretIndex = array(
                60 => 'easy', 61 => 'easy', 62 => 'easy', 63 => 'easy', 64 => 'easy',
                72 => 'medium', 73 => 'medium', 74 => 'medium', 75 => 'medium', 76 => 'medium',
                84 => 'hard', 85 => 'hard', 86 => 'hard', 87 => 'hard', 88 => 'hard',
                96 => 'expert', 97 => 'expert', 98 => 'expert', 99 => 'expert', 100 => 'expert',
            );
            $difficulties = array_unique( array_values( $fretIndex ) );

            $midi = new Midi();
            // $midi->importMid( 'abeautifullie/notes.mid' );
            // $midi->importMid( 'acdc_thunderstruck/notes.mid' );
            $midi->importMid( $this->directoryPath . DIRECTORY_SEPARATOR . 'notes.mid' );
            $xmlString = $midi->getXml();
            $xml = simplexml_load_string( $xmlString );

            foreach( $xml->Track as $track )
            {
                $trackName = (string)$track->Event[0]->TrackName;
                if ( substr( $trackName, 0, 5 ) == 'PART ' )
                {
                    $trackName = ucfirst( strtolower( substr( $trackName, 5 ) ) );
                    $parts[] = $trackName;

                    // disabled difficulty levels for track
                    $t0 = $track->xpath( 'Event[Absolute=0 and child::NoteOn]' );
                    foreach( $t0 as $event )
                    {
                        $note = (int)$event->NoteOn[0]['Note'];

                        if ( !isset( $disabled[$trackName] ) )
                            $disabled[$trackName] = array();
                        // unknown note, let's skip it for now
                        if ( !isset( $fretIndex[$note] ) )
                            break;
                        $difficulty = $fretIndex[$note];
                        if ( !isset( $disabled[$trackName][$difficulty] ) )
                            $disabled[$trackName][$difficulty] = array();

                        $disabled[$trackName][$difficulty][$note] = true;
                    }
                }
            }
            // display available difficulties for each track
            $tracks = array();
            foreach( $parts as $track )
            {
                $trackDifficulties = array();
                foreach( $difficulties as $difficulty )
                {
                    if ( !isset( $disabled[$track][$difficulty] ) )
                        $trackDifficulties[] = $difficulty;
                }
                if ( count( $trackDifficulties ) > 0 )
                {
                    $currentTrack = new FOFSongTrack( $track );
                    $currentTrack->difficulties = $trackDifficulties;
                    $tracks[] = $currentTrack;
                }
            }
        }
        return $privateData['tracks'];
    }

	/**
	 * Sets the __toString behaviour
	 */
	public function setStrMethod( $strMethod )
	{
		if ( !in_array( $strMethod, array( self::STR_METHOD_SIMPLE, self::STR_METHOD_ADVANCED ) ) )
			throw new Exception( "Unknown STR_METHOD: $strMethod" );
		$this->strMethod = $strMethod;
	}

    private $songINI = false;
    private $INIData = array();
    private $INIProperties = array(
        'artist',
        'album',
        'genre',
        'eighthnote_hopo',
        'version',
        'year',
        'lyrics',
        'icon',
        'name',
        'tags',
        'diff_guitar',
        'diff_drums',
        'diff_bass',
        'diff_band',
        'unlock_id',
        'unlock_require',
        'unlock_text',
        'cassettecolor',
        'count',
        'scores',
        'scores_ext',
        'tutorial',
        'delay',
        'frets',
        'version',
        'year',
        'genre',
        'loading_phrase',
        'hopofreq',
        'video',
        'video_start_time',
        'video_end_time',
        'preview_start_time',
        'cover',
        'background',
        'force_background',

        'hopo', 'diff_vocals', 'scores_bass', 'scores_coop', 'scores_lead'
    );
	private $privateData;
	private $directoryPath;

	public $debug = false;

	private $strMethod;

	/**
	* Used by self::uncerealize
	*/
	private $currentPos = null;

	const STR_METHOD_SIMPLE = 0;
	const STR_METHOD_ADVANCED = 1;
}
?>