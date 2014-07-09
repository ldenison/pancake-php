<?php
require_once(getenv("DOCUMENT_ROOT")."/Database/database.php");
require_once('model.php');

class Controller {
	public $db;

<<<<<<< HEAD
=======
	public static $modelPath;
>>>>>>> 57de4889e91571d70f4c08adbbcac516fb7ba19b
	public static $table;
	public static $model;
	public static $application_name;

	public static $databaseUser;

	function __construct() {
		$this->db = DB::connect(static::$databaseUser);
<<<<<<< HEAD
	}

	function index($orderBy="id ASC") {
		$query = "SELECT * FROM ".static::$table." ORDER BY $orderBy";
=======
		$model = strtolower(static::$model);
		$path = getenv("DOCUMENT_ROOT").static::$modelPath;
		require_once($path);
	}

	function index() {
		$query = "SELECT id FROM ".static::$table." ORDER BY id ASC";
>>>>>>> 57de4889e91571d70f4c08adbbcac516fb7ba19b
		$sth = $this->db->prepare($query);
		$sth->execute();

		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
<<<<<<< HEAD
			$models[] = new static::$model($row['ID'],$row);
=======
			$models[] = new static::$model($row['ID']);
>>>>>>> 57de4889e91571d70f4c08adbbcac516fb7ba19b
		}
		if(isset($models)) {
			return $models;
		}
		else return false;
	}

<<<<<<< HEAD
	function getBy($column,$value, $like=false, $order="ASC") {
		$value = strtolower($value);
		if($like) {
			$query = "SELECT * FROM ".static::$table. " WHERE lower($column) LIKE :value ORDER BY $column $order";
			$value = "%".$value."%";
		}
		else {
			$query = "SELECT * FROM ".static::$table. " WHERE lower($column)=:value ORDER BY id DESC";
		}
		$sth = $this->db->prepare($query);
=======
	function getBy($column,$value, $like=false) {
		$value = strtolower($value);
		if($like) {
			$query = "SELECT id FROM ".static::$table. " WHERE lower($column) LIKE :value ORDER BY id DESC";
			$value = "%".$value."%";
		}
		else {
			$query = "SELECT id FROM ".static::$table. " WHERE $column=:value ORDER BY id DESC";
		}
		$sth = $this->db->prepare($query);
		
>>>>>>> 57de4889e91571d70f4c08adbbcac516fb7ba19b
		$sth->bindParam(":value",$value);
		$sth->execute();

		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
<<<<<<< HEAD
			$models[] = new static::$model($row['ID'],$row);
=======
			$models[] = new static::$model($row['ID']);
>>>>>>> 57de4889e91571d70f4c08adbbcac516fb7ba19b
		}
		if(isset($models)) {
			return $models;
		}
		else return false;
	}
<<<<<<< HEAD
	function countBy($column,$value, $like=false) {
		$value = strtolower($value);
		if($like) {
			$query = "SELECT COUNT(id) FROM ".static::$table. " WHERE lower($column) LIKE :value";
			$value = "%".$value."%";
		}
		else {
			$query = "SELECT COUNT(id) FROM ".static::$table. " WHERE $column=:value";
		}
		$sth = $this->db->prepare($query);
		$sth->bindParam(":value",$value);
		$sth->execute();
	
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		if(!empty($row)) {
			return $row['COUNT(ID)'];
		}
		else return 0;
	}

	/**
	 * Filter models by clauses
	 * @param Mixed $clauses - Array or Arrays specifying the filter clauses
	 * $clause['column'],$clause['value'],$clause['comparator'],$clause['prepend'] must be 
	 * used as array indicies
	 * @returns Array of Models if any match, or false if none
	 */
	function filter ($clauses) {
		$query = "SELECT * FROM ".static::$table." WHERE ";
		//If its multiple clauses, go through each
		if(isset($clauses[0])) {
			foreach($clauses as $c) {
				if(!empty($c['prepend'])) {
					$query .= " " .$c['prepend']." ";
				}
				$query .= $c['column']." ".$c['comparator']." :".$c['column'];
			}
		}
		//Otherwise it is a 1D array, with 1 clause
		else {
			if(!empty($clauses['prepend'])) {
				$query .= " " .$c['prepend']." ";
			}
			$query .= $clauses['column']." ".$clauses['comparator']." :".$clauses['column'];
		}
		$sth = $this->db->prepare($query);
		if(isset($clauses[0])) {
			foreach($clauses as $c) {
				$sth->bindParam(":".$c['column'],$c['value']);
			}
		}
		else {
			$sth->bindParam(":".$clauses['column'],$clauses['value']);
		}
		$sth->execute();
		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$models[] = new static::$model($row['ID'],$row);
		}
		if(isset($models)) {
			return $models;
		}
		else return false;
	}
=======
	
	function create($data,$returnURL) {
		$model = new static::$model(0,$data);
		try {
			$model->save();
			$_SESSION[static::$application_name]['message'] = static::$model . " created";
			header("Location: $returnURL");
			die();
		}
		catch(Exception $e) {
			$_SESSION[static::$application_name]['error'] = $e->getMessage();
			header("Location: $returnURL");
			die();
		}
	}
	
	function update($data,$returnURL) {
		$model = new static::$model($data['id']);
		unset($data['id']);
		foreach(array_keys($data) as $key) {
			$str = "$key : " . $data[$key];
			$model->set($key,$data[$key]);
		}
		try {
			$model->save();
			$_SESSION[static::$application_name]['message'] = static::$model . " saved";
			header("Location: $returnURL");
			die();
		}
		catch(Exception $e) {
			$_SESSION[static::$application_name]['error'] = $e->getMessage();
			header("Location: $returnURL");
			die();
		}
	}
	
	/*DO NOT USE YET
	function printTable($objects, $columns, $options) {
		$str = "<table class='table tabled-bordered table-striped table-condensed'><tr>";
		foreach($columns as $c) {
			$str.= "<th>$c</th>";
		}
		$str.="</tr>";
		foreach($objects as $o) {
			$str.="<tr>";	
		
			foreach($columns as $c) {
				$str.="<td>".$o->get($c)."</td>";
			}
			$str.="</tr>";
		}
		$str.="</table>";
		return $str;
	}
	*/
>>>>>>> 57de4889e91571d70f4c08adbbcac516fb7ba19b
}
?>