<?php
//manage dropdown lists content for ajax requests
try {
    require_once('../conf.ini.php');

    header('Content-type: application/json');
	$expires = 60*60*24*7; //1 weeks
	header('Expires: '.gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT'); //always send else the next request will not have "If-Modified-Since" header
	header('Cache-Control: max-age=' . $expires.', must-revalidate'); //must-revalidate to force browser to used the cache control rules sended

	if( !filter_has_var(INPUT_GET, 'field') ){
		throw new Exception('Chargement des listes déroulantes : paramètre manquante.');
	} else {
		$field = filter_input(INPUT_GET, 'field', FILTER_SANITIZE_STRING);
		if( is_null($field) || $field === false ){
			throw new Exception('Chargement des listes déroulantes : liste incorrecte.');
		}

		//check the request headers for "If-Modified-Since"
		$request_headers = apache_request_headers();
		$browserHasCache = ( array_key_exists('If-Modified-Since', $request_headers) ? true : false );
		if( $browserHasCache ){
			$modifiedSince = strtotime($request_headers['If-Modified-Since']);
		}

		$lastModified = 0;
		$target = $field;

		if( $field == 'labelList' ) $target = 'payment';
		elseif( strpos($field, 'List') !== false ) $target = substr($field, 0, -4);
		elseif( strpos($field, '_filter') !== false ) $target = substr($field, 0, -7);

		if( $browserHasCache ){
			$ts = new list_timestamp($target);
			if( !empty($ts->id) ){
				$lastModified = strtotime($ts->stamp);
				//browser has list in cache and list was not modified
				if( $modifiedSince == $lastModified ){
					header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
					die;
				}
			} else { //create the timestamp
				$ts->id = $target;
				$ts->save();

				$ts->load($target);
				$lastModified = strtotime($ts->stamp);
			}
		}

		$list = array();
		switch ( $field ){
			/* form fields */
			case 'labelList':
					$oPayement = new Payment();
					$labels = $oPayment->loadLabelList();

					$ts = new list_timestamp('payment');
					if( !empty($ts->id) ) $lastModified = strtotime($ts->stamp);
				break;
			case 'currencyList':
			case 'locationList':
			case 'methodList':
			case 'originList':
			case 'typeList':
			case 'statusList':
			case 'ownerList':
			case 'recipientList':
					$target = ( strpos($field, 'List') !== false ? substr($field, 0, strlen($field)-4 ) : $field );

					$obj = new $target();
					$list = $obj->loadListForFilter();

					$ts = new list_timestamp($target);
					if( !empty($ts->id) ) $lastModified = strtotime($ts->stamp);
				break;

			case 'origin_filter':
			case 'recipient_filter':
			case 'location_filter':
					$target = substr($field, 0, -7);

					$obj = new $target();
					$list = $obj->loadListForFilterByOwner();

					$ts = new list_timestamp($target); //not optimal as the list has not always changed for a specific owner
					if( !empty($ts->id) ) $lastModified = strtotime($ts->stamp);
				break;
			default:
				throw new Exception('Chargement de liste : cible non reconnue.');
		}

		if( $lastModified != 0 ) header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified) . " GMT");

		echo json_encode($list);
		die;
	}
} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo $e->getMessage();
	die;
}
?>