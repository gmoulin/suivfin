<?php
//force le niveau de remontée des erreurs
error_reporting(E_ALL | E_STRICT);
session_start();

/*
	A mettre dans le virtual host
	SetEnv LOCATION XXX
*/
define('SF_PATH', dirname(__FILE__));

if( strpos('_DEV', $_SERVER['LOCATION']) !== false ){
	define( "SERVER_NAME", 'http://suivfin.dev' );
} else {
	define( "SERVER_NAME", 'http://suivfin' );
}

date_default_timezone_set('Europe/Zurich');
require( LMS_PATH.'/inc/function_commun.php' );
?>