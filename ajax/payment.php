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
					//if it's a transfert, create the mirror deposit
					if( empty($oPayment->id) && $oPayment->typeFK == 3 ){
						//is the recipient a valid origin ?
						$oRecipient = new recipient($oPayment->recipientFK);
						$origin = origin::existsByLabel($oRecipient->name);
						if( $origin ){
							$mirror = clone $oPayment;

							$mirror->typeFK = 1; //dépôt
							$mirror->recipientFK = $mirror->originFK;
							$mirror->originFK = $origin;
							$mirror->statusFK = 3; //à vérifier

							$oOwner = new owner();
							$limits = $oOwner->getLimits(true);
							foreach( $limits as $limit ){
								if( $mirror->originFK = $limit['origin_id'] ){
									$currency = $limit['currency_id'];
									$owner = $limit['owner_id'];
								}
							}


							if( $mirror->currencyFK != $currency ){
								if( !empty($bis->comment) ) $mirror->comment .= "\n";
								$mirror->comment .= "Montant à vérifier";
							}
							$mirror->currencyFK = $currency;
							$mirror->ownerFK = $owner;

							$mirror->save();
						}
					}

					$oPayment->save();
					$response = 'ok';
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
				$response = "ok";
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
		case 'sum' :
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
		case 'forecast' :
				$oPayement = new payment();
				$forecasts = $oPayement->getForecasts();
				$smarty->assign('forecasts', $forecasts);

				//get all related lists, normaly they are stashed
				$oStatus = new status();
				$statuses = $oStatus->loadListForFilter();
				$smarty->assign('statuses', $statuses);

				$oCurrency = new currency();
				$currencies = $oCurrency->loadListForFilter();
				$smarty->assign('currencies', $currencies);

				//generate the sums details
				$smarty->assign('partial', true);
				$smarty->display('forecast.tpl');
				die;
			break;
		/*
		case 'graph':
			//@todo develop the filters
				$oPayement = new payment();
				$response = $oArtist->loadList('paymentDate');
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
?>