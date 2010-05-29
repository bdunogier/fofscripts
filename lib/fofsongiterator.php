<?php
/**
 * Recursive FOFSong iterator
 * Will iterate over a directory and its subitems and return FOFSong objects
 * for found songs.
 *
 * A song is defined by a directory containing a valid song.ini file
 *
 * Usage:
 * <code>
 * $iterator = new FOFSongIterator( getcwd() );
 * $iterator->addFilter( 'artist', 'muse' );
 * foreach( $iterator as $song )
 * {
 *   echo $song->name . "\n";
 * }
 * </code>
 */
class FOFSongIterator extends FilterIterator
{
	public function __construct( $songsFolder )
	{
		parent::__construct(
			new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $songsFolder ),
				RecursiveIteratorIterator::SELF_FIRST
			)
		);
	}

	/**
	 * Accept callback. Only validates folders that contain a song.ini file.
	 * Iterates over $this->filter in order to filter in matching items.
	 *
	 * @return bool
	 */
	public function accept()
	{
		$item = $this->getInnerIterator()->current();
		$songINIPath = $item->getPathname() . DIRECTORY_SEPARATOR . 'song.ini';
		if ( $item->isDir() && file_exists( $songINIPath ) )
		{
			$song = new FOFSong( $item->getPathName() );
			foreach( $this->filters as $property => $value )
			{
				// @todo Refactor to a filter abstract class + classes
				if ( !is_array( $value) )
				{
					if ( strtolower( $song->$property ) != strtolower( $value ) )
						return false;
				}
				// complex filter, array[0] = operator, array[1] = value
				// FOFSongIteratorFilter
				// => FOFSongIteratorArithmeticComparison( $operator )
				//    Check for numeric property
				// Call filter callback
				else
				{
					$operator = $value[0];
					$value = $value[1];
					switch( $operator )
					{
						case '<':
							return (int)$song->$property < (int)$value;

						case '<=':
							return (int)$song->$property <= (int)$value;

						case '>':
							return (int)$song->$property > (int)$value;

						case '>=':
							return (int)$song->$property >= (int)$value;
					}
				}
			}
			return true;
		}
	}

	/**
	 * Instanciates an FOFSong with the current _ valid _ item (directory)
	 *
	 * @return FOFSong
	 */
	public function current()
	{
		return new FOFSong( $this->getInnerIterator()->current()->getPathName() );
	}

	/**
	 * Adds a custom filter to the iterator. The parameter key is an ini directive,
	 * and the value the INI value the directive must match.
	 *
	 * Only full match is implemented at the moment.
	 *
	 * @param string $key INI key
	 * @param string $value INI value
	 *
	 * @return void
	 */
	public function addFilter( $key, $value )
	{
		$this->filters[$key] = $value;
	}

	public $filters = array();
}
?>