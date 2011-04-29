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
		case 'refresh':
				$response = getFreshData( $smarty );
			break;
		case 'initNextMonth':
				$oPayment = new payment();
				$oPayment->initNextMonthPayment();

				$response = getFreshData( $smarty );
			break;
		case 'chart':
				$type = filter_has_var(INPUT_POST, 'type');
				if( is_null($type) || $type === false ){
					throw new Exception('Gestion des paiements : type du graphique manquant.');
				}

				$type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
				if( $type === false ){
					throw new Exception('Gestion des paiements : type du graphique incorrect.');
				}

				switch($type){
					case 'expense':
					default:
							$oPayment = new payment();
							$response = $oPayment->getExpenseData();
						break;
					case 'evolution':
							$oEvolution = new Evolution();
							$response = $oEvolution->getEvolutionData();
						break;
					case 'recipient':
							$oPayment = new payment();
							$response = $oPayment->getRecipientData();
						break;
				}
			break;
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

	$smarty->assign('monthsTranslation', init::getInstance()->getMonthsTranslation());

	$oPayement = new payment();
	$payments = $oPayement->loadForTimeFrame($frame);
	$smarty->assign('sums', $oPayement->getSums($frame));
	$smarty->assign('forecasts', $oPayement->getForecasts());

	$oOwner = new owner();
	$smarty->assign('owners', $oOwner->loadListForFilter());
	$smarty->assign('owner', $oOwner->getOwner());

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
	$smarty->assign('locations', $oLocation->loadListForFilter());

	//generate the payments details
	$smarty->assign('partial', true);

	$response = array();
	$response['payments'] = $payments;
	$response['origins'] = $origins;
	$response['statuses'] = $statuses;
	$response['recipients'] = $recipients;
	$response['types'] = $types;
	$response['currenciesWSymbol'] = $currenciesWSymbol;
	$response['methods'] = $methods;
	$response['sums'] = $smarty->fetch('sum.tpl');
	$response['forecasts'] = $smarty->fetch('forecast.tpl');

	return $response;
}
?>