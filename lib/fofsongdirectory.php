<?php
/**
 * The directory containing a frets on fire song
 */
class FOFSongDirectory extends SplFileInfo
{
	/*public function __construct( $directory )
	{
		$this->path = $directory;
		$this->name = basename( $directory );
	}*/

	/**
	 * Renames the directory.
	 * This can not be used to move a directory around, use the move() method instead.
	 *
	 * @param string $newName
	 * @return bool
	 */
	public function rename( $newName )
	{
		if ( !is_writable( $this->path ) )
		    throw new RuntimeException( "Song directory '{$this->path}' is not writable" );
	    
        $parentDirectory = substr( $this->path, 0, strrpos( $this->path, DIRECTORY_SEPARATOR ) );
	    if ( !is_writable( $parentDirectory ) )
	        throw new RuntimeException( "Song parent directory '{$parentDirectory}' is not writable" );
	    
		return rename( $this->path, $parentDirectory . DIRECTORY_SEPARATOR . $newName );
	}

	// public $path;
	// public $name;
}
?>