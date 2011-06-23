<?php
try {
	require_once('conf.ini.php');
	//metadata
	$metadata['description'] = 'application de gestion des paiements';
	$metadata['motscles'] = 'gestion, débit, crédit, transfert, compte, banque, monnaie, balance, paiement';
	$smarty->assign('metadata', $metadata);

	$lang = 'fr';
	$smarty->assign('lang', $lang);

	//ajax call for smarty cache, smarty templates_c and stash cleaning
	if( filter_has_var(INPUT_GET, 'servercache') ){
		//clean smarty templates_c
		$handle = opendir($smarty->compile_dir);
		while( $tmp = readdir($handle) ){
			if( $tmp != '..' && $tmp != '.' && $tmp != '' ){
				 if( is_file($smarty->compile_dir.DS.$tmp) ){
						 unlink($smarty->compile_dir.DS.$tmp);
				 }
			}
		}
		closedir($handle);

		//clean smarty cache
		$handle = opendir($smarty->cache_dir);
		while( $tmp = readdir($handle) ){
			if( $tmp != '..' && $tmp != '.' && $tmp != '' ){
				 if( is_file($smarty->cache_dir.DS.$tmp) ){
						 unlink($smarty->cache_dir.DS.$tmp);
				 }
			}
		}
		closedir($handle);

		//clean stash
		$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
		StashBox::setHandler($stashFileSystem);

		$stash = new Stash($stashFileSystem);
		$stash->clear();

		echo '1';
		die;
	}

	$smarty->display('clean.tpl');
} catch (Exception $e) {
	echo $e->getMessage();
	die;
}
?>