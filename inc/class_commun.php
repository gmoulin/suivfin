<?php
/**
 * class for database interractions shared functions
 *
 * class name is in lowerclass to match table name ("common" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.pro@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Common
 * @category Common
 */
class common {
	/**
	 * @var object Contains the database connexion handler
	 */
	protected $_db;

	/**
	 * @var string Contains the table name
	 */
	protected $_table;

	/**
	 * @var array that contains the object 'properties'
	 */
	protected $_data = array();

	/**
	 * @var array Array that contains the fields of the table
	 */
	protected static $_fields = array();

	/**
	 * @var array Array that contains the default values
	 */
	protected static $_defvalues = array();


	/**
	 * @require _table: the table name
	 * @param integer $id: identifier
	 */
	public function __construct( $id = null ){
		try {

			//load the names of the fields of the table
			//but only if they're not already loaded

			if( is_null($this->_table) ) throw new Exception('Variable _table cannot be null');

			if( !isset(self::$_fields[$this->_table]) || empty(self::$_fields[$this->_table]) ){
				self::getColumns($this->_table);
			}

			$this->_db = init::getInstance()->dbh();

			//initialize the members to their default values
			foreach( self::$_fields[$this->_table] as $key=>$val ){
				$this->_data[$val] = self::$_defvalues[$this->_table][$val];
			}

			if( !empty($id) ){
				$this->load($id);
			}

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/*
	 * close the connection when the class is garbaged
	 */
	public function __destruct(){
		$this->_db = null;
	}

	/**
	 * format search words for full text queries
	 *
	 * @param string $keywords
	 * return string
	 */
	public function prepareForFullTextQuery( $keywords ){
		$keywords = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', $keywords, -1, PREG_SPLIT_NO_EMPTY);

		return implode(',', $keywords);
	}

	/**
	 * fill the table fields into $_fields for a given table
	 * @param string $table
	 */
	protected static function getColumns( $table ){
		try {
			$_db = init::getInstance()->dbh();

			$res = $_db->prepare("SHOW FIELDS FROM :table");

			$res->execute(array(':table' => $table));

			while( $r = $res->fetch() ){

				self::$_fields[$table][] = $r['Field'];

				//get the type
				if( strpos($r['Type'], '(') !== false ){
					$type = substr($r['Type'], 0, strpos($r['Type'],'('));
				} else {
					$type = $r['Type'];
				}

				//inizialize the default value
				if( !empty($r['Default']) ){
					self::$_defvalues[$table][ $r['Field'] ] = $r['Default'];

				} elseif( $r['Null'] == 'YES' ){
					self::$_defvalues[$table][ $r['Field'] ] = null;

				} else {
					switch( strtoupper($type) ){
						//numeric
						case "BIT":
						case "TINYINT":
						case "BOOL":
						case "BOOLEAN":
						case "SMALLINT":
						case "MEDIUMINT":
						case "INT":
						case "INTEGER":
						case "BIGINT":
						case "FLOAT":
						case "DOUBLE":
						case "DECIMAL":
						case "DEC":
								self::$_defvalues[$table][ $r['Field'] ] = 0;
							break;

						//string
						case "CHAR":
						case "VARCHAR":
						case "BINARY":
						case "VARBINARY":
						case "TINYBLOB":
						case "TINYTEXT":
						case "BLOB":
						case "TEXT":
						case "MEDIUMBLOB":
						case "MEDIUMTEXT":
						case "LONGBLOB":
						case "LONGTEXT":
						case "ENUM":
						case "SET":
								self::$_defvalues[$table][ $r['Field'] ] = '';
							break;

						//date
						case "DATE":
								self::$_defvalues[$table][ $r['Field'] ] = '0000-00-00';
							break;
						case "DATETIME":
								self::$_defvalues[$table][ $r['Field'] ] = '0000-00-00 00:00:00';
							break;
						case "TIMESTAMP":
								self::$_defvalues[$table][ $r['Field'] ] = '0000-00-00 00:00:00';
							break;
						case "TIME":
								self::$_defvalues[$table][ $r['Field'] ] = '00:00:00';
							break;
						case "YEAR":
								self::$_defvalues[$table][ $r['Field'] ] = '0000';
							break;

						default:
								throw new Exception('unknow column type');
							break;
					}
				}
			}

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * __get() is automatically called from PHP when one refers to a member of the class wich is not defined.
	 * @param string $k: name of the member
	 */
	public function __get($k) {
		if( isset($this->_data[$k]) ){
			return $this->_data[$k];
		}
	}

	/**
	 * __set() is automatically called from PHP when one refers to a member of the class wich is not defined.
	 * @param string $k: name of the member
	 * @param string $v: value of the member
	 */
	public function __set($k, $v) {
		$this->_data[$k] = $v;
	}

	/**
	 * load data from the database
	 * @param integer $id: identifier of the record in the table
	 */
	public function load( $id ){
		try {
			if( empty($id) {
				throw Exception('Can not load '.$this->_table.' entry, no id given.');
			}

			$res = $this->_db->prepare("
				SELECT * FROM :table WHERE id = :id
			");

			$res->execute(array(
				':table' => $this->_table,
				':id' => $id,
			));

			$entry = $res->fetch();
			if( !empty($entry) ){
				foreach( self::$_fields[$this->_table] as $k => $v ){
					$this->_data[$k] = $entry[$v];
				}
			}

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param string $order: field for the ORDER BY
	 * @return array[key][entry]
	 */
	public function loadList( $order = null ){
		try {
			$params[':table'] = $this->_table;

			$loadList = $this->_db->prepare("
				SELECT * FROM :table ".(!empty($order) ? " ORDER BY ".$order : "")."
			");

			$loadList->execute($params);

			$list = array();
			while( $rs = $loadList->fetch() ){
				foreach( self::$_fields[$this->_table] as $k => $v ){
					$list[ $rs['id'] ][ $k ] = $rs[$k];
				}
			}

			return $list;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @return array[key][entry]
	 */
	public function loadListForFilter(){
		try {
			$params[':table'] = $this->_table;

			$loadList = $this->_db->prepare("
				SELECT id, name FROM :table ORDER BY name
			");

			$loadList->execute($params);

			$list = array();
			while( $rs = $loadList->fetch() ){
				$list[ $rs['id'] ] = $rs['name'];
			}

			return $list;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * save data in the database
	 * insert for id = 0, else update
	 */
	public function save(){
		try {
			$params = array(':table' => $this->_table);

			//insert
			if( empty($this->_data['id']) ){
				$action = 'add';

				//build the insert starting from self::$fields
				$fields = '';
				$values = '';

				foreach( self::$_fields[$this->_table] as $key ){
					if( $key != 'id' ){
						$fields .= $key.', ';

						if( $key == 'creationDate' ){
							$field_value .= ' '.$key.' = NOW(),';

						} elseif( isset($this->_data[$key]) && !is_null($this->_data[$key]) ){
							$values .= ':'.$key.', ';
							$params[':'.$key] = $this->_data[$key];
						} else {
							$values .= 'NULL';
						}
					}
				}

				$fields = substr($fields, 0, -1);
				$values = substr($values, 0, -1);
				$sql = 'INSERT INTO :table ('.$fields.') VALUES ('.$values.')';

			//update
			} else {
				$action = 'update';

				//build the update starting from self::$fields
				$where = '';
				$field_value = '';

				foreach( self::$_fields[$this->_table] as $key ){
					if( $key == 'id' ){
						$where = ' '.$key.' = :id';
						$params[':id'] = $this->_data[$key];

					} elseif( $key == 'modificationDate' ){
						$field_value .= ' '.$key.' = NOW(),';

					} elseif( isset($this->_data[$key]) and !is_null($this->_data[$key]) ){
						$field_value .= ' '.$key.' = :'.$key.',';
						$params[':'.$key] = $this->_data[$key];

					} else {
						$field_value .= ' '.$key.' = NULL,';
					}
				}
				$field_value = substr($field_value, 0, -1);
				$sql = 'UPDATE :table SET '.$field_value.' WHERE '.$where;
			}

			$q = $this->_db->prepare($sql);

			$q->execute($params);

			if( empty($this->_data['id']) ){
				$this->_data['id'] = $this->_db->lastInsertId();
			}

			if( get_class( $this ) != 'list_timestamp' ){
				$this->_cleanCaches();
			}

			return true;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * delete data in the database
	 */
	public function delete(){
		try{
			if( empty($this->_data['id']) ){
				throw new Exception('Impossible to delete record, id is null');

			if( $this->_isUsed() ){
				return 'used';
			}

			$q = $this->_db->prepare('DELETE FROM :table where id = :id');
			$q->excute(array(
				':table' => $this->_table,
				':id' => $this->_data['id'],
			));

			$this->_cleanCaches();

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}

		return true;
	}

	/**
	 * @return boolean
	 */
	protected function _isUsed( $field ) {
		try {
			$verif = true;

			$isUsed = $this->db->prepare("
				SELECT COUNT(DISTINCT ".$this->_table."FK) AS verif
				FROM flux
				WHERE ".$this->_table."FK = :id");

			$isUsed->execute( array( ':id' => $this->_data['id'] ) );

			$result = $isUsed->fetch();
			if( !empty($result) && $result['verif'] == 0 ) {
				$verif = false;
			}

			return $verif;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @return array[][]
	 */
	public function delImpact() {
		try {
			$delImpact = $this->db->prepare("
				SELECT id, label, creationDate
				FROM flux
				WHERE ".$this->_table."FK = :fk
				ORDER BY creationDate
			");

			$delImpact->execute( array( ':fk' => $id ) );

			return $delImpact->fetchAll();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return boolean
	 */
	public static function existsById( $id ) {
		try {
			$verif = false;

			$exists = $this->db->prepare("
				SELECT COUNT(id) AS verif
				FROM :table
				WHERE id = :id");

			$exists->execute( array( ':table' => get_called_class(), ':id' => $id ) );

			$result = $exists->fetch();
			if( !empty($result) && $result['verif'] == 1 ) {
				$verif = true;
			}

			return $verif;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param string $name
	 * @return id or false
	 */
	public static function existsByLabel( $name ) {
		try {
			$existsByLabel = $this->db->prepare('
				SELECT id
				FROM :table
				WHERE name = :name
			');

			$existsByLabel->execute( array( ':table' => get_called_class(), ':name' => $name ) );

			$result = $existsByLabel->fetchAll();
			if( count($result) > 0 ){
				$id = $result[0]['id'];
			} else $id = false;

			return $id;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * clean the caches for the class lists
	 * @params array $_relatedStathes: list the related stashes which will be cleaned
	 * @params array $_relatedTimestamps: list the related timestamps which need updating
	 */
	protected function _cleanCaches(){
		//clear stash cache
		$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
		$stash = new Stash($stashFileSystem);

		$stash->setupKey(get_class(this));
		$stash->clear();

		if( isset($this->_relatedStathes) && !empty($this->_relatedStathes) ){
			foreach( $this->_relatedStathes as $s ){
				$stash->setupKey($s);
				$stash->clear();
			}
		}

		//update caches timestamps
		$ts = new list_timestamp();
		$ts->refresh(get_class(this));

		if( isset($this->_relatedTimestamps) && !empty($this->_relatedTimestamps) ){
			foreach( $this->_relatedTimestamps as $t ){
				$ts->refresh($t);
			}
		}
	}
}
?>