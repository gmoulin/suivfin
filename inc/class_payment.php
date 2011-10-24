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

	// Constructor
	public function __construct($id = null){
		//for "common" ($this->_db & co)
		parent::__construct($id);
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
			'action'			=> FILTER_SANITIZE_STRING,
			'id'				=> FILTER_SANITIZE_NUMBER_INT,
			'label'				=> FILTER_SANITIZE_STRING,
			'paymentDate'		=> FILTER_SANITIZE_STRING,
			'amount'			=> array(FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
			'comment'			=> FILTER_SANITIZE_STRING,
			'recurrent'			=> FILTER_SANITIZE_NUMBER_INT,
			'recipientFK'		=> FILTER_SANITIZE_STRING,
			'typeFK'			=> FILTER_SANITIZE_NUMBER_INT,
			'currencyFK'		=> FILTER_SANITIZE_NUMBER_INT,
			'methodFK'			=> FILTER_SANITIZE_STRING,
			'originFK'			=> FILTER_SANITIZE_STRING,
			'statusFK'			=> FILTER_SANITIZE_NUMBER_INT,
			'locationFK'		=> FILTER_SANITIZE_STRING,
			'owner'				=> FILTER_SANITIZE_NUMBER_INT,
		);

		foreach( $args as $field => $validation ){
			if( !filter_has_var(INPUT_POST, $field) ){
				$errors[] = array('global', 'Le champ '.$field.' est manquant.', 'error');
			}
		}

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

			//errors are set to #label because #id is hidden
			//check owner, will serve only if $this->getOwner() return an incorrect value
			if( is_null($owner) || $owner === false ){
				$errors[] = array('label', 'Compte incorrect, merci de recharger la page.', 'error');
			} else {
				$check = filter_var($owner, FILTER_VALIDATE_INT, array('min_range' => 1, 'max_range' => 3));
				if( $check === false ){
					$errors[] = array('label', 'Compte incorrect, merci de recharger la page.', 'error');
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
							$d = strtotime($date);
							if( $d === false ){ //strtotime accepts only valid english date
								$errors[] = array('paymentDate', 'Format de date incorrect.', 'error');
							} else {
								$formData['paymentDate'] = $date;

								if( date( 'd', $d ) > 24 ){
									$d = new DateTime($date);
									$d->add(new DateInterval('P1M'));
									$formData['paymentMonth'] = $d->format('Y-m');
								} else {
									$formData['paymentMonth'] = date('Y-m', $d);
								}
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
						$errors[] = array('recipientFK', 'Bénéficiaire incorrect.', 'error');
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
							if( recipient::existsById($recipientFK) ){
								$formData['recipientFK'] = $recipientFK;
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
							if( method::existsById($methodFK) ){
								$formData['methodFK'] = $methodFK;
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
						if( $check === false ){ //not an id
							if( empty($originFK) ){
								$errors[] = array('originFK', 'L\'origine est requise.', 'required');
							} else {
								//new origin ?
								$check = origin::existsByLabel($originFK);
								if( $check ) $formData['originFK'] = $check;
								else {
									$eOrigin = new origin();
									$eOrigin->name = $originFK;
									$eOrigin->save();

									$formData['originFK'] = $eOrigin->id;
								}
							}
						} else {
							//check if id exists in DB
							if( origin::existsById($originFK) ){
								$formData['originFK'] = $check;
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
							if( location::existsById($locationFK) ){
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

			if( $action == 'update' ){
				$this->load($formData['id']);

				$oEvolution = new evolution();

				if( $this->_data['paymentDate'] != $formData['paymentDate'] ){
					$since = date('Y-m-d', min( strtotime($this->_data['paymentDate']), strtotime($formData['paymentDate']) ) );
					//delete evolutions for the payment old origin, recalculation will be done in save function
					$oEvolution->deleteSince($since, $this->_data['originFK'], $this->_data['recipientFK']);
				}

				if( $this->_data['originFK'] != $formData['originFK'] ){
					//delete evolutions for the payment old origin, recalculation will be done in save function
					$oEvolution->deleteSince($this->_data['paymentDate'], $this->_data['originFK'], $this->_data['recipientFK']);
				}
			}

			foreach( self::$_fields[$this->_table] as $k => $v ){
				if( isset($formData[$v]) ) $this->_data[$v] = $formData[$v];
			}

			//bulletproofing
			$check = $this->getOwner();
			if( empty($check) ){
				init::getInstance()->setOwner( $owner );
				$this->_data['ownerFK'] = $this->getOwner();
			}

			$this->_data['ownerFK'] = $this->getOwner();

			//clean labelList cache if the label does not exists
			$this->existsLabel($formData['label']);
		}

		return $formData;
	}

	/**
	 * existsByLabel clone for labelList special case
	 * @param string $name
	 * @return id or false
	 */
	public function existsLabel( $name ){
		try {
			$existsLabel = $this->_db->prepare("
				SELECT id
				FROM payment
				WHERE label = :name
			");

			$existsLabel->execute( array(':name' => $name) );

			$result = $existsLabel->fetchAll();
			if( count($result) == 0 ){
				//clear stash cache
				$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
				$stash = new Stash($stashFileSystem);

				$stash->setupKey( 'labelList' );

				$stash->clear();
			}

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param boolean $returnTs : flag for the function to return the list and the ts or only the list
	 * @param boolean $tsOnly : flag for the function to return the cache creation date timestamp only
	 * @return array[key][entry]
	 */
	public function loadLabelList( $returnTs = false, $tsOnly = false ){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			$stash = StashBox::getCache('labelList');

			if( $tsOnly ){
				$ts = $stash->getTimestamp();
				if( $stash->isMiss() ){
					return null;
				} else {
					return $ts;
				}
			}

			$list = $stash->get();
			$ts = null;
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$loadList = $this->_db->prepare("
					SELECT DISTINCT(label) FROM ".$this->_table." ORDER BY label
				");

				$loadList->execute();

				$list = array();
				while( $rs = $loadList->fetch() ){
					$list[] = $rs['label'];
				}

				if( !empty($list) ){
					$stash->store($list, STASH_EXPIRE);
					$ts = $stash->getTimestamp();
				}
			} elseif( $returnTs ){
				$ts = $stash->getTimestamp();
			}

			if( $returnTs ){
				return array('lastModified' => $ts, 'data' => $list);
			} else {
				return $list;
			}

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * overload of common function to manage evolution table
	 * @param boolean $cleanCache : will the _cleanCaches method be called
	 */
	public function save( $cleanCache = true ){
		parent::save($cleanCache);

		//delete evolutions for the payment origin
		$oEvolution = new evolution();
		$oEvolution->deleteSince($this->_data['paymentDate'], $this->_data['originFK'], $this->_data['recipientFK']);
		//calculate all missing evolution
		$oEvolution->calculateEvolution();
	}

	/**
	 * overload of common function to manage balance table cleaning
	 */
	public function delete(){
		try {
			parent::delete();

			//delete evolutions for the payment origin
			$oEvolution = new evolution();
			$oEvolution->deleteSince($this->_data['paymentDate'], $this->_data['originFK'], $this->_data['recipientFK']);
			//calculate all missing evolution until today
			$oEvolution->calculateEvolution();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * get all payments for a given time frame
	 * @param array $ids : array containing the delta payments ids
	 * @return array[]
	 */
	public function loadByIds( $ids ){

		try {
			//construct the query and parameters
			//PDO does not bind correctly parameter with IN()
			$sql = "
				SELECT *
				FROM ".$this->_table."
				WHERE ownerFK = :owner
				AND id IN (".implode(',', $ids).")
			";
			$params = array(
				':owner' => $this->getOwner(),
			);

			$q = $this->_db->prepare($sql);
			$q->execute( $params );

			$list = $q->fetchAll();

			return $list;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * get all payments for the current month
	 * @param string $month : format YYYY-MM
	 * @param string $timestamp : unix timestamp for cache
	 * @return array[][]
	 */
	public function loadByMonth( $month, $timestamp = 0 ){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $this->getOwner(), $month);

			//check timestamp
			if( !empty($timestamp) ){
				$ts = $stash->getTimestamp();
				if( !$stash->isMiss() && $timestamp == $ts ){ //no changes
					return null;
				}
			}

			$list = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$q = $this->_db->prepare("
					SELECT *
					FROM ".$this->_table."
					WHERE ownerFK = :owner
					AND paymentMonth = :month
					ORDER BY paymentDate desc
				");

				$q->execute( array(
					':owner' => $this->getOwner(),
					':month' => $month,
				) );

				$list = $q->fetchAll();

				if( !empty($list) ){
					$stash->store($list, STASH_EXPIRE);
				}
			}
			$ts = $stash->getTimestamp();

			return array('lastModified' => $ts, 'payments' => $list);

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * get all payments for a given time frame
	 * @param array $frame : key month and value timestamp (month format yyyy-mm)
	 * @return array[][]
	 */
	public function loadForTimeFrame( $frame ){
		try {
			$result = array();
			foreach( $frame as $month => $timestamp ){
				$data = $this->loadByMonth($month, $timestamp);

				//add to the result only if something has changed
				if( !is_null($data) ){
					$result[$month]['list'] = $data['payments'];
					$result[$month]['lastModified'] = gmdate("D, d M Y H:i:s", $data['lastModified'])." GMT";
				}
			}

			return $result;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * dupplicate all current month recurrent payments for next month
	 * set datePayment to + 1 month, status to 2 (Prévisible)
	 */
	public function initNextMonthPayment(){
		try {
			$q = $this->_db->prepare("
				INSERT INTO ".$this->_table."
				(`label`, `paymentDate`, `paymentMonth`, `amount`, `comment`, `recurrent`, `recipientFK`, `typeFK`, `currencyFK`, `methodFK`, `originFK`, `statusFK`, `ownerFK`, `locationFK`, `creationDate`, `modificationDate`)
				SELECT `label`, DATE_ADD(`paymentDate`, INTERVAL 1 MONTH) AS paymentDate, DATE_ADD(CONCAT(`paymentMonth`, '-01'), INTERVAL 1 MONTH) AS paymentMonth ,`amount`, `comment`, `recurrent`, `recipientFK`, `typeFK`, `currencyFK`, `methodFK`, `originFK`, 2, `ownerFK`, `locationFK`, NOW(), NOW()
				FROM ".$this->_table."
				WHERE recurrent = 1
				AND paymentMonth = DATE_FORMAT(CURDATE(), '%Y-%m')
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
			$q = $this->_db->prepare("
				SELECT LEFT(paymentMonth, 4) AS `year`, RIGHT(paymentMonth, 2) AS `month`
				FROM ".$this->_table."
				WHERE ownerFK = :owner
				GROUP BY `year`, `month`
				ORDER BY `year`, `month`
			");
			$q->execute( array(':owner' => $this->getOwner()) );

			$range = $q->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);

			return $range;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * sum the payment by month-year, type, origin and currency
	 * @param string $month : format YYYY-MM
	 * @param string $timestamp : unix timestamp for cache
	 * @return array
	 */
	public function getSumByMonth( $month, $timestamp ){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $this->getOwner(), $month);

			if( !empty($timestamp) ){
				$ts = $stash->getTimestamp();
				if( !$stash->isMiss() && $ts == $timestamp ){ //no changes
					return null;
				}
			}

			$sums = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them

				//get the balance of each origin per month by getting the previous month last day balance (24)
				$sql = "
					SELECT amount, DATE_FORMAT(DATE_ADD(evolutionDate, INTERVAL 1 MONTH), '%Y-%m') AS `month`, e.originFK, currencyFK
					FROM evolution e
					INNER JOIN limits l ON l.originFK = e.originFK
					WHERE l.ownerFK = :owner
					AND evolutionDate = DATE_SUB(CONCAT(:month, '-24'), INTERVAL 1 MONTH)
					ORDER BY `month`, originFK, currencyFK
				";
				$e = $this->_db->prepare($sql);
				$e->execute( array(
					':owner' => $this->getOwner(),
					':month' => $month,
				) );

				$sums = array('balance' => array(), 'list' => array(), 'fromto' => array());
				if( $e->rowCount() > 0 ){
					while( $r = $e->fetch() ){
						$sums['balance'][ $r['originFK'] ][ $r['currencyFK'] ] = $r['amount'];
						$sums['fromto'][] = $r['originFK'];
					}
					$sums['fromto'] = array_unique($sums['fromto']);
				}


				// type = 1 means deposits, and for them it's the recipient
				// recipient and origin have the same ids for bank accounts and cash
				$sql = "
					SELECT `month`, sum, paymentDate, fromto, typeFK, currencyFK, recurrent
					FROM (
							(
								SELECT paymentMonth AS `month`, SUM( amount ) AS `sum`, paymentDate, p.originFK AS 'fromto', p.typeFK, p.currencyFK, p.recurrent
								FROM ".$this->_table." p
								INNER JOIN ".$this->_join." l ON p.ownerFK = l.ownerFK
								WHERE p.ownerFK = :owner1
								AND p.typeFK != 1
								AND p.originFK = l.originFK
								AND p.currencyFK = l.currencyFK
								GROUP BY paymentMonth, p.typeFK, p.recurrent, fromto, p.currencyFK
							) UNION (
								SELECT paymentMonth AS `month`, SUM( amount ) AS `sum`, paymentDate, p.recipientFK AS 'fromto', p.typeFK, p.currencyFK, p.recurrent
								FROM ".$this->_table." p
								INNER JOIN ".$this->_join." l ON p.ownerFK = l.ownerFK
								WHERE p.ownerFK = :owner2
								AND p.typeFK = 1
								AND p.recipientFK = l.originFK
								AND p.currencyFK = l.currencyFK
								GROUP BY paymentMonth, p.typeFK, p.recurrent, fromto, p.currencyFK
							)
					) sub
					WHERE `month` = :month
					ORDER BY typeFK, recurrent, fromto, currencyFK
				";

				$q = $this->_db->prepare($sql);
				$q->execute( array(
					':month' => $month,
					':owner1' => $this->getOwner(),
					':owner2' => $this->getOwner(),
				) );

				if( $q->rowCount() > 0 ){
					while( $r = $q->fetch() ){
						$sums['list'][ $r['typeFK'].($r['recurrent'] ? 'r' : '') ][ $r['fromto'] ][ $r['currencyFK'] ] = $r['sum'];

						if( !isset($sums['total'][ $r['fromto'] ][ $r['currencyFK'] ]) ){
							if(    isset($sums['balance'][ $r['fromto'] ])
								&& isset($sums['balance'][ $r['fromto'] ][ $r['currencyFK'] ])
							){
								$sums['total'][ $r['fromto'] ][ $r['currencyFK'] ] = $sums['balance'][ $r['fromto'] ][ $r['currencyFK'] ];
							} else {
								$sums['total'][ $r['fromto'] ][ $r['currencyFK'] ] = 0;
							}
						}
						if( $r['typeFK'] == 1 ){
							$sums['total'][ $r['fromto'] ][ $r['currencyFK'] ] += $r['sum'];
						} else {
							$sums['total'][ $r['fromto'] ][ $r['currencyFK'] ] -= $r['sum'];
						}
					}
				}

				if( !empty($sums) ){
					$stash->store($sums, STASH_EXPIRE);
				}
			}
			$ts = $stash->getTimestamp();

			return array('lastModified' => $ts, 'data' => $sums);

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}


	/**
	 * sum the payment by month-year, type, origin and currency
	 * @param array $frame : key month and value timestamp (month format yyyy-mm)
	 * @return array
	 */
	public function getSums( $frame ){
		try {
			$result = array();
			foreach( $frame as $month => $timestamp ){
				$sum = $this->getSumByMonth($month, $timestamp);

				//add to the result only if something has changed
				if( !is_null($sum) ){
					$result[$month] = array('sums' => $sum['data'], 'lastModified' => $sum['lastModified']);
				}
			}

			return $result;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}


	/**
	 * sum the expanses for the current month and the next one
	 * @param string $timestamp : unix timestamp for cache
	 */
	public function getForecasts( $timestamp ){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $this->getOwner(), date('Y-m'));

			if( $timestamp >= 0 ){
				$ts = $stash->getTimestamp();
				if( !$stash->isMiss() && $ts == $timestamp ){
					return null;
				}
			}

			$forecasts = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$q = $this->_db->prepare("
					SELECT paymentMonth AS `month`, SUM( amount ) AS `sum`, statusFK, currencyFK
					FROM ".$this->_table."
					WHERE ownerFK = :owner
					AND typeFK != 1
					AND statusFK IN (2,4)
					AND (
							paymentMonth = IF( DAYOFMONTH(CURDATE()) > 24, DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m'), DATE_FORMAT(CURDATE(), '%Y-%m') )
						 OR paymentMonth = IF( DAYOFMONTH(CURDATE()) > 24, DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 2 MONTH), '%Y-%m'), DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m') )
						)
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
				if( !empty($forecasts) ){
					$stash->store($forecasts, STASH_EXPIRE);
				}
			}
			$ts = $stash->getTimestamp();

			return array('lastModified' => $ts, 'data' => $forecasts);

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}


	/**
	 * compile payments data for chart rendering
	 * @param boolean $returnTs : flag for the function to return the list and the ts or only the list
	 * @param boolean $tsOnly : flag for the function to return the cache creation date timestamp only
	 */
	public function getExpenseData($returnTs = false, $tsOnly = false){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $this->getOwner());

			if( $tsOnly ){
				$ts = $stash->getTimestamp();
				if( $stash->isMiss() ){
					return null;
				} else {
					return $ts;
				}
			}

			$result = $stash->get();
			$ts = null;
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$oType = new type();
				$types = $oType->loadListForFilter();

				$oCurrency = new currency();
				$currenciesWSymbol = $oCurrency->loadList();

				$q = $this->_db->prepare("
					SELECT paymentMonth AS `month`, SUM( amount ) AS `sum`, typeFK, recurrent, currencyFK
					FROM ".$this->_table."
					WHERE ownerFK = :owner
					AND typeFK = 2
					AND paymentDate >= '2011-01-25'
					GROUP BY currencyFK, typeFK, recurrent, `month`
					ORDER BY currencyFK, typeFK, recurrent, `month`
				");

				$q->execute( array(':owner' => $this->getOwner()) );

				$monthsTranslation = init::getInstance()->getMonthsTranslation();
				$result = array();
				$data = $q->fetchAll();

				if( !empty($data) ){
					$tmp = array();
					$months = array();

					foreach( $data as $d ){
						$months[] = $d['month'];

						if( !isset($tmp[ $d['currencyFK'] ][ $d['typeFK'].'_'.$d['recurrent'] ]) ){
							$tmp[ $d['currencyFK'] ][ $d['typeFK'].'_'.$d['recurrent'] ] = array();
						}
						$tmp[ $d['currencyFK'] ][ $d['typeFK'].'_'.$d['recurrent'] ][ $d['month'] ] = $d['sum'];
					}

					//get unique month, sort then translate
					$months = array_unique($months);
					sort( $months );

					$result['months'] = $months;
					foreach( $result['months'] as $key => $value ){
						$result['months'][$key] = $monthsTranslation[ substr($value, 5, 2) ].' '.substr($value, 0, 4);
					}

					//compile the data in the chart library format
					foreach( $tmp as $currencyFK => $parts ){
						$symbol = $currenciesWSymbol[ $currencyFK ]['symbol'];
						$label = $currenciesWSymbol[ $currencyFK ]['name'];

						foreach( $parts as $key => $data ){
							$key = explode('_', $key); //index 0 for typeFK value, 1 for recurrent value
							$frequency = ($key[1] == 0 ? ($key[0] == 2 ? 'ponctuelles' : 'ponctuels') : ($key[0] == 2 ? 'récurrentes' : 'récurrents') );

							foreach( $months as $m ){
								if( !isset($data[$m]) ) $data[$m] = 0;
							}

							ksort($data);
							$data = array_values($data);

							$result['sums'][] = array(
								'name' => $types[ $key[0] ].'s '.$frequency.' en '.$label.'s',
								'data' => $data,
								'stack' => ($key[0] == 1 ? 'A' : 'B').'_'.$currencyFK
							);
						}
					}
				}

				if( !empty($result) ){
					$stash->store($result, STASH_EXPIRE);
					$ts = $stash->getTimestamp();
				}
			} elseif( $returnTs ){
				$ts = $stash->getTimestamp();
			}

			if( $returnTs ){
				return array($ts, $result);
			} else {
				return $result;
			}

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}


	/**
	 * compile payments data for chart rendering
	 * @param boolean $returnTs : flag for the function to return the list and the ts or only the list
	 * @param boolean $tsOnly : flag for the function to return the cache creation date timestamp only
	 */
	public function getRecipientData($returnTs = false, $tsOnly = false){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $this->getOwner());

			if( $tsOnly ){
				$ts = $stash->getTimestamp();
				if( $stash->isMiss() ){
					return null;
				} else {
					return $ts;
				}
			}

			$result = $stash->get();
			$ts = null;
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$result = array();

				$oRecipient = new recipient();
				$recipients = $oRecipient->loadListForFilter();

				$oCurrency = new currency();
				$currenciesWSymbol = $oCurrency->loadList();
				foreach( $currenciesWSymbol as $c ){
					$result['currencies'][ $c['id'] ] = $c['symbol'];
				}

				$q = $this->_db->prepare("
					SELECT paymentMonth AS `month`, SUM( amount ) AS `sum`, currencyFK, recipientFK
					FROM ".$this->_table."
					WHERE ownerFK = :owner
					AND typeFK = 2
					AND paymentDate >= '2011-01-25'
					GROUP BY currencyFK, recipientFK, `month`
					ORDER BY currencyFK, recipientFK, `month`
				");

				$q->execute( array(':owner' => $this->getOwner()) );

				$data = $q->fetchAll();

				if( !empty($data) ){
					$tmp = array();
					$months = array();

					foreach( $data as $d ){
						$months[] = $d['month'];

						$tmp[ $d['currencyFK'] ][ $d['recipientFK'] ][ $d['month'] ] = $d['sum'];
					}

					//get unique month, sort then translate
					$months = array_unique($months);
					sort( $months );

					$monthsTranslation = init::getInstance()->getMonthsTranslation();
					foreach( $months as $key => $value ){
						$result['months'][$key] = $monthsTranslation[ substr($value, 5, 2) ].' '.substr($value, 0, 4);
					}

					//percentages and data compilation in chart library format
					foreach( $tmp as $currency => $recip ){
						foreach( $recip as $recipient => $sums ){
							foreach($months as $m){
								if( !isset($sums[ $m ]) ){
									$sums[ $m ] = 0;
								}
							}
							ksort($sums); //reorder the sums by month, so array_values will be in the correct order

							$result['sums'][] = array(
								'name' => $recipients[ $recipient ],
								'data' => array_values($sums),
								'stack' => $currency,
							);
						}
					}
				}

				if( !empty($result) ){
					$stash->store($result, STASH_EXPIRE);
					$ts = $stash->getTimestamp();
				}
			} elseif( $returnTs ){
				$ts = $stash->getTimestamp();
			}

			if( $returnTs ){
				return array($ts, $result);
			} else {
				return $result;
			}

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

}
?>
