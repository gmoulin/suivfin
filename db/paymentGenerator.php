<?php
require('../conf.ini.php');

set_time_limit(0);
ini_set('memory_limit', '512M');
header('Content-type: text/html; charset=UTF-8');

try {
	$nbPayments = ( isset($_GET['nb']) && !empty($_GET['nb']) ? $_GET['nb'] : 2000 );

	$clean = ( isset($_GET['clean']) ? $_GET['clean'] : 0 );

	if( $clean ){
		$db = init::getInstance()->dbh();

		$cleanup = $db->prepare("DELETE FROM payment WHERE label LIKE 'test paiement%'");
		$cleanup->execute();

		$cleanup = $db->prepare("DELETE FROM recipient WHERE id NOT IN (SELECT recipientFK FROM payment)");
		$cleanup->execute();
	}

	//get all related lists
	$oStatus = new status();
	$statuses = $oStatus->loadListForFilter();
	$statusesLength = count($statuses);

	$recipients = array('Migros', 'Marché', 'Essence', 'Free', 'EDF', 'Generali', 'BNP', 'Swisscom');
	$recipientsLength = count($recipients) - 1;

	$oType = new type();
	$types = $oType->loadListForFilter();
	$typesLength = count($types);

	$oCurrency = new currency();
	$currencies = $oCurrency->loadListForFilter();
	$currenciesLength = count($currencies);

	$oMethod = new method();
	$methods = $oMethod->loadListForFilter();
	$methodsLength = count($methods) - 1;

	$oLocation = new location();
	$locations = $oLocation->loadListForFilter();
	$locationsLength = count($locations) - 1;

	$oOwner = new owner();
	$owners = $oOwner->loadListForFilter();
	$limits = array();
	$limitsLength = array();

	$date = new DateTime();
	$date_min = strtotime($date->format('Y-m-d'));

	$date->add(new DateInterval('P18M')); //add 18 months
	$date_max = strtotime($date->format('Y-m-d'));

	$n = 0;
	$p = new payment();

	foreach( $owners as $owner => $name ){
		for( $i = 0; $i < $nbPayments; $i++ ){
			init::getInstance()->setOwner( $owner );

			if( !isset($limits[$owner]) ){
				$limits[$owner] = $oOwner->getLimits();
				$limitsLenght[$owner] = count($limits[$owner]) - 1;
			}

			$limit = rand(0, $limitsLenght[$owner]);
			$origin = $limits[$owner][$limit]['originFK'];
			$currency = $limits[$owner][$limit]['currencyFK'];

			$_POST = array(
				'action'		=> 'add',
				'id'			=> null,
				'label'			=> 'test paiement '.$n,
				'paymentDate'	=> date('d/m/Y', rand($date_min, $date_max)),
				'amount'		=> floatval(rand(10, 1000) + (rand(1, 99) / 100)),
				'comment'		=> 'test automatisé d\'ajout de paiement, numéro '.$n,
				'recurrent'		=> rand(0, 1),
				'recipientFK'	=> $recipients[rand(1, $recipientsLength)],
				'typeFK'		=> rand(1, $typesLength),
				'currencyFK'	=> $currency,
				'methodFK'		=> $methods[rand(1, $methodsLength)],
				'originFK'		=> $origin,
				'statusFK'		=> rand(1, $statusesLength),
				'locationFK'	=> $locations[rand(1, $locationsLength)],
			);

			// /!\ comment "filter_has_var" foreach test in payment class
			$formData = $p->checkAndPrepareFormData();

			if( empty($formData['errors']) ){
				$p->id = null; //security to force insert
				$p->save();
				$n++;
			} else {
				echo '<pre>';
				print_r($formData['errors']);
				echo '</pre>';
				die;
			}
		}
	}

	echo '<br />'.$n.' payments generated';

} catch( Exception $e ){
	echo $e->getMessage();
	die;
}
?>