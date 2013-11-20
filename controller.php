<?php
require_once(getenv("DOCUMENT_ROOT")."/Database/database.php");
require_once('model.php');

class Controller {
	public $db;

	public static $modelPath;
	public static $table;
	public static $model;
	public static $application_name;

	public static $databaseUser;

	function __construct() {
		$this->db = DB::connect(static::$databaseUser);
		$model = strtolower(static::$model);
		$path = getenv("DOCUMENT_ROOT").static::$modelPath;
		require_once($path);
	}

	function index() {
		$query = "SELECT id FROM ".static::$table." ORDER BY id ASC";
		$sth = $this->db->prepare($query);
		$sth->execute();

		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$models[] = new static::$model($row['ID']);
		}
		if(isset($models)) {
			return $models;
		}
		else return false;
	}

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
		
		$sth->bindParam(":value",$value);
		$sth->execute();

		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$models[] = new static::$model($row['ID']);
		}
		if(isset($models)) {
			return $models;
		}
		else return false;
	}
	
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
}
?>