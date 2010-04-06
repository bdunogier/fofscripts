<?php
require('midi.class.php');

class MidiDuration extends Midi{
	
	//---------------------------------------------------------------
	// returns duration in seconds
	//---------------------------------------------------------------
	function getDuration(){
		$maxTime=0;
		foreach ($this->tracks as $track){
			$msgStr = $track[count($track)-1];
			list($time) = explode(" ", $msgStr);
			$maxTime = max($maxTime, $time);
		}
		return $maxTime * $this->getTempo() / $this->getTimebase() / 1000000;
	}
}


// TEST:
// $midi = new MidiDuration();
// $midi->importMid($file);
// echo 'Duration [sec]: '.$midi->getDuration(); // 69.14 sec for bossa.mid
	
?>