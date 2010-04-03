<?php
/**
* Comment lire le dossier de chansons (récursif):
* 1. Ouvrir le dossier racine
* 2. Lire le premier sous-élément
* 3. Si ce dossier contient un ou des sous-dossiers, l'ouvrir également (appel récursif)
* 4. Si le dossier contient un fichier chanson + un fichier INI, lire le fichier INI
*    (guitar.ogg + song.ini)
**/
class SongsFilterIterator extends FilterIterator
{
	public function accept()
	{
		return ( $this->current()->getFileName() == 'song.ini' );
	}
}

$iterator = new SongsFilterIterator(
	new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( getcwd() ) ) );

include 'lib/fofsong.php';

foreach( $iterator as $item )
{
	$directory = dirname( $item );
	//if ( !$ini = @parse_ini_file( $iniFile ) )
	try {
		$song = new FOFSong( $directory );
	} catch ( Exception $e )
	{
		echo "FOFSong error: " . $e->getMessage() . "\n";
		continue;
	}

	if ( $song->artist === false or $song->name === false )
	{
		echo "'$iniFile' doesn't have an artist and/or name\n";
	    continue;
	}

	$songTitle = cleanup( $song->name );
	$songArtist = cleanup( $song->artist );

	$parentFolder = substr( $directory, 0, strrpos( $directory, DIRECTORY_SEPARATOR ) );
	$newDirectoryName = "$parentFolder/$songArtist - $songTitle";
	echo "Rename: $directory => $newDirectoryName\n";
	rename( $directory, $newDirectoryName );
}

function cleanup( $string )
{
	// Replace special characters with a dash
	$specialCharsRegexp = '#[' . preg_quote( '\/:*?"<>|' ) . ']#';
	$string = preg_replace( $specialCharsRegexp, '-', $string );
	if ( preg_match( '#^[0-9]+(?:\. |-)(.*)$#', $string, $matches ) )
	{
		$string = $matches[1];
	}
	return $string;
}

/**
 * parseINIFile()
 *
 * @param string $iniFile
 * @return array|false
 */
function parseINIFile($iniFile)
{
	$data = array();
	if ( !$lines = @file( $iniFile ) )
		throw new Exception( "Error parsing '$iniFile'" );
	if ( trim( $lines[0] ) != "[song]" )
		throw new Exception( "file doesn't contain a [song] header" );
	array_shift( $lines );
	foreach( $lines as $line )
	{
		if ( preg_match( '#^([^ ]+) = "?(.*)"?$#', trim( $line ), $matches ) )
		{
			$data[ strtolower( $matches[1] ) ] = $matches[2];
		}
	}

	// Check required data
	if ( !isset( $data['artist'] ) or !isset( $data['name'] ) )
		throw new Exception( "No artist or name INI settings" );

	return $data;
}
?>