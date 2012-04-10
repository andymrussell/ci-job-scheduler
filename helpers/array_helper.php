<?php


if ( ! function_exists('__unserialize'))
{
	function __unserialize($string) {
	    $unserialized = stripslashes($string);
	       $unserialized = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $unserialized );
	       return unserialize($unserialized);
	}
}