<?php
//force le niveau de remontée des erreurs
error_reporting(E_ALL | E_STRICT);
session_start();

/*
	A mettre dans le virtual host
	SetEnv LOCATION XXX
*/
define('SF_PATH', dirname(__FILE__));

if( !isset($_SERVER['LOCATION']) || empty($_SERVER['LOCATION']) ){
	define('ENV', 'WEB');

} elseif( strpos('_DEV', $_SERVER['LOCATION']) !== false ){
	define('ENV', 'LOCAL_DEV');

} else {
	define('ENV', 'LOCAL');
}

date_default_timezone_set('Europe/Zurich');
require( SF_PATH.'/inc/function_commun.php' );

//stash cache
//include(SF_PATH.'/inc/Stash/Autoloader.class.php');
include(SF_PATH.'/inc/Stash/Autoloader.class.php');

// Lazy load classes as they're called
StashAutoloader::register();

define('STASH_EXPIRE', 60 * 60 * 24 * 14); //2 weeks
define('STASH_PATH', SF_PATH.'/stash/');

//smarty
require(SF_PATH.'/inc/smarty/Smarty.class.php');

$smarty = new Smarty;

$smarty->debugging = false;
$smarty->caching = false;

$smarty->template_dir = SF_PATH.'/smarty/templates/';
$smarty->compile_dir  = SF_PATH.'/smarty/templates_c/';
$smarty->config_dir   = SF_PATH.'/smarty/configs/';
$smarty->cache_dir    = SF_PATH.'/smarty/cache/';
?>