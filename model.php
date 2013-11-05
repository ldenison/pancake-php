<?php 
require_once(getenv("DOCUMENT_ROOT")."/Database/database.php");

class Model {
	public $db;

	protected $id;

	public static $table;
	public static $databaseUser;

	protected $columns;		//Array of all columns of the object
	protected $data;		//Actual data with column name as index
	protected $changed;		//Indicates if a variable has been changed and needs to be saved

	/**
	 * 
	 * @param Int $id - pass in a 0 if it is a new object not yet in the Database
	 * @param Array $data - optional, include array of data if it is a new object ($id=0)
	 */
	function __construct($id,$data=null) {
		$this->db = DB::connect(static::$databaseUser);
		
		//Describe the columns of the object
		$query = "select column_name,data_type from all_tab_columns where table_name='".static::$table."'";
		$sth = $this->db->prepare($query);
		$sth->execute();
		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			//$this->columns[] = Array($row['COLUMN_NAME'],$row['DATA_TYPE']);
			$this->columns[] = $row['COLUMN_NAME'];
		}
		//die(var_dump($this->columns));
		if($id==0) {
			if($data!=null) {
				foreach(array_keys($data) as $k) {
					//check in columns
					$key = strtoupper($k);
					$this->data[$key] = $data[$k];
					$this->changed[] = $key;
				}
			}
		}
		else {
			$query = "SELECT * FROM ".static::$table." WHERE id=:id";

			$this->id = $id;
			$sth = $this->db->prepare($query);
			$sth->bindParam(":id",$this->id,PDO::PARAM_INT);
			$sth->execute();
			if($row = $sth->fetch(PDO::FETCH_ASSOC)) {
				foreach(array_keys($row) as $key) {
					$this->data[$key] = $row[$key];
				}
			}
		}
	}

	function save() {
		//Make sure there is data to be saved
		if(isset($this->changed)) {
			//If id is 0, it is a brand new model, perform INSERT
			if($this->id==0) {
				$query = "INSERT INTO ".static::$table."(";
				foreach($this->changed as $c) {
					if(in_array(strtoupper($c),$this->columns)) {
						$query.= "$c,";
					}
					else {
						throw new Exception("$c is not a column");
					}
				}
				$query = substr_replace($query ,"",-1);
				$query.= ") VALUES (";
				foreach($this->changed as $c) {
					$query .= ":$c,";
				}
				$query = substr_replace($query ,"",-1);
				$query .= ")";
				$sth = $this->db->prepare($query);
				foreach($this->changed as $c) {
					$sth->bindParam(":$c",$this->data[$c]);
				}
				if($sth->execute()) {
					unset($this->changed);
					$query = "SELECT MAX(id) FROM ".static::$table;
					$sth = $this->db->prepare($query);
					$sth->execute();
					$row = $sth->fetch(PDO::FETCH_ASSOC);
					if(isset($row['MAX(ID)'])) {
						$this->data['ID'] = $row['MAX(ID)'];
						$this->id = $row['MAX(ID)'];
					}
				}
				else {
					throw new Exception("Error saving new data");
				}
			}
			//If id is not 0, it is existing, perform UPDATE
			else {
				foreach($this->changed as $c) {
					if(in_array($c,$this->columns)) {
						$query = "UPDATE " .static::$table. " SET $c=:value WHERE id=:id";
						$sth = $this->db->prepare($query);
						$sth->bindParam(":value",$this->data[$c]);
						$sth->bindParam(":id",$this->id,PDO::PARAM_INT);
						
						if($sth->execute()) {
							unset($this->changed);
							return true;
						}
						else {
							throw new Exception("Error saving data");
						}
					}
					//This should never happen if you use the setter methods
					else {
						throw new Exception("$c is not a column");
					}
					unset($this->changed);
				}
			}
		}
	}

	function set($column, $value) {
		$column = strtoupper($column);
		if(in_array($column,$this->columns)) {
			$this->data[$column] = $value;
			$this->changed[] = $column;
		}
		else {
			throw new Exception("$column is not a column");
		}
	}

	function get($column) {
		if(isset($this->data[strtoupper($column)])) {
			return $this->data[strtoupper($column)];
		}
		else {
			return null;
		}
	}
	function getID() {
		return $this->id;
	}

	static function getTable() {
		return static::$table;
	}
}
?>