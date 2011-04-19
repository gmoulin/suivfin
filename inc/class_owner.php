<?php
/**
 * Class for paiement owners management
 *
 * class name is in lowerclass to match table name ("common" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Payement
 * @category Owners
 */
class owner extends common {
	protected $_table = 'owner';
	protected $_link = 'limits';
	protected $_relatedStashes = array();
	protected $_relatedTimestamps = array('payment');

	// Constructor
	public function __construct( $id = null ){
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
			'action'		=> FILTER_SANITIZE_STRING,
			'id'			=> FILTER_SANITIZE_NUMBER_INT,
			'name'			=> FILTER_SANITIZE_STRING,
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
			//errors are set to #name because #id is hidden
			if( $action == 'update' ){
				if( is_null($id) || $id === false ){
					$errors[] = array('name', 'Identifiant incorrect.', 'error');
				} else {
					$id = filter_var($id, FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $id === false ){
						$errors[] = array('name', 'Identifiant de la personne incorrect.', 'error');
					} else {
						//check if id exists in DB
						if( self::existsById($id) ){
							$formData['id'] = $id;
						} else {
							$errors[] = array('name', 'Identifiant de la personne inconnu.', 'error');
						}
					}
				}
			}

			if( $action == 'update' || $action == 'add' ){
				//name
				if( is_null($name) || $name === false ){
					$errors[] = array('name', 'Libellé incorrect.', 'error');
				} elseif( empty($name) ){
					$errors[] = array('name', 'Le libellé est requis.', 'required');
				} else {
					$formData['name'] = trim($name);
				}

				//unicity
				if( empty($errors) ){
					$check = self::existsByLabel($name);
					if( $check ){
						if( $action == 'add' || ($action == 'update' && $formData['id'] != $check) ){
							$errors[] = array('name', 'Cette personne est déjà présente.', 'error');
						}
					}
				}
			}
		}
		$formData['errors'] = $errors;

		if( empty($errors) ){

			if( !empty($this->id) ){
				$this->load($id);
			}

			foreach( self::$_fields[$this->_table] as $k => $v ){
				$this->$k = $formData[$k];
			}
		}

		return $formData;
	}

	/**
	 * return the linked origins (bank accounts) and currencies for a given owner
	 */
	public function getLimits( $forAll = false ){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__, ( !$forAll ? $this->getOwner() : 'all' ));
			$list = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$q = $this->_db->prepare("
					SELECT ".( $forAll ? "owner_id, " : "" )." origin_id, currency_id
					FROM ".$this->_link."
					".( !$forAll ? "WHERE owner_id = :owner" : "" )."
				");
				if( !$forAll ){
					$q->execute( array(':owner' => $this->getOwner()) );
				} else {
					$q->execute();
				}

				$list = $q->fetchAll();

				if( !empty($list) ) $stash->store($list, STASH_EXPIRE);
			}

			return $list;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}
}
?>