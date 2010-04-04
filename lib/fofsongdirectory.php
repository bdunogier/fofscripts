<?php
class FOFSongDirectory
{
	public function __construct( $directory )
	{
		$this->path = $directory;
		$this->name = basename( $directory );
	}

	/**
	 * Renames the directory. This can not be used to move a directory around.
	 * Use the move() method instead.
	 *
	 * @param string $newName
	 * @return bool
	 */
	public function rename( $newName )
	{
		$parentDirectory = substr( $this->path, 0, strrpos( $this->path, DIRECTORY_SEPARATOR ) );
		return rename( $this->path, $parentDirectory . DIRECTORY_SEPARATOR . $newName );
	}

	public $path;
	public $name;
}
?>