<?php
//manage dropdown lists content for ajax requests
try {
    require_once('../conf.ini.php');

    header('Content-type: application/json');

	if( !filter_has_var(INPUT_GET, 'field') || !filter_has_var(INPUT_GET, 'forceUpdate') ){
		throw new Exception('Chargement des listes déroulantes : paramètre manquante.');
	} else {
		$field = filter_input(INPUT_GET, 'field', FILTER_SANITIZE_STRING);
		if( is_null($field) || $field === false ){
			throw new Exception('Chargement des listes déroulantes : liste incorrecte.');
		}

		$notModified = false;
		$request_headers = apache_request_headers();
		$browserHasCache = ( array_key_exists('If-Modified-Since', $request_headers) ? true : false );
		if( $browserHasCache ){
			$modifiedSince = strtotime($request_headers['If-Modified-Since']);
		}

		$list = array();
		$lastModified = 0;
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
					$target = substr($field, 0, strlen($field)-4 );

					if( $browserHasCache ){
						$ts = new list_timestamp($target);
						if( !empty($ts->id) ){
							//browser has list in cache and list was not modified
							if( $modifiedSince == $ts->timestamp ){
								$lastModified = $ts->timestamp;
								$notModified = true;
								header($_SERVER["SERVER_PROTOCOL"]." 304");
							}
						}
					}

					if( !$notModified ){
						$obj = new $table();
						$list = $obj->loadList('name');
					}
				break;

			default:
				throw new Exception('Chargement de liste : cible non reconnue.');
		}

		$expires = 60*60*24*7; //1 weeks
		header('Expires: '.gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
		header('Cache-Control: max-age=' . $expires);
		if( $lastModified != 0 ) header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified) . " GMT");
		header('Content-Type: application/json');

		if( !$notModified ){
			echo json_encode($liste);
		}
		die;
	}
} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo $e->getMessage();
	die;
}
?>