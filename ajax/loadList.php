<?php
//manage dropdown lists content for ajax requests
try {
    require_once('../conf.ini.php');

    header('Content-type: application/json');
	$expires = 60*60*24*7; //1 weeks
	header('Expires: '.gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT'); //always send else the next request will not have "If-Modified-Since" header
	header('Cache-Control: max-age=' . $expires.', must-revalidate'); //must-revalidate to force browser to used the cache control rules sended

	if( !filter_has_var(INPUT_GET, 'field') || !filter_has_var(INPUT_GET, 'forceUpdate') ){
		throw new Exception('Chargement des listes déroulantes : paramètre manquante.');
	} else {
		$field = filter_input(INPUT_GET, 'field', FILTER_SANITIZE_STRING);
		if( is_null($field) || $field === false ){
			throw new Exception('Chargement des listes déroulantes : liste incorrecte.');
		}

		//use to force a HTTP 200 response containing a fresh list (when browser has nothing in the <select> or <datalist> element)
		$forceUpdate = filter_input(INPUT_GET, 'forceUpdate', FILTER_SANITIZE_NUMBER_INT);
		if( is_null($forceUpdate) || $forceUpdate === false ){
			throw new Exception('Chargement des listes déroulantes : paramètre incorrect.');
		}
		$forceUpdate = filter_var($forceUpdate, FILTER_VALIDATE_INT, array('min_range' => 0, 'max_range' => 1));
		if( $forceUpdate === false ){
			throw new Exception('Chargement des listes déroulantes : paramètre incorrect.');
		}

		//check the request headers for "If-Modified-Since"
		$request_headers = apache_request_headers();
		$browserHasCache = ( array_key_exists('If-Modified-Since', $request_headers) ? true : false );
		if( $browserHasCache ){
			$modifiedSince = strtotime($request_headers['If-Modified-Since']);
		}

		$lastModified = 0;
		$target = ( strpos($field, 'List') !== false ? substr($field, 0, strlen($field)-4 ) : $field );
		if( $browserHasCache ){
			$ts = new list_timestamp($target);
			if( !empty($ts->id) ){
				$lastModified = strtotime($ts->stamp);
				//browser has list in cache and list was not modified
				if( $modifiedSince == $lastModified ){
					if( !$forceUpdate ){
						header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
						die;
					}
				}
			}
		}

		$list = array();
		switch ( $field ){
			/* form fields */
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
					$list = $obj->loadList('name');

					$ts = new list_timestamp($target);
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