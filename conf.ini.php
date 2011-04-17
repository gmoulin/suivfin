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
require( SF_PATH.'/inc/function_commun.php' );

//stash cache
//include(SF_PATH.'/inc/Stash/Autoloader.class.php');
include(SF_PATH.'/inc/Stash/Autoloader.class.php');

// Lazy load classes as they're called
StashAutoloader::register();

define('STASH_EXPIRE', 60 * 60 * 24 * 14); //2 weeks
define('STASH_PATH', SF_PATH.'/stash/');

$lang_months = array(
	'01' => 'Janvier',
	'02' => 'Février',
	'03' => 'Mars',
	'04' => 'Avril',
	'05' => 'Mai',
	'06' => 'Juin',
	'07' => 'Juillet',
	'08' => 'Août',
	'09' => 'Septembre',
	'10' => 'Octobre',
	'11' => 'Novembre',
	'12' => 'Décembre',
);

//smarty
require(SF_PATH.'/inc/smarty/Smarty.class.php');

$smarty = new Smarty;

$smarty->debugging = false;
$smarty->caching = false;

$smarty->template_dir = SF_PATH.'/smarty/templates/';
$smarty->compile_dir  = SF_PATH.'/smarty/templates_c/';
$smarty->config_dir   = SF_PATH.'/smarty/configs/';
$smarty->cache_dir    = SF_PATH.'/smarty/cache/';

$smarty->assign('lang_months', $lang_months);
?>