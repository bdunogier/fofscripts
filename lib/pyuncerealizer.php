<?php
/**
* PHP version of Cerealize, a python serialize method:
* http://home.gna.org/oomadness/en/cerealizer/index.html
*
* @copyright sneftrup, http://sourceforge.net/projects/fofchart/
*/
class pyUncerealizer
{
	/**
	 * Parsing method. Returns the converted string
	 * @param string $string
	 * @return array
	 **/
	public static function parse( $string )
	{
		$uncerealizer = new self;

		$dec = $uncerealizer->hexToAscii( $string );
		return $uncerealizer->uncerealize( $dec );
	}

	/**
	 * Uncerealize the string. This is the main function
	 *
	 * @param string $cerealizedString
	 * @return string
	 */
	protected function uncerealize( $cerealizedString )
	{
		$length = strlen( $cerealizedString );
		$numOfObjects = 0;
		$buffer;
		$result = array();
		if ( !strcmp( substr( $cerealizedString, 0, strlen( "cereal!" ) ), "cereal1" ) == 0 )
		{
			// @todo ezcomponents
			throw new Exception( "Not a valid cerealized string: $cerealizedString" );
		}
		else
		{
			$this->currentPos += strlen( "cereal1\n" );
			// figure out how many objects we have
			$numOfObjects = $this->readLine( $cerealizedString );

			for( $i = 0; $i < $numOfObjects; $i++ )
			{
				array_push( $result, $this->parseObjects( $cerealizedString ) );
			}

			$result = $this->parseReferences( $cerealizedString, $result, $numOfObjects );
		}
		return $result;
	}

	/**
    * convert a hex string to ascii
    *
    * @param string $hexString
    * @return string
    */
    protected function hexToAscii( $hexString )
    {
        $returnString = "";
        $stringLen = strlen( $hexString );
        if ( $stringLen % 2 != 0 )
        {
            throw new Exception( "Error, odd-length string: $hexString" );
        }

        for ( $i = 0; $i < $stringLen; $i += 2 )
        {
            $decimalValue = hexdec( substr( $hexString, $i, 2 ) );
            $returnString .= chr( $decimalValue );
        }
        return $returnString;
    }

    /**
    * read a line, i.e to the next \n or \r
    *
    * @param mixed $string
    * @return
    */
    protected function readLine( $string )
    {
        $line = "";
        while ( $this->currentPos < strlen( $string ) ) // read a complete line
        {
            $char = substr( $string, $this->currentPos++, 1 );
            if ( $char == "\n" || $char == "\r" )
            {
                break;
            }
            else
            {
                $line .= $char;
            }
        }
        return $line;
    }

    // internal function that parses the list of objects and returns them in an orded array
    protected function parseObjects( $cerealizedString )
    {
        $length = strlen( $cerealizedString );
        $result = array();
        while ( $this->currentPos < $length )
        {
            $line = $this->readLine( $cerealizedString );
            if ( strcmp( $line, "dict" ) == 0 ) // dictionary
            {
                return array( "dict" );
            }
            else if ( strcmp( $line, "list" ) == 0 ) // list
            {
                return array( "list" );
            }
            else if ( strcmp( $line, "set" ) == 0 ) // set
            {
                return array( "set" );
            }
            else if ( strcmp( $line, "tuple" ) == 0 ) // tuple
            {
                $tupObjectCount = $this->readLine( $cerealizedString );
                return $this->parseTuple( $cerealizedString, $tupObjectCount );
            }
            else if ( strcmp( substr( $line, 0, 1 ), "i" ) == 0 ) // integer
            {
                $int = substr( $line, 1, strlen( $line ) - 1 );
                return $int;
            }
            else if ( strcmp( substr( $line, 0, 1 ), "s" ) == 0 ) // string
            {
                $strLen = "";
                for ( $i = 1; $i < strlen( $line ); $i++ )
                {
                    $strLen .= substr( $line, $i, 1 );
                }
                $string = substr( $cerealizedString, $this->currentPos, $strLen );
                $this->currentPos += $strLen;
                return $string;
            }
        }
    }
    // parses a tuple
    protected function parseTuple( $string, $objectCount )
    {
        $objectsFound = 0;
        $length = strlen( $string );
        $result = array();
        while ( $objectsFound < $objectCount )
        {
            array_push( $result, $this->parseObjects( $string, $this->currentPos ) );
            $objectsFound++;
        }
        return $result;
    }

    /**
     * Parses the reference list, reorders the objects so it's correct
     *
     * @param string $string
     * @param string $result
     * @param int $numOfObjects
     * @return array
     */
    protected function parseReferences( $string, $result, $numOfObjects )
    {
        $refStart = $this->currentPos;
        $tmpResult = array();
        $length = strlen( $string );

        for ( $currentObject = 0; $currentObject < $numOfObjects; $currentObject++ )
        {
            if ( strcmp( $result[$currentObject][0], "dict" ) == 0 )
            {
                $dict = array();
                // read number of items, then read
                $itemNum = $this->readLine( $string );
                for ( $i = 0; $i < $itemNum; $i++ )
                {
                    $ref = $this->readLine( $string );
                    $ref = substr( $ref, 1, strlen( $ref ) );
                    $id = $this->readLine( $string );
                    $id = substr( $id, 1, strlen( $id ) );
                    array_push( $dict, array( $id, $ref ) );
                }
                array_push( $tmpResult, $dict );
            }
            else if ( strcmp( $result[$currentObject][0], "list" ) == 0 )
            {
                $list = array();

                $itemNum = $this->readLine( $string );
                for ( $i = 0; $i < $itemNum; $i++ )
                {
                    $id = $this->readLine( $string );
                    array_push( $list, $result[substr( $id, 1, strlen( $id ) )] );
                }
                array_push( $tmpResult, $list );
            }
            else if ( strcmp( $result[$currentObject][0], "set" ) == 0 )
            {
                // not implemented
            }
        }

        $finalResult = $tmpResult[0];
        for ( $i = 0; $i < count( $dict ); $i++ )
        {
            $finalResult[$i][1] = $tmpResult[$dict[$i][1]];
        }
        return $finalResult;
    }

	protected $currentPos = 0;
}

?>