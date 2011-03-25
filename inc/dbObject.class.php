<?php
/**
 * @package the-eye
 * @see DBsimpleObject
 */

/**
 * ORM abstract base class
 *
 * Usage: to use this class it's necessary to write the class as
 *
 * class DBtableName extends DBsimpleObjcet {
 *
 *		protected $_tableName = 'tableName';
 *	}
 *
 * if one want to add data member it's enough to create in the constructor a new element like
 * $this->_data['member'] = '';
 *
 * in this way it's possible to iterate over all the members of the object via l'implementation of the iterator interface
 * provided by this abstract class
 *
 * @author Francesco Corbetta <francesco.corbetta@b-i.com>
 * @version 0.1
 * @package the-eye
 * @subpackage ORM_simple_object
 *
 */

abstract class DBsimpleObject implements Iterator  {
	/**
    * @var string Contains the name of the table
	*/
	protected $_tableName;

	/**
	 * @var array Array that contains the 'properties' of th eobject
	 */
	protected $_data = array();

	/**
	 * @staticvar array Array that contains the fields of the table
	 */
	protected static $_fields = array();

	/**
	 * @staticvar array Array that contains the default values
	 */
	protected static $_defvalues = array();

	/*
	 * @staticvar object Reference to DB object
	 */
	protected $_db;


	/**
	 * the constructor of this base class requires the name of the table.
	 * In the extended class the name could be hardcoded
	 * @param var $obj optional: it could be the id value or an object returned by mysqli fetch_object)
	 */
	public function __construct($obj = null) {
		//load the names of the fields of the table
		//but only if they're not already loaded

		if ($this->_tableName == null)
			throw new BaseException('Variable _tableName cannot be null');

		if (!isset(self::$_fields[$this->_tableName]) or  count(self::$_fields[$this->_tableName]) == 0)
		{
			self::retrieveFields($this->_tableName);
		}

		$this->_db = DB::getInstance();
		//initialize the members to their default values
		foreach(self::$_fields[$this->_tableName] as $key=>$val) {
			$this->_data[$val] = self::$_defvalues[$this->_tableName][$val];
		}

		//if $obj is not null fill the members with the values contained in obj
		if (is_object($obj))
		{
			foreach($obj as $a => $v) {
				$this->_data[$a]=$obj->$a;
			}
		}
		else
		{
			$this->load($obj);
		}
	}

	/**
	 *  store the fields of the table in a static array whose key is the tablename
	 * @param string $tableName name of the table on the DB
	 */

	protected static function retrieveFields($tableName)
	{
		/*
		//this is a static context, $this is not allowed
		$_db = DB::getInstance();
		//it's not important to have a result
		$res = $_db->my_query("select * from ".$tableName." where id = '0'");
    	//first step, create the properties

    	while ($field = $res->fetch_field())
		{
			//todo: create a default value for every data type
			self::$_fields[$tableName][] = $field->name;
		}
		*/
		/*
		 * from mysql manual
		 *
		 *  As of MySQL 5.0.2, if a column definition includes no explicit DEFAULT value, MySQL determines the default value as follows:

				If the column can take NULL as a value, the column is defined with an explicit DEFAULT NULL clause. This is the same as before 5.0.2.

				If the column cannot take NULL as the value, MySQL defines the column with no explicit DEFAULT clause. For data entry, if an INSERT or REPLACE statement includes no value for the column, MySQL handles the column according to the SQL mode in effect at the time:

    	*

      		If strict SQL mode is not enabled, MySQL sets the column to the implicit default value for the column data type.
    	*

      		If strict mode is enabled, an error occurs for transactional tables and the statement is rolled back. For non-transactional tables, an error occurs, but if this happens for the second or subsequent row of a multiple-row statement, the preceding rows will have been inserted.
			---------------------------------------------
		 *
		 *take a look also at the page
		 * http://mysql.com/doc/refman/5.0/en/data-type-defaults.html
		 */


		$_db = DB::getInstance();
		//it's not important to have a result
		$res = $_db->my_query("SHOW FIELDS FROM ".$tableName);
    	//first step, create the properties

    	while ($myrow = $res->fetch_array(MYSQLI_ASSOC))
		{

			self::$_fields[$tableName][] = $myrow['Field'];
			//get the type
			if (strpos($myrow['Type'],'('))
			{
				$type = substr($myrow['Type'],0,strpos($myrow['Type'],'('));
			} else {
				$type = $myrow['Type'];
			}

			//inizialize the default value

			if ($myrow['Default']!='')
			{
				self::$_defvalues[$tableName][$myrow['Field']] = $myrow['Default'];
			}
			else
			{

				if ($myrow['Null'] == 'YES')
				{
					self::$_defvalues[$tableName][$myrow['Field']] = null;
				}
				else
				{

					switch (strtoupper($type))
					{
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
							self::$_defvalues[$tableName][$myrow['Field']] = 0;
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
							self::$_defvalues[$tableName][$myrow['Field']] = '';
						break;
						//date
						//http://mysql.com/doc/refman/5.0/en/date-and-time-types.html
						case "DATE":
							self::$_defvalues[$tableName][$myrow['Field']] = '0000-00-00';
						break;
						case "DATETIME":

							self::$_defvalues[$tableName][$myrow['Field']] = '0000-00-00 00:00:00';
						break;
						case "TIMESTAMP":
							self::$_defvalues[$tableName][$myrow['Field']] = '0000-00-00 00:00:00';
						break;
						case "TIME":
							self::$_defvalues[$tableName][$myrow['Field']] = '00:00:00';
						break;
						case "YEAR":
							self::$_defvalues[$tableName][$myrow['Field']] = '0000';
						break;
					}
				}
			}
		}
	}

	/**
	 * __get() is automatically called from PHP when one refers to a member of the class wich is not defined.
	 * It reads data in $this->_data
	 * @param string $member name of the member
	 */
	public function __get($member) {

        if (isset($this->_data[$member])) {
            return $this->_data[$member];
        }
    }

	/**
	 * __set() is automatically called from PHP when one refers to a member of the class wich is not defined.
	 * It stores data in $this->_data
	 * @param string $member name of the member
	 * @param string $value value of the member
	 */
    public function __set($member, $value) {
        // The ID of the dataset is read-only

        $this->_data[$member] = $value;

    }

    /**
	 * __call() is automatically called from PHP when one refers to a method of the class wich is not defined.
	 *
	 * @param string $member name of the member
	 * @param string $value value of the member
	 */
    function __call($method, $arguments) {

        $prefix = strtolower(substr($method, 0, 3));
        $property = strtolower(substr($method, 3));

        if (empty($prefix) || empty($property)) {
            return;
        }

        if ($prefix == "get" && isset($this->_data[$property])) {
            return $this->_data[$property];
        }

        if ($prefix == "set") {

            $this->_data[$property] = $arguments[0];
        }
    }

	/**
	 * load data from the DB
	 * it throws the exception thrown by DB object
	 * @param integer $id id of the record on the DB
	 */
    public function load($id)
    {

    	//if id=0 the resultset is void but it contains column names
    	$res = $this->_db->my_query("select * from ".$this->_tableName." where id = '".$id."'");
		/*
		// fill the properties
    	if ($res->num_rows == 0)
    	{
    		//inizialise to default values
    		foreach(self::$_fields[$this->_tableName] as $key=>$val) {
    			$this->_data[$val] = self::$_defvalues[$this->_tableName][$val];
    		}
    	}
    	*/

    	while ($myrow = $res->fetch_array(MYSQLI_ASSOC))
		{
    		foreach(self::$_fields[$this->_tableName] as $key=>$val) {
    			$this->_data[$val] = $myrow[$val];
    		}
		}

		$res->free();
    }


    /*
	 * save data on the DB
	 * if id is different from 0 it performes an update, otherwise it performs an insert
	 * it throws the exception thrown by DB object

    public function save()
    {
    	if ($this->_data['id'] == 0)
    	{
    		//insert
    		//build the insert starting from self::$_fields
    		$_fields = '';
    		$values = '';

    		foreach (self::$_fields[$this->_tableName] as $key)
    		{
    			if ($key != 'id')
    			{
    				$_fields .= $key.',';
    				$values .= '\''.addslashes($this->_data[$key]).'\',';
    			}
    		}
    		$_fields = substr($_fields,0,strlen($_fields)-1);
    		$values = substr($values,0,strlen($values)-1);
    		$strSql = 'insert into '.$this->_tableName.' ('.$_fields.') values ('.$values.')';
    	}
    	else
    	{
    		//update
    		//build the update starting from self::$_fields
    		$whereId = '';
    		$field_value = '';
    		foreach (self::$_fields[$this->_tableName] as $key)
    		{
    			if ($key == 'id')
    			{
    				$whereId = ' id = '.$this->_data[$key];
    			} else {
    				$field_value .= ' '.$key.' = \''.addslashes($this->_data[$key]).'\',';
    			}

    		}
    		$field_value = substr($field_value,0,strlen($field_value)-1);
    		$strSql = 'update '.$this->_tableName.' set '.$field_value.' where '.$whereId;
    	}

    	$this->_db->my_query($strSql);

    	if ($this->_data['id'] == 0)
    		$this->_data['id'] = $this->_db->getLastId();
    }
    */

     /*
	 * delete data on the DB
	 * if id is equals to 0 it throws an exceptions
	 * it throws the exception thrown by DB object

    public function delete()
    {
    	if ($this->_data['id'] == 0)
    		throw new Exception('Impossible to delete record, id is null');

    	$strSql = 'delete from '.$this->_tableName.' where id = '.$this->_data['id'];
    	$this->_db->my_query($strSql);
    	//what to do when a object is deleted?

    }
    */
    //implementation of the iterator Interface

    public function rewind() {

       reset($this->_data);
   }

   public function current() {
       $var = current($this->_data);
       return $var;
   }

   public function key() {
       $var = key($this->_data);
       return $var;
   }

   public function next() {
       $var = next($this->_data);
       return $var;
   }

   public function valid() {
       $var = $this->current() !== false;
       return $var;
   }
}
?>