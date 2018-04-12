<?php
// koristimo late static bindings metodu
class Db_object{
	
	public $errors=array();
	public $upload_errors_array=array(

	UPLOAD_ERR_OK=>"There is no error, the file uploaded with success.",
	UPLOAD_ERR_INI_SIZE=>"The uploaded file exceeds the upload_max_filesize directive in php.ini.",
	UPLOAD_ERR_FORM_SIZE=>" The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",
	UPLOAD_ERR_PARTIAL=>"The uploaded file was onlsy partially uploaded.",
	UPLOAD_ERR_NO_FILE=>" No file was uploaded.",
	UPLOAD_ERR_NO_TMP_DIR=>"Missing a temporary folder. Introduced in PHP 5.0.3.",
	UPLOAD_ERR_CANT_WRITE=>"Failed to write file to disk. Introduced in PHP 5.1.0.",
	UPLOAD_ERR_EXTENSION=>"A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help. Introduced in PHP 5.2.0."
	);


	public function set_file($file){
		//provjeravamo da li je prazan da li je file i da li je niz
		if(empty($file) || !$file || !is_array($file)){
			$this->errors[]="There was no file uploaded here";
			return false;
		}elseif($file['error'] != 0){
			$this->errors[]=$this->upload_errors_array[$file['error']];
			return false;
		}else {

			$this->user_image=basename($file['name']);
			$this->tmp_path=$file['tmp_name'];
			$this->type=$file['type'];
			$this->size=$file['size'];

		}

	}

	public static function find_all(){
		global $database;
		return static::find_by_query("SELECT * FROM ". static::$db_table." ");
	}

	public static function find_by_id($id){
		global $database;
		$the_result_array=static::find_by_query("SELECT * FROM " . static::$db_table." WHERE id=$id LIMIT 1");
		//array_shift izvlaci prvi element iz niza 
		return !empty($the_result_array) ? array_shift($the_result_array) : false;

	}

	public static function find_by_query($sql){
		global $database;

		$result_set=$database->query($sql);
		$the_object_array=array();

		while($row=mysqli_fetch_array($result_set)){
			$the_object_array[]=static::instantation($row);
		}

		return $the_object_array;
	}

	public static function instantation($the_record){
		//vraca klasu koja je pozvala 
		$calling_class=get_called_class();
		$the_object=new $calling_class;

		foreach ($the_record as $the_attribute => $value) {
			if ($the_object->has_the_attribute($the_attribute)) {
				$the_object->$the_attribute=$value;
			}
		}

		return $the_object;
	}

	private function has_the_attribute($the_attribute){
		$object_proporties=get_object_vars($this);

		return array_key_exists($the_attribute, $object_proporties);
	}

	protected function proporties(){
		
		$proporties=array();
		foreach (static::$db_table_fields as $db_field) {
			if(property_exists($this, $db_field)){
				$proporties[$db_field]=$this->$db_field;
			}
		}

		return $proporties;
	}


	//pozivamo funckciju escape_string na svim vrijednostima radi provjere 
	protected function clean_proporties(){
		global $database;


		$clean_proporties= array();

		foreach ($this->proporties() as $key => $value) {
			$clean_proporties[$key]=$database->escape_string($value);
		}

		return $clean_proporties;
	}


	public function save(){

		return isset($this->id)?$this->update():$this->create(); 


	}

	public function create(){
		global $database;

		$proporties=$this->clean_proporties();

		$sql="INSERT INTO " .static::$db_table. "(". implode("," , array_keys($proporties))  .")" ;
		$sql.=" VALUES ('" . implode("','" , array_values($proporties))   . "')";

		if($database->query($sql)){
			$this->id=$database->the_insert_id();
			return true;

		}else{
			return false;

		}
		
	}
	public function update(){
		global $database;

		$proporties=$this->clean_proporties();
		$proporties_pairs=array();
	//dodjeljujemo sve vrijednosti koje treba da updejtujemo
		foreach ($proporties as $key => $value) {
			$proporties_pairs[]="{$key}='{$value}'";
		}


		$sql="UPDATE " .static::$db_table. " SET ";
		$sql.=implode(",",$proporties_pairs);
		$sql.=" WHERE id=" .$database->escape_string($this->id);

		$database->query($sql);
			
		return (mysqli_affected_rows($database->connection)==1) ? true : false; 	
			
	}

	public function delete(){
		global $database;

		$id=$database->escape_string($this->id);

		$sql="DELETE FROM " .static::$db_table. " WHERE id=$id LIMIT 1 ";

		$database->query($sql);

		return (mysqli_affected_rows($database->connection)==1) ? true : false;
	}

public static function count_all(){
	global $database;

	$sql="SELECT COUNT(*) FROM ".static::$db_table;
	$result_set=$database->query($sql);
	$row=mysqli_fetch_array($result_set);
	return array_shift($row);
}



	








}





?>