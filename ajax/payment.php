<?php
//manage payment related ajax requests
try {
	include('../conf.ini.php');

	header('Content-type: application/json');

	$action = filter_has_var(INPUT_POST, 'action');
	if( is_null($action) || $action === false ){
		throw new Exception('Gestion des paiements : action manquante.');
	}

	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
	if( $action === false ){
		throw new Exception('Gestion des paiements : action incorrecte.');
	}

	//check the request headers for "If-Modified-Since"
	$request_headers = apache_request_headers();
	$browserHasCache = ( array_key_exists('If-Modified-Since', $request_headers) ? true : false );
	if( $browserHasCache ){
		$modifiedSince = strtotime($request_headers['If-Modified-Since']);
	}

	$lastModified = null;

	$frame = filter_has_var(INPUT_POST, 'timeframe');
	if( !is_null($frame) && $frame !== false ){
		$frame = filter_var($_POST['timeframe'], FILTER_SANITIZE_STRING);
		if( $frame !== false ){
			$tmp = explode(',', $frame);
			if( empty($tmp) ){
				$frame = null;
			}
		}
	}

	switch ( $action ){
		case 'add':
		case 'update':
				//in offline mode the owner is added to the requests sended when returning online
				$offline = filter_has_var(INPUT_POST, 'offline');
				if( !is_null($offline) && $offline !== false ){
					$owner = filter_has_var(INPUT_POST, 'owner');
					if( !is_null($owner) && $owner !== false ){
						$owner = filter_var($_POST['owner'], FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $owner === false ){
							throw new Exception('Gestion des paiements : identifiant de la personne incorrect.');
						}
						init::getInstance()->setOwner( $owner );
					}
				}

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

					//when returning online the refresh will be done aside and only one time
					if( $offline ){
						$response = 'ok';
					} else {
						$response = getFreshData( $smarty, $frame );
						if( is_null($response) ) $response = 'ok';
					}
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

				//in offline mode the owner is added to the requests sended when returning online
				$offline = filter_has_var(INPUT_POST, 'offline');
				if( !is_null($offline) && $offline !== false ){
					$owner = filter_has_var(INPUT_POST, 'owner');
					if( !is_null($owner) && $owner !== false ){
						$owner = filter_var($_POST['owner'], FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $owner === false ){
							throw new Exception('Gestion des paiements : identifiant de la personne incorrect.');
						}
						init::getInstance()->setOwner( $owner );
					}
				}

				$oPayment = new payment($id);
				$oPayment->delete();

				//when returning online the refresh will be done aside and only one time
				if( $offline ){
					$response = 'ok';
				} else {
					$response = getFreshData( $smarty, $frame );
					if( is_null($response) ) $response = 'ok';
				}
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
		case 'initNextMonth':
				$oPayment = new payment();
				$oPayment->initNextMonthPayment();

				if( !is_null($frame) ){
					$response = getFreshData( $smarty, $frame );
				} else {
					$response = 'ok';
				}
			break;
		case 'refresh':
				//always get the owner, the cache manifest does not request index.php all the time
				$owner = filter_has_var(INPUT_POST, 'owner');
				if( is_null($owner) || $owner === false ){
					throw new Exception('Gestion des paiements : identifiant de la personne manquant.');
				} else {
					$owner = filter_var($_POST['owner'], FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $owner === false ){
						throw new Exception('Gestion des paiements : identifiant de la personne incorrect.');
					}
					init::getInstance()->setOwner( $owner );
				}

				if( $browserHasCache && $modifiedSince != 0 ){
					$oPayment = new payment();
					$oEvolution = new evolution();

					//get timestamp for each part
					$tsPayment = $oPayment->loadForTimeFrame($frame, null, true);
					$tsSums = $oPayment->getSums($frame, null, true);
					$tsForecasts = $oPayment->getForecasts(null, true);
					$tsBalances = $oEvolution->getTodayBalances(null, true);
					if( is_null($tsForecasts) ) $tsForecasts = 0; //null when there is no forecast
					if( is_null($tsBalances) ) $tsBalances = 0; //null after evolution table reset

					if( !is_null($tsPayment) && !is_null($tsSums) ){
						$maxTs = max( $tsPayment, $tsSums, $tsForecasts, $tsBalances );
						//browser has list in cache and list was not modified
						if( $modifiedSince == $maxTs ){
							header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
							die;
						}
					}
				}

				list($lastModified, $response) = getFreshData( $smarty, $frame, true );
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

							if( $browserHasCache && $modifiedSince != 0 ){
								$ts = $oPayment->getExpenseData(null, true);
								//browser has list in cache and list was not modified
								if( $modifiedSince == $ts ){
									header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
									die;
								}
							}

							list($lastModified, $response) = $oPayment->getExpenseData(true);
						break;
					case 'evolution':
							$oEvolution = new Evolution();

							if( $browserHasCache && $modifiedSince != 0 ){
								$ts = $oEvolution->getEvolutionData(null, true);
								//browser has list in cache and list was not modified
								if( $modifiedSince == $ts ){
									header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
									die;
								}
							}

							list($lastModified, $response) = $oEvolution->getEvolutionData(true);
						break;
					case 'recipient':
							$oPayment = new payment();

							if( $browserHasCache && $modifiedSince != 0 ){
								$ts = $oPayment->getRecipientData(null, true);
								//browser has list in cache and list was not modified
								if( $modifiedSince == $ts ){
									header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
									die;
								}
							}

							list($lastModified, $response) = $oPayment->getRecipientData(true);
						break;
				}
			break;
		default:
			throw new Exception('Gestion des paiements : action non reconnue.');
	}

	if( !is_null($lastModified) ) header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified) . " GMT");

	echo json_encode($response);
	die;

} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo json_encode($e->getMessage());
	die;
}

/*
 * @param object $smarty
 * @param mixed (string | null) $frame : months separated by commas (format yyyy-mm)
 * @param boolean $getTs : flag for returning the biggest timestamp from each part
 */
function getFreshData( &$smarty, $frame, $getTs = false ){
	if( empty($frame) ){
		if( $getTs ) return array(null, null);

		return null;
	}

	$smarty->assign('monthsTranslation', init::getInstance()->getMonthsTranslation());

	$oPayment = new payment();
	list($tsPayments, $payments) = $oPayment->loadForTimeFrame($frame, true);
	list($tsSums, $sums) = $oPayment->getSums($frame, true);
	$smarty->assign('sums', $sums);
	list($tsForecasts, $forecasts) = $oPayment->getForecasts(true);
	$smarty->assign('forecasts', $forecasts);

	$oEvolution = new evolution();
	list($tsBalances, $balances) = $oEvolution->getTodayBalances(true);
	$smarty->assign('balances', $balances);

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
	$location = $oLocation->loadListForFilter();
	$smarty->assign('locations', $location);

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
	$response['balances'] = $smarty->fetch('balance.tpl');

	if( $getTs ){
		$max = max($tsPayments, $tsSums, $tsForecasts, $tsBalances);
		return array($max, $response);
	}

	return $response;
}
?>