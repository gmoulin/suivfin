<?php
/**
 * Class for paiement management
 *
 * class name is in lowerclass to match table name ("common" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Payement
 * @category Payements
 */
class payment extends common {
	protected $_table = 'payment';
	protected $_join = 'limits';
	protected $_relatedStashes = array();
	protected $_relatedTimestamps = array('payment');

	// Constructor
	public function __construct() {
		//for "common" ($this->_db & co)
		parent::__construct();
	}

	/**
	 * check and parse form data for add or update
	 * errors are returned with form inputs ids as (id, text, type)
	 *
	 * @return array[]
	 */
	public function checkAndPrepareFormData(){
		$formData = array();
		$errors = array();

		$args = array(
			'action'		=> FILTER_SANITIZE_STRING,
			'id'			=> FILTER_SANITIZE_NUMBER_INT,
			'label'			=> FILTER_SANITIZE_STRING,
			'paymentDate'	=> FILTER_SANITIZE_STRING,
			'amount'		=> array(FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
			'comment'		=> FILTER_SANITIZE_STRING,
			'recurrent'		=> FILTER_SANITIZE_NUMBER_INT,
			'recipientFK'	=> FILTER_SANITIZE_STRING,
			'typeFK'		=> FILTER_SANITIZE_NUMBER_INT,
			'currencyFK'	=> FILTER_SANITIZE_NUMBER_INT,
			'methodFK'		=> FILTER_SANITIZE_STRING,
			'originFK'		=> FILTER_SANITIZE_NUMBER_INT,
			'statusFK'		=> FILTER_SANITIZE_NUMBER_INT,
			'locationFK'	=> FILTER_SANITIZE_STRING,
		);

/* @todo uncomment
		foreach( $args as $field => $validation ){
			if( !filter_has_var(INPUT_POST, $field) ){
				$errors[] = array('global', 'Le champ '.$field.' est manquant.', 'error');
			}
		}
*/
		if( empty($errors) ){

			$formData = filter_var_array($_POST, $args);

			foreach( $formData as $field => $value ){
				${$field} = $value;
			}

			//id
			//errors are set to #label because #id is hidden
			if( $action == 'update' ){
				if( is_null($id) || $id === false ){
					$errors[] = array('label', 'Identifiant incorrect.', 'error');
				} else {
					$check = filter_var($id, FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $check === false ){
						$errors[] = array('label', 'Identifiant du paiement incorrect.', 'error');
					} else {
						//check if id exists in DB
						if( self::existsById($id) ){
							$formData['id'] = $id;
						} else {
							$errors[] = array('label', 'Identifiant du paiement inconnu.', 'error');
						}
					}
				}
			}

			if( $action == 'update' || $action == 'add' ){
				//label
					if( is_null($label) || $label === false ){
						$errors[] = array('label', 'Libellé incorrect.', 'error');
					} elseif( empty($label) ){
						$errors[] = array('label', 'Le libellé est requis.', 'required');
					} else {
						$formData['label'] = trim($label);
					}

				//paymentDate, format dd/mm/yyyy
					if( is_null($paymentDate) || $paymentDate === false ){
						$errors[] = array('paymentDate', 'Date incorrecte.', 'error');
					} elseif( empty($paymentDate) ){
						$errors[] = array('paymentDate', 'La date est requise.', 'required');
					} else {
						$regexp = array("options" => array("regexp" => "/^([012][123456789]|[123]0|31)\/([0][123456789]|[1][012])\/20[0-9]{2}$/"));
						$check = filter_var($paymentDate, FILTER_VALIDATE_REGEXP, $regexp);
						if( $check === false ){
							$errors[] = array('paymentDate', 'Date incorrecte.'.$paymentDate, 'error');
						} else {
							$date = substr($paymentDate, -4).'-'.substr($paymentDate, 3, 2).'-'.substr($paymentDate, 0, 2);
							if( strtotime($date) === false ){ //strtotime accepts only valid english date
								$errors[] = array('paymentDate', 'Format de date incorrect.', 'error');
							} else {
								$formData['paymentDate'] = $date;
							}
						}
					}

				//amount
					if( is_null($amount) || $amount === false ){
						$errors[] = array('amount', 'Montant incorrect.', 'error');
					} else {
						$check = filter_var($amount, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
						if( $check === false ){
							$errors[] = array('amount', 'Montant incorrect.', 'error');
						} else {
							$formData['amount'] = $amount;
						}
					}

				//comment
					if( is_null($comment) || $comment === false ){
						$errors[] = array('comment', 'Commentaire incorrect.', 'error');
					} else {
						$formData['comment'] = trim($comment);
					}

				//recurrent
					if( is_null($recurrent) || $recurrent === false ){
						$errors[] = array('recurrent', 'Récurrence incorrecte.', 'error');
					} else {
						$check = filter_var($recurrent, FILTER_VALIDATE_BOOLEAN);
						if( $check === null ){
							$errors[] = array('recurrent', 'Récurrence incorrecte.', 'error');
						} else {
							$formData['recurrent'] = $recurrent;
						}
					}

				//recipientFK
					if( is_null($recipientFK) || $recipientFK === false ){
						$errors[] = array('recipientFK', 'Bénéficiaire incorrecte.', 'error');
					} else {
						$check = filter_var($recipientFK, FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $check === false ){ //not an id
							if( empty($recipientFK) ){
								$errors[] = array('recipientFK', 'Le bénéficiaire est requis.', 'required');
							} else {
								//new recipient ?
								$check = recipient::existsByLabel($recipientFK);
								if( $check ) $formData['recipientFK'] = $check;
								else {
									$eRecipient = new recipient();
									$eRecipient->name = $recipientFK;
									$eRecipient->save();

									$formData['recipientFK'] = $eRecipient->id;
								}
							}
						} else {
							//check if id exists in DB
							$check = recipient::existsById($recipientFK);
							if( $check ){
								$formData['recipientFK'] = $check;
							} else {
								$errors[] = array('recipientFK', 'Identifiant du bénificiaire inconnu.', 'error');
							}
						}
					}

				//typeFK
					if( is_null($typeFK) || $typeFK === false ){
						$errors[] = array('typeFK', 'Type incorrect.', 'error');
					} else {
						$check = filter_var($typeFK, FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $check === false ){
							$errors[] = array('typeFK', 'Identifiant du type incorrect.', 'error');
						} else {
							//check if id exists in DB
							if( type::existsById($typeFK) ){
								$formData['typeFK'] = $typeFK;
							} else {
								$errors[] = array('typeFK', 'Identifiant du type inconnu.', 'error');
							}
						}
					}

				//statusFK
					if( is_null($statusFK) || $statusFK === false ){
						$errors[] = array('statusFK', 'Statut incorrect.', 'error');
					} else {
						$check = filter_var($statusFK, FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $check === false ){
							$errors[] = array('statusFK', 'Identifiant du statut incorrect.', 'error');
						} else {
							//check if id exists in DB
							if( status::existsById($statusFK) ){
								$formData['statusFK'] = $statusFK;
							} else {
								$errors[] = array('statusFK', 'Identifiant du statut inconnu.', 'error');
							}
						}
					}

				//currencyFK
					if( is_null($currencyFK) || $currencyFK === false ){
						$errors[] = array('currencyFK', 'Monnaie incorrecte.', 'error');
					} else {
						$check = filter_var($currencyFK, FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $check === false ){
							$errors[] = array('currencyFK', 'Identifiant de la monnaie incorrect.', 'error');
						} else {
							//check if id exists in DB
							if( currency::existsById($currencyFK) ){
								$formData['currencyFK'] = $currencyFK;
							} else {
								$errors[] = array('currencyFK', 'Identifiant de la monnaie inconnu.', 'error');
							}
						}
					}

				//methodFK
					if( is_null($methodFK) || $methodFK === false ){
						$errors[] = array('methodFK', 'Méthode incorrecte.', 'error');
					} else {
						$check = filter_var($methodFK, FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $check === false ){ //not an id
							if( empty($methodFK) ){
								$errors[] = array('methodFK', 'La méthode est requise.', 'required');
							} else {
								//new method ?
								$check = method::existsByLabel($methodFK);
								if( $check ) $formData['methodFK'] = $check;
								else {
									$eMethod = new method();
									$eMethod->name = $methodFK;
									$eMethod->save();

									$formData['methodFK'] = $eMethod->id;
								}
							}
						} else {
							//check if id exists in DB
							$check = method::existsById($methodFK);
							if( $check ){
								$formData['methodFK'] = $check;
							} else {
								$errors[] = array('methodFK', 'Identifiant de la méthode inconnu.', 'error');
							}
						}
					}

				//originFK
					if( is_null($originFK) || $originFK === false ){
						$errors[] = array('originFK', 'Origine incorrecte.', 'error');
					} else {
						$check = filter_var($originFK, FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $check === false ){
							$errors[] = array('originFK', 'L\'origine est requise.', 'error');
						} else {
							//check if id exists in DB
							if( origin::existsById($originFK) ){
								$formData['originFK'] = $originFK;
							} else {
								$errors[] = array('originFK', 'Identifiant de l\'origine inconnu.', 'error');
							}
						}
					}

				//locationFK
					if( is_null($locationFK) || $locationFK === false ){
						$errors[] = array('locationFK', 'Localisation incorrecte.', 'error');
					} else {
						$check = filter_var($locationFK, FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $check === false ){ //not an id
							if( empty($locationFK) ){
								$errors[] = array('locationFK', 'La localisation est requise.', 'required');
							} else {
								//new location ?
								$check = location::existsByLabel($locationFK);
								if( $check ) $formData['locationFK'] = $check;
								else {
									$eLocation = new location();
									$eLocation->name = $locationFK;
									$eLocation->save();

									$formData['locationFK'] = $eLocation->id;
								}
							}
						} else {
							//check if id exists in DB
							$check = location::existsById($locationFK);
							if( $check ){
								$formData['locationFK'] = $check;
							} else {
								$errors[] = array('locationFK', 'Identifiant de la localisation inconnu.', 'error');
							}
						}
					}
			}
		}
		$formData['errors'] = $errors;

		if( empty($errors) ){

			$this->hasAmountBeenModified = false;
			if( !empty($this->id) ){
				$this->load($id);

				if( $this->amount != $formData->amout ) $this->hasAmountBeenModified = true;
			}

			foreach( self::$_fields[$this->_table] as $k => $v ){
				if( isset($formData[$v]) ) $this->_data[$v] = $formData[$v];
			}
			$this->_data['ownerFK'] = $this->getOwner();
		}

		return $formData;
	}

	public function save(){
		parent::save();

		if( $this->hasAmountBeenModified ){
			//update balance lastUpdate field for the batch
			$eBalance = new balance();
			$eBalance->loadByFKs($this->_data['currencyFK'], $this->_data['originFK'], $this->_data['typeFK']);

			if( !empty($eBalance->id) ){
				$eBalance->lastUpdate = $this->creationDate;

				$eBalance->save();
			}
		}
	}

	/**
	 * overload of common function to manage balance table cleaning
	 */
	public function delete(){
		try {
			if( parent::delete() === true ){
				$q = $this->_db->prepare('DELETE FROM balance where typeFK = :id');
				$q->excute(array(
					':id' => $this->_data['id'],
				));
			}

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * get all payments for the current month
	 * @return array[][]
	 */
	public function loadForCurrentMonth(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $this->getOwner(), date('Y-m'));
			$list = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$q = $this->_db->prepare("
					SELECT p.*
					FROM ".$this->_table." p
					INNER JOIN ".$this->_join." l ON l.owner_id = p.ownerFK
					WHERE ownerFK = :owner
					AND l.origin_id = p.originFK
					AND l.currency_id = p.currencyFK
					AND paymentDate BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01')
					AND DATE_FORMAT(LAST_DAY(CURDATE()), '%Y-%m-%d')
					ORDER BY paymentDate desc
				");
				$q->execute( array(':owner' => $this->getOwner()) );

				$list = $q->fetchAll();

				if( !empty($list) ) $stash->store($list, STASH_EXPIRE);
			}

			return $list;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * get all payments for a given time frame
	 * @return array[][]
	 */
	public function loadForTimeFrame( $frame ){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $this->getOwner(), $frame);
			$list = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				//construct the query and parameters
				$sql = "
					SELECT p.*
					FROM ".$this->_table." p
					INNER JOIN ".$this->_join." l ON l.owner_id = p.ownerFK
					WHERE ownerFK = :owner
					AND l.origin_id = p.originFK
					AND l.currency_id = p.currencyFK
				";
				$where = array();
				$params = array(':owner' => $this->getOwner());

				$frame = explode(',', $frame);

				foreach( $frame as $i => $partialDate ){
					$where[] = 'paymentDate LIKE :partial'.$i;
					$params[':partial'.$i] = $partialDate.'-__';
				}

				if( !empty($where) ) $sql .= " AND (".implode(' OR ', $where).")";

				$sql .= " ORDER BY paymentDate desc";

				$q = $this->_db->prepare($sql);
				$q->execute( $params );

				$list = $q->fetchAll();

				if( !empty($list) ) $stash->store($list, STASH_EXPIRE);
			}

			return $list;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * dupplicate all current month recurrent payments for next month
	 * set datePayment to + 1 month, status to 2 (Prévisible)
	 * @todo make it one time per month only
	 */
	public function initNextMonthPayment(){
		try {
			$q = $this->_db->prepare("
				INSERT INTO ".$this->_table."
				(`label`, `paymentDate`, `amount`, `comment`, `recurrent`, `recipientFK`, `typeFK`, `currencyFK`, `methodFK`, `originFK`, `statusFK`, `ownerFK`, `locationFK`, `creationDate`, `modificationDate`)
				SELECT `label`, DATE_ADD(`paymentDate`, INTERVAL 1 MONTH) AS paymentDate, `amount`, `comment`, `recurrent`, `recipientFK`, `typeFK`, `currencyFK`, `methodFK`, `originFK`, 2, `ownerFK`, `locationFK`, NOW(), NOW()
				FROM ".$this->_table."
				WHERE paymentDate
				BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01')
				AND DATE_FORMAT(LAST_DAY(CURDATE()), '%Y-%m-%d')
				AND recurrent = 1
			");

			$q->execute();

			$this->_cleanCaches();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * return the list of years and months with payments
	 */
	public function getYearsAndMonths(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $this->getOwner());
			$range = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$q = $this->_db->prepare("
					SELECT DATE_FORMAT(paymentDate, '%Y') AS `year`, DATE_FORMAT(paymentDate, '%m') AS `month`
					FROM ".$this->_table." p
					INNER JOIN ".$this->_join." l ON l.owner_id = p.ownerFK
					WHERE ownerFK = :owner
					AND l.origin_id = p.originFK
					AND l.currency_id = p.currencyFK
					GROUP BY `year`, `month`
					ORDER BY `year`, `month`
				");
				$q->execute( array(':owner' => $this->getOwner()) );

				$range = $q->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);

				if( !empty($range) ) $stash->store($range, STASH_EXPIRE);
			}

			return $range;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * sum the payment by month-year, type, origin and currency
	 */
	public function getSums( $frame ){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $this->getOwner(), $frame);
			$sums = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$sql = "
					SELECT DATE_FORMAT(paymentDate, '%m-%Y') AS `month`, SUM( amount ) AS `sum`, originFK, typeFK, currencyFK
					FROM ".$this->_table." p
					INNER JOIN ".$this->_join." l ON l.owner_id = p.ownerFK
					WHERE ownerFK = :owner
					AND l.origin_id = p.originFK
					AND l.currency_id = p.currencyFK
				";

				$where = array();
				$params = array(':owner' => $this->getOwner());

				$frame = explode(',', $frame);
				foreach( $frame as $i => $partialDate ){
					$where[] = 'paymentDate LIKE :partial'.$i;
					$params[':partial'.$i] = $partialDate.'-__';
				}

				if( !empty($where) ) $sql .= " AND (".implode(' OR ', $where).")";

				$sql .= "
					GROUP BY `month`, originFK, typeFK, currencyFK
					ORDER BY `month` DESC, originFK, typeFK, currencyFK
				";

				$q = $this->_db->prepare($sql);
				$q->execute($params);

				$sums = array();
				if( $q->rowCount() > 0 ){
					while( $r = $q->fetch() ){
						$sums[ $r['month'] ][ $r['originFK'] ][ $r['typeFK'] ][ $r['currencyFK'] ] = $r['sum'];
					}
				}
				if( !empty($sums) ) $stash->store($sums, STASH_EXPIRE);
			}

			return $sums;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}


	/**
	 * sum the expanses for the current month and the next one
	 */
	public function getForecasts(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $this->getOwner());
			$forecasts = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$q = $this->_db->prepare("
					SELECT DATE_FORMAT(paymentDate, '%m-%Y') AS `month`, SUM( amount ) AS `sum`, statusFK, currencyFK
					FROM ".$this->_table." p
					INNER JOIN ".$this->_join." l ON l.owner_id = p.ownerFK
					WHERE ownerFK = :owner
					AND l.origin_id = p.originFK
					AND l.currency_id = p.currencyFK
					AND p.typeFK = 2
					AND p.statusFK IN (2,4)
					AND paymentDate BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01')
					AND DATE_FORMAT(LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 1 MONTH)), '%Y-%m-%d')
					GROUP BY `month`, statusFK, currencyFK
					ORDER BY `month`, statusFK, currencyFK
				");

				$q->execute( array(':owner' => $this->getOwner()) );

				$forecasts = array();
				if( $q->rowCount() > 0 ){
					while( $r = $q->fetch() ){
						$forecasts[ $r['month'] ][ $r['statusFK'] ][ $r['currencyFK'] ] = $r['sum'];
					}
				}
				if( !empty($forecasts) ) $stash->store($forecasts, STASH_EXPIRE);
			}

			return $forecasts;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}
}
?>