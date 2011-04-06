<?php
//manage payment related ajax requests
try {
	require_once('../conf.ini.php');

	header('Content-type: application/json');

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

				$check = filter_var($_POST['id'], FILTER_SANITIZE_STRING, array('min_range' => 1));
				if( $check === false ){
					throw new Exception('Gestion des paiements : identifiant incorrect.');
				}

				$oPayment = new payment($id);

				if( empty($oPayment->id) ){
					throw new Exception('Gestion des paiements : identitifant du paiement incorrect.');
				}

				$response = $oPayment;
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

				//get all related lists, normaly they are stashed
				$oOrigin = new origin();
				$origins = $oOrigin->loadListForFilter();

				$oStatus = new status();
				$statuses = $oStatus->loadListForFilter();

				$oRecipient = new recipient();
				$recipients = $oRecipient->loadListForFilter();

				$oType = new type();
				$types = $oType->loadListForFilter();

				$oCurrency = new currency();
				$currencies = $oCurrency->loadListForFilter();

				$oMethod = new method();
				$methods = $oMethod->loadListForFilter();

				$oLocation = new location();
				$locations = $oLocation->loadListForFilter();

				$oOwner = new owner();
				$owners = $oOwner->loadListForFilter();

				//generate the payments details
				$partial = true;
				include(SF_PATH.'/list/payment.php');
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

	echo json_encode($response);
	die;

} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo json_encode($e->getMessage());
	die;
}
?>