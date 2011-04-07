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
		$owner = filter_input(INPUT_GET, 'field', FILTER_SANITIZE_NUMBER_INT);
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
		$tmp[$limit['origin_id']] = $limit['currency_id'];
	}
	$limits = $tmp;
	$smarty->assign('limits', $limits);

	$yearsAndMonths = $oPayment->getYearsAndMonths();
	$smarty->assign('yearsAndMonths', $yearsAndMonths);

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
	$smarty->assign('lang_months', $lang_months);

	$smarty->display('index.tpl');
} catch (Exception $e) {
	echo $e->getMessage();
	die;
}
?>
