===========================
Frets on fire songs library
===========================

.:: Author: Bertrand Dunogier

Synopsis
========
This set of libraries provides a simple API for manipulating Frets on Fire
songs.

Requirements
============

* PHP >= 5.2.6 - http://php.net/
* ezComponents - http://ezcomponents.org/

Installation
============
Just meet the requirements.

Current API
===========

FOFSong
-------
High level access to a song. All the INI properties can be read & written
using their name as a property::

$song = new FOFSong('mysongdir');
echo $song->name;
$song->fret = 'me';
$song->save

It gives you access to the directory it is contained into as a FOFSongDirectory
object::

print_r( $song->directory );

You can also browse the file the directory contains using the files property,
as a DirectoryIterator::

foreach( $song->files as $file )
{
	echo $file->getBasename();
}

FOFSongDirectory
----------------
Used by FOFSong. Should probably not be used on its own, kinda pointless.

FOFSongIterator
---------------
Provides an easy way to browse a directory's songs, recursively.

It can also accept filters so that you can easily get the list of songs that
match a criteria::

$list = new FOFSongIterator( 'path/to/songs' );
$list->addFilter( 'artist', 'Muse' );
foreach( $list as $song )
{
	echo $song->directory->path;
}