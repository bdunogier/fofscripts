<?php
define( 'TEST_MODE', false );
define( 'LETTER_SPLIT', true );

$baseSongsFolder = "d:\\Games\\Frets on fire\\Songs";
$songsFolder = "$baseSongsFolder\\Unsorted";
$byArtistSongsFolder = "$baseSongsFolder\\By artist";

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

function createJunction( $sourceDirectory, $target )
{
	$components = explode( DIRECTORY_SEPARATOR, $target );
	$title = array_pop( $components );
	$artist = array_pop( $components );
	$prefix = implode( DIRECTORY_SEPARATOR, $components );
	$artistFolder = $prefix . DIRECTORY_SEPARATOR . $artist;

	if ( LETTER_SPLIT )
	{
		$letter = substr( $artist, 0, 1 );
		if ( !preg_match( '#^[a-z]$#i', $letter ) )
			$letter = '#';
		$letterFolder = $prefix . DIRECTORY_SEPARATOR . $letter;
		if ( !TEST_MODE and !file_exists( $letterFolder ) )
			mkdir( $letterFolder );
		$artistFolder = $letterFolder . DIRECTORY_SEPARATOR . $artist;
		$target = $artistFolder . DIRECTORY_SEPARATOR . $title;
	}

	if ( file_exists( $target ) )
		return true;

	echo "'$sourceDirectory' => '$target'\n";

	// Create parent, artist folder
	if ( !TEST_MODE and !file_exists( $artistFolder ) )
	{
		if ( !@mkdir( $artistFolder ) )
			throw new Exception( "An error occured creating artist folder '$artistFolder'" );
	}

	if ( TEST_MODE )
		return true;

	$out = $ret = null;
	exec( $cmd = "mklink /J \"$target\" \"$sourceDirectory\"", $out, $ret );
	if ( $ret != 0 )
	{
		throw new Exception( "An error occured creating junction '$target'" );
	}
	else
	{
		return true;
	}
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

$iterator = new SongsFilterIterator(
	new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $songsFolder ) ) );

foreach( $iterator as $item )
{
	$iniFile = (string)$item;
	//if ( !$ini = @parse_ini_file( $iniFile ) )
	try {
		$data = parseINIFile( $iniFile );
	} catch ( Exception $e )
	{
		echo "Error on $iniFile: " . $e->getMessage() . "\n";
		continue;
	}

	if ( !isset( $data['artist'] ) )
	{
		echo "'$iniFile' doesn't have an artist\n"; continue;
	}
	if ( !isset( $data['name'] ) )
	{
		echo "'$iniFile' doesn't have a name\n"; continue;
	}
	$songTitle = cleanup( $data['name'] );
	$songArtist = cleanup( $data['artist'] );

	$songJunction = $byArtistSongsFolder . DIRECTORY_SEPARATOR . $songArtist . DIRECTORY_SEPARATOR . $songTitle;

	try {
		createJunction( dirname( $iniFile ), $songJunction );
	} catch ( Exception $e ) {
		echo "ERROR: " . $e->getMessage() . "\n";
	}
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
			$data[ $matches[1] ] = $matches[2];
		}
	}

	// Check required data
	if ( !isset( $data['artist'] ) or !isset( $data['name'] ) )
		throw new Exception( "No artist or name INI settings" );

	return $data;
}
?>