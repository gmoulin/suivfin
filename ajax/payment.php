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
					if( count($c) == 1 ) $c[1] = 0;
					$frame[ $c[0] ] = $c[1];
				}
			}
		}
	}

	switch ( $action ){
		case 'add':
		case 'update':
				//@tofinish
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
				if( !is_null($onlyDelta) && $onlyDelta !== false ){
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

					//save the payment before update
					if( $action == 'update' ) $paymentBefore = new payment($formData['id']);

					$oPayment->save();

					if( $onlyDelta ) $deltaIds[] = $oPayment->id;
					//when returning online the refresh will be done aside and only one time
					if( $offline ){
						$response = 'ok';
					} elseif( empty($frame) && empty($deltaIds) ){ //reload mode
						$response = 'ok';
					} else { //delta or timeframe change modes
						if( $action == 'add' ) $response = getFreshData( $smarty, $frame, $action, ( $onlyDelta ? $deltaIds : null ), null, $oPayment );
						else $response = getFreshData( $smarty, $frame, $action, ( $onlyDelta ? $deltaIds : null ), $paymentBefore, $oPayment );

						if( is_null($response) ) $response = 'ok';
					}
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'delete':
				/**
				 * in which case are we ?
				 * delete
				 * 		balance -> only if current month
				 * 		forecast -> only if current month or next and status 2 or 4
				 * 		sums -> month data
				 */
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des paiements : identitifant du paiement manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des paiements : identifiant incorrect.');
				}

				//in offline mode the owner is added to the requests sended when returning online
				/*$offline = filter_has_var(INPUT_POST, 'offline');
				if( !is_null($offline) && $offline !== false ){
					$owner = filter_has_var(INPUT_POST, 'owner');
					if( !is_null($owner) && $owner !== false ){
						$owner = filter_var($_POST['owner'], FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $owner === false ){
							throw new Exception('Gestion des paiements : identifiant de la personne incorrect.');
						}
						init::getInstance()->setOwner( $owner );
					}
				}*/

				$oPayment = new payment($id);
				$paymentBefore = clone $oPayment; //copy before deletion
				$oPayment->delete();

				//when returning online the refresh will be done aside and only one time
				/*if( $offline ){
					$response = 'ok';
				} else {*/
				$response = getFreshData( $smarty, null, $action, null, $paymentBefore );
				//}
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
					$response = getFreshData( $smarty, $frame, $action );
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

				$response = getFreshData( $smarty, $frame, $action );

				//nothing has changed, 304
				if( empty($response) ){
					header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
					die;
				}
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
 * @param string $action : the action name
 * @param mixed (array ) $deltaIds : array containing the modified payments ids
 * @param object $paymentBefore : payment before action save
 * @param object $paymentAfter : payment after action save
 */
function getFreshData( &$smarty, $frame, $action, $deltaIds = null, $paymentBefore = null, $paymentAfter = null ){
	/**
	 * in which case are we ?
	 * add & update
	 * 		delta (&d=1)
	 * 			payments list -> delta only
	 * 			balance -> only if payment date (new or old one) <= today
	 * 			sums -> payment month data and any future month from uri "sums" parameter
	 * 			forecasts -> only if current month or next and status 2 or 4
	 * 		timeframe change (&timeframe=)
	 * 			payments list -> payment month data
	 * 			balance -> only if payment date (new or old one) <= today and any future month from uri "sums" parameter
	 * 			sums -> payment month data (new and old one)
	 * 			forecasts -> only if current month or next and status 2 or 4
	 * delete
	 * 		payments list -> not needed
	 * 		balance -> only if payment date (new or old one) <= today
	 * 		forecast -> only if current month or next and status 2 or 4
	 * 		sums -> month data and any future month from uri "sums" parameter
	 */

	$currentMonth = ( date('d') > 24 ? date('Y-m', strtotime("+1 month")) : date('Y-m') );
	$nextMonth = ( date('d') > 24 ? date('Y-m', strtotime("+2 month")) : date('Y-m', strtotime("+1 month")) );

	$smarty->assign('monthsTranslation', init::getInstance()->getMonthsTranslation());

	$oPayment = new payment();
	$payments = null;
	$delta = null;

	//get all the timestamps for the filters and form datalists
	if( $action != 'delete' && $action != 'initNextMonth' ){
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

		$tsLabel = filter_has_var(INPUT_POST, 'tsLabel');
		if( is_null($tsLabel) || $tsLabel === false ){
			throw new Exception('Gestion des paiements : horodatage pour les labels manquant.');
		} else {
			$tsLabel = filter_var($_POST['tsLabel'], FILTER_VALIDATE_INT, array('min_range' => 0));
			if( $tsLabel === false ){
				throw new Exception('Gestion des paiements : horodatage pour les labels incorrect.');
			}
		}

		$tsLocation = filter_has_var(INPUT_POST, 'tsLocation');
		if( is_null($tsLocation) || $tsLocation === false ){
			throw new Exception('Gestion des paiements : horodatage pour les localisations manquant.');
		} else {
			$tsLocation = filter_var($_POST['tsLocation'], FILTER_VALIDATE_INT, array('min_range' => 0));
			if( $tsLocation === false ){
				throw new Exception('Gestion des paiements : horodatage pour les localisations incorrect.');
			}
		}
	}

	if( $action == 'delete' || $action == 'add' || $action == 'update' || $action == 'initNextMonth' ){
		$sumsFrame = filter_has_var(INPUT_POST, 'sums');
		if( !is_null($sumsFrame) && $sumsFrame !== false ){
			$sumsFrame = filter_var($_POST['sums'], FILTER_SANITIZE_STRING);
			if( $sumsFrame !== false ){
				$sumsFrame = explode(',', $sumsFrame);
			}
		}
	}

	if( $action == 'delete' ){
		if( ( $currentMonth == $paymentBefore->paymentMonth || $nextMonth == $paymentBefore->paymentMonth )
			&& ( $paymentBefore->statusFK == 2 || $paymentBefore->statusFK == 4 )
		){
			$tsForecast = 0;
		} else $tsForecast = -1;

		if( date('Y-m-d') >= $paymentBefore->paymentDate ){
			$tsBalance = 0;
		} else $tsBalance = -1;

		//for sums
		$frame = array( $paymentBefore->paymentMonth => 0 );
		if( !empty($sumsFrame) ){
			$m = intVal( str_replace('-', '', $paymentBefore->paymentMonth) );
			foreach( $sumsFrame as $sm ){
				if( intVal( str_replace('-', '', $sm) ) > $m ){
					$frame[$sm] = 0;
				}
			}
		}
		$sums = $oPayment->getSums( $frame );

	} elseif( $action == 'add' || $action == 'update' ){
		if( !empty($deltaIds) ){ //delta
			$delta = $oPayment->loadByIds( $deltaIds );
		} elseif( !empty($frame) ){ //timeframe change
			$payments = $oPayment->loadForTimeFrame( $frame );
		}

		$frame = array( $paymentAfter->paymentMonth => 0 );
		if( !is_null($paymentBefore) && $paymentAfter->paymentMonth != $paymentBefore->paymentMonth ){
			$frame[ $paymentBefore->paymentMonth ] = 0;
		}

		if( !empty($sumsFrame) ){
			if( !is_null($paymentBefore) ){
				$mb = intVal( str_replace('-', '', $paymentBefore->paymentMonth) );
			}
			$ma = intVal( str_replace('-', '', $paymentAfter->paymentMonth) );
			foreach( $sumsFrame as $sm ){
				$tmp = intVal( str_replace('-', '', $sm) );
				if( $tmp > $ma ){
					$frame[$sm] = 0;
				}
				if( !is_null($paymentBefore) && $tmp > $mb ){
					$frame[$sm] = 0;
				}
			}
		}
		$sums = $oPayment->getSums( $frame );

		if( date('Ymd') >= str_replace('-', '', $paymentAfter->paymentMonth) ){
			$tsBalance = 0;
		} elseif( !is_null($paymentBefore) && date('Ymd') >= str_replace('-', '', $paymentBefore->paymentDate) ){
			$tsBalance = 0;
		} else $tsBalance = -1;

		if( ( $currentMonth == $paymentAfter->paymentMonth || $nextMonth == $paymentAfter->paymentMonth )
			&& ( $paymentAfter->statusFK == 2 || $paymentAfter->statusFK == 4 )
		){
			$tsForecast = 0;
		} elseif( !is_null($paymentBefore) ){
			if( ( $currentMonth == $paymentBefore->paymentMonth || $nextMonth == $paymentBefore->paymentMonth )
				&& ( $paymentBefore->statusFK == 2 || $paymentBefore->statusFK == 4 )
			){
				$tsForecast = 0;
			}
		} else $tsForecast = -1;

	} else if( $action == 'initNextMonth' ){
		$payments = $oPayment->loadForTimeFrame( array($next_month => 0) );

		$frame = array($next_month => 0);
		if( !empty($sumsFrame) ){
			$m = intVal( str_replace('-', '', $next_month) );
			foreach( $sumsFrame as $sm ){
				if( intVal( str_replace('-', '', $sm) ) > $m ){
					$frame[$sm] = 0;
				}
			}
		}
		$sums = $oPayment->getSums( $frame );

		$tsBalance = 0;
		$tsForecast = 0;

	} else { //classic case (refresh)
		$payments = $oPayment->loadForTimeFrame( $frame );

		//timestamp for sums
		$sumsFrame = filter_has_var(INPUT_POST, 'sumsTimeframe');
		if( !is_null($sumsFrame) && $sumsFrame !== false ){
			$sumsFrame = filter_var($_POST['sumsTimeframe'], FILTER_SANITIZE_STRING);
			if( $frame !== false ){
				$tmp = explode(',', $sumsFrame);
				if( empty($tmp) ){
					$sumsFrame = null;
				} else {
					$sumsFrame = array();
					foreach( $tmp as $couple ){
						$c = explode('|', $couple);
						$sumsFrame[ $c[0] ] = $c[1];
					}
				}
			}
		}

		$sums = $oPayment->getSums( $sumsFrame );
	}

	//get the data if needed
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
	$smarty->assign('origins', $origins['data']);

	$oRecipient = new recipient();
	$recipients = $oRecipient->loadListForFilter(true, false);
	$smarty->assign('recipients', $recipients['data']);

	$oMethod = new method();
	$methods = $oMethod->loadListForFilter(true, false);
	$smarty->assign('methods', $methods['data']);

	//for the smarty templates, to get only the "inside" html
	$smarty->assign('partial', true);

	$response = array();
	if( !empty($payments) ) $response['payments'] = $payments;
	if( !empty($delta) ) $response['delta'] = $delta;

	//those 5 lists are not sent back on delete
	if( $action != 'delete' && $action != 'initNextMonth' ){
		if( !is_null($origins) && $tsOrigin != $origins['lastModified'] ){
			$origins['lastModified'] = gmdate("D, d M Y H:i:s", $origins['lastModified']) . " GMT";
			$response['origins'] = $origins;
		}

		if( !is_null($recipients) && $tsRecipient != $recipients['lastModified'] ){
			$recipients['lastModified'] = gmdate("D, d M Y H:i:s", $recipients['lastModified']) . " GMT";
			$response['recipients'] = $recipients;
		}

		if( !is_null($methods) && $tsMethod != $methods['lastModified'] ){
			$methods['lastModified'] = gmdate("D, d M Y H:i:s", $methods['lastModified'])." GMT";
			$response['methods'] = $methods;
		}

		$labels = $oPayment->loadLabelList(true, false);
		if( !is_null($labels) && $tsLabel != $labels['lastModified'] ){
			$labels['lastModified'] = gmdate("D, d M Y H:i:s", $labels['lastModified']) . " GMT";
			$response['labels'] = $labels;
		}

		$oLocation = new location();
		$locations = $oLocation->loadListForFilter(true, false);
		if( !is_null($locations) && $tsLocation != $locations['lastModified'] ){
			$locations['lastModified'] = gmdate("D, d M Y H:i:s", $locations['lastModified']) . " GMT";
			$response['locations'] = $locations;
		}
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
