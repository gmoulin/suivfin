<?php
//manage dropdown lists content for ajax requests
try {
    require_once('../conf.ini.php');

    header('Content-type: application/json');
	$expires = 60*60*24*7; //1 weeks
	header('Expires: '.gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT'); //always send else the next request will not have "If-Modified-Since" header
	header('Cache-Control: max-age=' . $expires.', must-revalidate'); //must-revalidate to force browser to used the cache control rules sended

	if( !filter_has_var(INPUT_GET, 'field') ){
		throw new Exception('Chargement des listes déroulantes : paramètre manquant.');
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

		$lastModified = null;
		$target = $field;

		if( $field == 'labelList' ) $target = 'payment';
		elseif( strpos($field, 'List') !== false ) $target = substr($field, 0, -4);
		elseif( strpos($field, '_filter') !== false ) $target = substr($field, 0, -7);

		$data = array();
		switch ( $field ){
			/* form fields */
			case 'labelList':
					$oPayment = new payment();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $oPayment->loadLabelList( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $data) = $oPayment->loadLabelList( true );
				break;
			case 'currencyList':
			case 'locationList':
			case 'methodList':
			case 'originList':
			case 'typeList':
			case 'statusList':
			case 'ownerList':
			case 'recipientList':
					$obj = new $target();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $obj->loadListForFilter( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $data) = $obj->loadListForFilter(true);
				break;

			case 'origin_filter':
			case 'recipient_filter':
			case 'location_filter':
					$obj = new $target();

					if( $browserHasCache && $modifiedSince != 0 ){
						$ts = $obj->loadListForFilterByOwner( null, true );
						if( !is_null($ts) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts ){
								header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
								die;
							}
						}
					}

					list($lastModified, $data) = $obj->loadListForFilterByOwner(true);
				break;
			default:
				throw new Exception('Chargement de liste : cible non reconnue.');
		}

		if( !is_null($lastModified) ) header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified) . " GMT");

		echo json_encode($data);
		die;
	}
} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo $e->getMessage();
	die;
}
?>