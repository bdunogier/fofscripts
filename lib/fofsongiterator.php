<?php
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
	 * Accept callback. Only validates folders that contain a song.ini file
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
			foreach( $this->filters as $key => $value )
			{
				if ( strtolower( $song->$key ) != strtolower( $value ) )
					return false;
			}
			return true;
		}
	}

	/**
	 * @return FOFSong
	 */
	public function current()
	{
		return new FOFSong( $this->getInnerIterator()->current()->getPathName() );
	}

	public function addFilter( $key, $value )
	{
		$this->filters[$key] = $value;
	}

	public $filters = array();
}
?>