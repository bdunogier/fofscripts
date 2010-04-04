<?php
require_once "ezc/Base/base.php"; // dependent on installation method, see below
set_include_path( dirname( __FILE__ ) );
spl_autoload_register( array( 'FOF', 'autoload' ) );
spl_autoload_register( array( 'ezcBase', 'autoload' ) );

class FOF
{
	public static function autoload( $className )
	{
		$lib = dirname( __FILE__ );

		if ( strtolower( substr( $className, 0, 3 ) ) == 'fof' )
		{
			$fileName = $lib . DIRECTORY_SEPARATOR . strtolower( $className ) .".php";
			if ( file_exists( $fileName ) )
				include $fileName;
		}
	}
}
?>