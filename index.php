<?php
//@todo bug filtre recipient (psycho)
//@todo bug solde comptes pas à jour (et erroné)

try {
	require_once('conf.ini.php');

	//metadata
	$metadata['description'] = 'application de gestion des paiements';
	$metadata['motscles'] = 'gestion, débit, crédit, transfert, compte, banque, monnaie, balance, paiement';
	$smarty->assign('metadata', $metadata);

	$lang = 'fr';
	$smarty->assign('lang', $lang);

	if( file_exists(SF_PATH.'/css/style.css') ) $smarty->assign('css', filemtime(SF_PATH.'/css/style.css'));
	if( file_exists(SF_PATH.'/js/script.js') ) $smarty->assign('js', filemtime(SF_PATH.'/js/script.js'));

	if( !filter_has_var(INPUT_GET, 'owner') ){
		$owner = 3; //default
	} else {
		$owner = filter_input(INPUT_GET, 'owner', FILTER_SANITIZE_NUMBER_INT);
		if( is_null($owner) || $owner === false ){
			throw new Exception('Gestion des comptes : compte incorrect.');
		}
	}

	init::getInstance()->setOwner( $owner );
	$smarty->assign('owner', $owner);

	$smarty->assign('monthsTranslation', init::getInstance()->getMonthsTranslation());

	$day = date('d');
	$selectedTimeFrame = array();
	if( $day > 20 ){
		$selectedTimeFrame[ date('Y') ][] = date('m');
		$selectedTimeFrame[ date('Y', strtotime("+10 days")) ][] = date('m', strtotime("+10 days"));
	} elseif( $day < 5 ){
		$selectedTimeFrame[ date('Y', strtotime("-10 days")) ][] = date('m', strtotime("-10 days"));
		$selectedTimeFrame[ date('Y') ][] = date('m');
	} else {
		$selectedTimeFrame[ date('Y') ][] = date('m');
	}
	$smarty->assign('selectedTimeFrame', $selectedTimeFrame);

	//get payments for current month
	$smarty->assign('payments', array());
	$smarty->assign('sums', array());
	$smarty->assign('forecasts', array());
	$smarty->assign('balances', array());

	//get all related lists
	$oOrigin = new origin();
	$origins = $oOrigin->loadListForFilter();
	$smarty->assign('origins', $origins);

	$oStatus = new status();
	$statuses = $oStatus->loadListForFilter();
	$smarty->assign('statuses', $statuses);

	$oRecipient = new recipient();
	$recipients = $oRecipient->loadListForFilter();
	$smarty->assign('recipients', $recipients);

	$oType = new type();
	$types = $oType->loadListForFilter();
	$smarty->assign('types', $types);

	$oCurrency = new currency();
	$currencies = $oCurrency->loadListForFilter();
	$smarty->assign('currencies', $currencies);

	$currenciesWSymbol = $oCurrency->loadList();
	$smarty->assign('currenciesWSymbol', $currenciesWSymbol);

	$oMethod = new method();
	$methods = $oMethod->loadListForFilter();
	$smarty->assign('methods', $methods);

	$oLocation = new location();
	$locations = $oLocation->loadListForFilter();
	$smarty->assign('locations', $locations);

	$oOwner = new owner();
	$owners = $oOwner->loadListForFilter();
	$smarty->assign('owners', $owners);

	$limits = $oOwner->getLimits();
	$tmp = array();
	foreach( $limits as $limit ){
		$tmp[ $origins[ $limit['originFK'] ] ] = $limit['currencyFK'];
	}
	$limits = $tmp;
	$smarty->assign('limits', $limits);

	$oPayment = new payment();
	$yearsAndMonths = $oPayment->getYearsAndMonths();

	$smarty->assign('yearsAndMonths', $yearsAndMonths);

	$smarty->display('index.tpl');
} catch (Exception $e) {
	echo $e->getMessage();
	die;
}
?>