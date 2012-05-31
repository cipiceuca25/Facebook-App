<?php
/**
 * An utility class holds some common help functions
 * 
 */
class Fancrank_Util_Util
{
	/**
	 * Executes a program and capture the result into array
	 */
	public static function execute($cmd,$stdin=null){
		$proc=proc_open($cmd,array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		fwrite($pipes[0],$stdin);
		fclose($pipes[0]);
		$stdout=stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		$stderr=stream_get_contents($pipes[2]);
		fclose($pipes[2]);
		$return=proc_close($proc);
		return array( 'stdout'=>$stdout, 'stderr'=>$stderr, 'return'=>$return );
	}
}

?>