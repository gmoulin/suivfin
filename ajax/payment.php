<?php
//manage payment related ajax requests
try {
	include('../conf.ini.php');

	$action = filter_has_var(INPUT_POST, 'action');
	if( is_null($action) || $action === false ){
		throw new Exception('Gestion des paiements : action manquante.');
	}

	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
	if( $action === false ){
		throw new Exception('Gestion des paiements : action incorrecte.');
	}

	switch ( $action ){
		case 'add':
		case 'update':
				$oPayment = new payment();

				$formData = $oPayment->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					//if it's a new transfert, create the mirror deposit
					if( $action == 'add' && empty($oPayment->id) && $oPayment->typeFK == 3 ){
						//is the recipient a valid origin ? (also used for limitation)
						$oRecipient = new recipient($oPayment->recipientFK);
						$deposit_recipient = origin::existsByLabel($oRecipient->name);
						if( $deposit_recipient ){
							$mirror = clone $oPayment;

							$mirror->typeFK = 1; //deposit

							//no need to swap origin and recipient as deposits are summed by recipient

							$mirror->statusFK = 3; //to check

							$oOwner = new owner();
							$limits = $oOwner->getLimits(true);
							foreach( $limits as $limit ){
								if( $deposit_recipient == $limit['originFK'] ){
									$currency = $limit['currencyFK'];
									break;
								}
							}

							if( $mirror->currencyFK != $currency ){
								$mirror->currencyFK = $currency;
								if( strlen($mirror->comment) ) $mirror->comment .= "\n";
								$mirror->comment .= "Montant à vérifier";
							}

							//the owner for the deposit may not be the current owner
							$mirror_owner = filter_has_var(INPUT_POST, 'transfert_ownerFK');
							if( is_null($mirror_owner) || $mirror_owner === false ){
								throw new Exception('Gestion des paiements : identitifant du receveur manquant.');
							}

							$mirror_owner = filter_var($_POST['transfert_ownerFK'], FILTER_VALIDATE_INT, array('min_range' => 1));
							if( $mirror_owner === false ){
								throw new Exception('Gestion des paiements : identifiant du receveur incorrect.');
							}

							if( owner::existsById($mirror_owner) ){
								$mirror->ownerFK = $mirror_owner;
							} else {
								throw new Exception('Gestion des paiements : identifiant du receveur inconnu.');
							}

							$mirror->save();
						}
					}

					$oPayment->save();
					$response = getFreshData( $smarty );
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'delete':
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des paiements : identitifant du paiement manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des paiements : identifiant incorrect.');
				}

				$oPayment = new payment($id);
				$oPayment->delete();

				$response = getFreshData( $smarty );
			break;
		case 'get':
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des paiements : identitifant du paiement manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des paiements : identifiant incorrect.');
				}

				$oPayment = new payment($id);

				if( $oPayment->id == 0 ){
					throw new Exception('Gestion des paiements : identitifant du paiement incorrect.');
				}

				$response = $oPayment->getData();
			break;
		case 'list':
				$frame = filter_has_var(INPUT_POST, 'timeframe');
				if( is_null($frame) || $frame === false ){
					throw new Exception('Gestion des paiements : liste des mois manquant.');
				}

				$frame = filter_var($_POST['timeframe'], FILTER_SANITIZE_STRING);
				if( $frame === false ){
					throw new Exception('Gestion des paiements : liste des mois incorrecte.');
				}

				$tmp = explode(',', $frame);
				if( empty($tmp) ){
					throw new Exception('Gestion des paiements : liste des mois incorrecte.');
				}

				$oPayement = new payment();
				$payments = $oPayement->loadForTimeFrame($frame);
				$smarty->assign('payments', $payments);

				//get all related lists, normaly they are stashed
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
				$currenciesWSymbol = $oCurrency->loadList();
				$smarty->assign('currenciesWSymbol', $currenciesWSymbol);

				$oMethod = new method();
				$methods = $oMethod->loadListForFilter();
				$smarty->assign('methods', $methods);

				$oLocation = new location();
				$locations = $oLocation->loadListForFilter();
				$smarty->assign('locations', $locations);

				//generate the payments details
				$smarty->assign('partial', true);
				$smarty->display('payment.tpl');
				die;
			break;
		case 'sum':
				$frame = filter_has_var(INPUT_POST, 'timeframe');
				if( is_null($frame) || $frame === false ){
					throw new Exception('Gestion des paiements : liste des mois manquant.');
				}

				$frame = filter_var($_POST['timeframe'], FILTER_SANITIZE_STRING);
				if( $frame === false ){
					throw new Exception('Gestion des paiements : liste des mois incorrecte.');
				}

				$tmp = explode(',', $frame);
				if( empty($tmp) ){
					throw new Exception('Gestion des paiements : liste des mois incorrecte.');
				}


				$oOwner = new owner();
				$owners = $oOwner->loadListForFilter();
				$smarty->assign('owners', $owners);

				$owner = $oOwner->getOwner();
				$smarty->assign('owner', $owner);

				$oPayement = new payment();
				$sums = $oPayement->getSums($frame);
				$smarty->assign('sums', $sums);

				//get all related lists, normaly they are stashed
				$oOrigin = new origin();
				$origins = $oOrigin->loadListForFilter();
				$smarty->assign('origins', $origins);

				$oType = new type();
				$types = $oType->loadListForFilter();
				$smarty->assign('types', $types);

				$oCurrency = new currency();
				$currenciesWSymbol = $oCurrency->loadList();
				$smarty->assign('currenciesWSymbol', $currenciesWSymbol);

				//generate the sums details
				$smarty->assign('partial', true);
				$smarty->display('sum.tpl');
				die;
			break;
		case 'forecast':
				$oPayement = new payment();
				$forecasts = $oPayement->getForecasts();
				$smarty->assign('forecasts', $forecasts);

				//get all related lists, normaly they are stashed
				$oStatus = new status();
				$statuses = $oStatus->loadListForFilter();
				$smarty->assign('statuses', $statuses);

				$oCurrency = new currency();
				$currenciesWSymbol = $oCurrency->loadList();
				$smarty->assign('currenciesWSymbol', $currenciesWSymbol);

				//generate the sums details
				$smarty->assign('partial', true);
				$smarty->display('forecast.tpl');
				die;
			break;
		case 'refresh':
				$response = getFreshData( $smarty );
			break;
		case 'initNextMonth':
				$oPayment = new payment();
				$oPayment->initNextMonthPayment();

				$response = getFreshData( $smarty );
			break;
		/*
		case 'graph':
			multiple type of graphs with same data
			zoomable parts (year -> month -> day)

			use canvas, maybe with highcharts.js

			break;
		*/
		default:
			throw new Exception('Gestion des paiements : action non reconnue.');
	}

	header('Content-type: application/json');
	echo json_encode($response);
	die;

} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo json_encode($e->getMessage());
	die;
}

function getFreshData( &$smarty ){
	$frame = filter_has_var(INPUT_POST, 'timeframe');
	if( is_null($frame) || $frame === false ){
		return 'ok';
	}

	$frame = filter_var($_POST['timeframe'], FILTER_SANITIZE_STRING);
	if( $frame === false ){
		return 'ok';
	}

	$tmp = explode(',', $frame);
	if( empty($tmp) ){
		return 'ok';
	}

	$oPayement = new payment();
	$smarty->assign('payments', $oPayement->loadForTimeFrame($frame));
	$smarty->assign('sums', $oPayement->getSums($frame));
	$smarty->assign('forecasts', $oPayement->getForecasts());

	$oOwner = new owner();
	$smarty->assign('owners', $oOwner->loadListForFilter());
	$smarty->assign('owner', $oOwner->getOwner());

	//get all related lists, normaly they are stashed
	$oOrigin = new origin();
	$smarty->assign('origins', $oOrigin->loadListForFilter());

	$oStatus = new status();
	$smarty->assign('statuses', $oStatus->loadListForFilter());

	$oRecipient = new recipient();
	$smarty->assign('recipients', $oRecipient->loadListForFilter());

	$oType = new type();
	$smarty->assign('types', $oType->loadListForFilter());

	$oCurrency = new currency();
	$smarty->assign('currenciesWSymbol', $oCurrency->loadList());

	$oMethod = new method();
	$smarty->assign('methods', $oMethod->loadListForFilter());

	$oLocation = new location();
	$smarty->assign('locations', $oLocation->loadListForFilter());

	//generate the payments details
	$smarty->assign('partial', true);

	$response = array();
	$response['payments'] = $smarty->fetch('payment.tpl');
	$response['sums'] = $smarty->fetch('sum.tpl');
	$response['forecasts'] = $smarty->fetch('forecast.tpl');

	return $response;
}
?>