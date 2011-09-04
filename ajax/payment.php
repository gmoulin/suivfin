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

	$frame = filter_has_var(INPUT_POST, 'timeframe');
	if( !is_null($frame) && $frame !== false ){
		$frame = filter_var($_POST['timeframe'], FILTER_SANITIZE_STRING);
		if( $frame !== false ){
			$tmp = explode(',', $frame);
			if( empty($tmp) ){
				$frame = null;
			} else {
				$frame = array();
				foreach( $tmp as $couple ){
					$c = explode('|', $couple);
					$frame[ $c[0] ] = $c[1];
				}
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

				$onlyDelta = filter_has_var(INPUT_POST, 'd');
				if( !is_null($owner) && $owner !== false ){
					$onlyDelta = filter_var($_POST['d'], FILTER_VALIDATE_INT, array('min_range' => 1, 'max_range' => 1));
				}

				$oPayment = new payment();

				$formData = $oPayment->checkAndPrepareFormData();

				$deltaIds = array();

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

							if( $onlyDelta ) $deltaIds[] = $mirror->id;
						}
					}

					$oPayment->save();
					if( $onlyDelta ) $deltaIds[] = $oPayment->id;

					//when returning online the refresh will be done aside and only one time
					if( $offline ){
						$response = 'ok';
					} else {
						$response = getFreshData( $smarty, $frame, ( $onlyDelta ? $deltaIds : null ) );
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
					$response = getFreshData( $smarty, $frame, 'delete' );
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

				$tsForecast = filter_has_var(INPUT_POST, 'tsForecast');
				if( is_null($tsForecast) || $tsForecast === false ){
					$tsForecast = -1;
				} else {
					$tsForecast = filter_var($_POST['tsForecast'], FILTER_VALIDATE_INT, array('min_range' => 0));
					if( $tsForecast === false ){
						throw new Exception('Gestion des paiements : horodatage pour les prévisions incorrect.');
					}
				}

				$tsBalance = filter_has_var(INPUT_POST, 'tsBalance');
				if( is_null($tsBalance) || $tsBalance === false ){
					$tsBalance = -1;
				} else {
					$tsBalance = filter_var($_POST['tsBalance'], FILTER_VALIDATE_INT, array('min_range' => 0));
					if( $tsBalance === false ){
						throw new Exception('Gestion des paiements : horodatage pour le solde incorrect.');
					}
				}

				$tsOrigin = filter_has_var(INPUT_POST, 'tsOrigin');
				if( is_null($tsOrigin) || $tsOrigin === false ){
					throw new Exception('Gestion des paiements : horodatage pour les origines manquant.');
				} else {
					$tsOrigin = filter_var($_POST['tsOrigin'], FILTER_VALIDATE_INT, array('min_range' => 0));
					if( $tsOrigin === false ){
						throw new Exception('Gestion des paiements : horodatage pour les origines incorrect.');
					}
				}

				$tsRecipient = filter_has_var(INPUT_POST, 'tsRecipient');
				if( is_null($tsRecipient) || $tsRecipient === false ){
					throw new Exception('Gestion des paiements : horodatage pour les bénéficiaires manquant.');
				} else {
					$tsRecipient = filter_var($_POST['tsRecipient'], FILTER_VALIDATE_INT, array('min_range' => 0));
					if( $tsRecipient === false ){
						throw new Exception('Gestion des paiements : horodatage pour les bénéficiaires incorrect.');
					}
				}

				$tsMethod = filter_has_var(INPUT_POST, 'tsMethod');
				if( is_null($tsMethod) || $tsMethod === false ){
					throw new Exception('Gestion des paiements : horodatage pour les méthodes manquant.');
				} else {
					$tsMethod = filter_var($_POST['tsMethod'], FILTER_VALIDATE_INT, array('min_range' => 0));
					if( $tsMethod === false ){
						throw new Exception('Gestion des paiements : horodatage pour les méthodes incorrect.');
					}
				}

				$response = getFreshData( $smarty, $frame, null, $tsForecast, $tsBalance, $tsRecipient, $tsOrigin, $tsMethod );
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
							$oEvolution = new evolution();

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

	echo json_encode($response);
	die;

} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo json_encode($e->getMessage());
	die;
}

/*
 * @param object $smarty
 * @param mixed (string | null) $frame : key month (YYYY-MM), value timestamp
 * @param mixed (array | 'delete') $deltaIds : array containing the modified payments ids or the action 'delete' (will return no payments)
 * @param int $tsForecast : unix timestamp for the forecast part
 * @param int $tsBalance : unix timestamp for the balance part
 * @param int $tsRecipient : unix timestamp for the recipients list
 * @param int $tsOrigin : unix timestamp for the origins list
 * @param int $tsMethod : unix timestamp for the methods list
 */
function getFreshData( &$smarty, $frame, $deltaIds = null, $tsForecast, $tsBalance, $tsRecipient, $tsOrigin, $tsMethod ){
	if( empty($frame) ){
		return null;
	}

	$smarty->assign('monthsTranslation', init::getInstance()->getMonthsTranslation());

	$oPayment = new payment();

	$tsPayments = 0;
	if( is_null($deltaIds) ){
		$payments = $oPayment->loadForTimeFrame( $frame );
	} elseif( is_array($deltaIds) ) {
		$delta = $oPayment->loadByIds( $deltaIds );
	}

	$sums = $oPayment->getSums( $frame );

	if( $tsForecast >= 0 ){
		$forecasts = $oPayment->getForecasts( $tsForecast );
		if( !empty($forecasts) ) $smarty->assign('forecasts', $forecasts['data']);
	}

	if( $tsBalance >= 0 ){
		$oEvolution = new evolution();
		$balances = $oEvolution->getTodayBalances( $tsBalance );
		if( !empty($balances) ) $smarty->assign('balances', $balances['data']);
	}

	$oOwner = new owner();
	$smarty->assign('owners', $oOwner->loadListForFilter());
	$smarty->assign('owner', $oOwner->getOwner());

	//get all related lists, normaly they are stashed

	//the next 3 lists do not change
	$oStatus = new status();
	$statuses = $oStatus->loadListForFilter();
	$smarty->assign('statuses', $statuses);

	$oType = new type();
	$types = $oType->loadListForFilter();
	$smarty->assign('types', $types);

	$oCurrency = new currency();
	$currenciesWSymbol = $oCurrency->loadList();
	$smarty->assign('currenciesWSymbol', $currenciesWSymbol);

	//the next 3 lists can change with new payments, so timestamp is used
	$oOrigin = new origin();
	$origins = $oOrigin->loadListForFilter(true, false);
	$smarty->assign('origins', $origins[1]);

	$oRecipient = new recipient();
	$recipients = $oRecipient->loadListForFilter(true, false, $tsRecipient);
	$smarty->assign('recipients', $recipients);

	$oMethod = new method();
	$methods = $oMethod->loadListForFilter(true, false, $tsMethod);
	$smarty->assign('methods', $methods);

	//generate the payments details
	$smarty->assign('partial', true);

	$response = array();
	if( is_null($deltaIds) ){
		$response['payments'] = $payments;
	} elseif( is_array($deltaIds) ){
		$response['delta'] = $delta;
	}

	if( !is_null($origins) && $tsOrigin != $origins[0] ){
		$origins[0] = gmdate("D, d M Y H:i:s", $origins[0]) . " GMT";
		$response['origins'] = $origins;
	}

	if( !is_null($recipients) && $tsRecipient != $recipients[0] ){
		$recipients[0] = gmdate("D, d M Y H:i:s", $recipients[0]) . " GMT";
		$response['recipients'] = $recipients;
	}

	if( !is_null($methods) && $tsMethod != $methods[0] ){
		$methods[0] = gmdate("D, d M Y H:i:s", $methods[0])." GMT";
		$response['methods'] = $methods;
	}

	if( !empty($sums) ){
		foreach( $sums as $month => $info ){
			$smarty->assign('sums', $info['sums']);
			$smarty->assign('sumMonth', $month);
			$response['sums'][$month]['lastModified'] = gmdate("D, d M Y H:i:s", $info['lastModified'])." GMT";
			$response['sums'][$month]['html'] = $smarty->fetch('sum.tpl');
		}
	}

	if( $tsForecast >= 0 && !empty($forecasts) ){
		$response['forecasts']['lastModified'] = gmdate("D, d M Y H:i:s", $forecasts['lastModified'])." GMT";
		$response['forecasts']['html'] = $smarty->fetch('forecast.tpl');
	}

	if( $tsBalance >= 0 && !empty($balances) ){
		$response['balances']['lastModified'] = gmdate("D, d M Y H:i:s", $balances['lastModified'])." GMT";
		$response['balances']['html'] = $smarty->fetch('balance.tpl');
	}

	return $response;
}
?>
