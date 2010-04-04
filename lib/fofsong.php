<?php
/**
 * A frets on fire song
 *
 * @doc http://fretsonfire.wikidot.com/song-ini-values
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
        $this->INIData[$property] = $value;
    }

    public function __get( $property )
    {
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

	public $debug = false;
}
?>