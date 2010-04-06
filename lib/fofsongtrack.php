<?php
class FOFSongTrack
{
    public function __construct( $type )
    {
        $this->type = $type;
    }
    
    public $difficulties = array();
    public $type = false;
}
?>