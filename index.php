<?php
try {
	require_once('conf.ini.php');

	//metadata
	$metadata['description'] = 'application de gestion des paiements';
	$metadata['motscles'] = 'gestion, débit, crédit, transfert, compte, banque, monnaie, balance, paiement';
	$smarty->assign('metadata', $metadata);

	$lang = 'fr';
	$smarty->assign('lang', $lang);

	$smarty->assign('css', filemtime( SF_PATH.'/css/style.css' ));
	$smarty->assign('js', filemtime( SF_PATH.'/js/script.js' ));

	if( !filter_has_var(INPUT_GET, 'owner') ){
		//@todo choose owner overlay
		$owner = 1; //default
	} else {
		$owner = filter_input(INPUT_GET, 'owner', FILTER_SANITIZE_NUMBER_INT);
		if( is_null($owner) || $owner === false ){
			throw new Exception('Gestion des comptes : compte incorrect.');
		}
	}

	init::getInstance()->setOwner( $owner );
	$smarty->assign('owner', $owner);

	$currentYear = date('Y');
	$smarty->assign('currentYear', $currentYear);
	$currentMonth = date('m');
	$smarty->assign('currentMonth', $currentMonth);

	//get payments for current month
	$oPayment = new payment();
	$payments = $oPayment->loadForCurrentMonth();
	$smarty->assign('payments', $payments);
	$sums = $oPayment->getSums( $currentYear.'-'.$currentMonth );
	$smarty->assign('sums', $sums);
	$forecasts = $oPayment->getForecasts();
	$smarty->assign('forecasts', $forecasts);

	//get all related lists
	$oOrigin = new origin();
	$origins = $oOrigin->loadListForFilterByOwner();
	$origins_full = $oOrigin->loadListForFilter();
	$smarty->assign('origins', $origins);

	$oStatus = new status();
	$statuses = $oStatus->loadListForFilter();
	$smarty->assign('statuses', $statuses);

	$oRecipient = new recipient();
	$recipients = $oRecipient->loadListForFilterByOwner();
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
	$methods = $oMethod->loadListForFilterByOwner();
	$smarty->assign('methods', $methods);

	$oLocation = new location();
	$locations = $oLocation->loadListForFilterByOwner();
	$smarty->assign('locations', $locations);

	$oOwner = new owner();
	$owners = $oOwner->loadListForFilter();
	$smarty->assign('owners', $owners);

	$limits = $oOwner->getLimits(true);
	$tmp = array();
	foreach( $limits as $limit ){
		$tmp[ $origins_full[ $limit['origin_id'] ] ] = $limit['currency_id'];
	}
	$limits = $tmp;
	$smarty->assign('limits', $limits);

	$yearsAndMonths = $oPayment->getYearsAndMonths();
	$smarty->assign('yearsAndMonths', $yearsAndMonths);

	$smarty->display('index.tpl');
} catch (Exception $e) {
	echo $e->getMessage();
	die;
}
?>
