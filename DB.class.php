<?php
	/**
     * @param Library for CRUD operation
     *********************************************************************************
     * @param v2.1 Beta
     *********************************************************************************
     * @param Copyright © 2018 PyRu Inc.
     */
	class DB{
		protected static $tableColumns; //splice first column i.e primary field
		protected $db;
		protected $table;
		protected $data;
		protected $encrypt 	= 0;
		protected $encryptKey = 'DEFAULT';
		protected $isEncryptColumns = 0;
		protected $encryptColumns = [];
		protected $dataKeys;
		protected $dataValues;
		protected $newValues;
		protected $columns = [];
		protected $condition = [];
		protected $response= [];

		public function connect($db){
			$this->db = $db;
		}

		public function table($table){
			$this->table = $table;
		}

		public static function getTableColumns($table){
			$query 			 = 'SHOW COLUMNS FROM '.$table;
			$con 			 = $this->db->prepare($query);
			$con->execute();
			$getTableColumns = [];
			while($row = $con->fetch(PDO::FETCH_ASSOC)){
				$getTableColumns[] = ($row['Field']);
			}
			array_splice($getTableColumns, 0, 1);
			return self::$tableColumns = $getTableColumns;
		}

		public function data($data){
			if(is_array($data)){
				$this->data 	  = $data;
				$this->dataKeys   = array_keys($data);
				$this->dataValues = array_values($data); 
			}
			else
				echo "data must be an array";
		}

		public function encrypt($key){
			$this->encrypt = 1;
			$this->encryptKey = $key;
		}

		public function encryptColumns($encryptColumns){
			$this->isEncryptColumns = 1;
			if(is_array($encryptColumns))
				$this->encryptColumns = $encryptColumns;
			else
				$this->encryptColumns[] = $encryptColumns;
		}

		public function create(){
			if(!$this->encrypt){
				$dataKeys   = implode(', ', $this->dataKeys); //comma separated keys
				$dataValues = "AES_ENCRYPT('".implode("', '$this->encryptKey'), AES_ENCRYPT('", $this->dataValues)."', '$this->encryptKey')"; //comma and quoted values
				$query 		= 'INSERT INTO '.$this->table.'('.$dataKeys.') VALUES('.$dataValues.')';
				$con 		= $this->db->prepare($query);
				$con1 		= $con->execute();
			}
			if($this->encrypt && $this->isEncryptColumns){
				$dataKeys   = implode(', ', $this->dataKeys); //comma separated keys
				$dataValues = [];
				foreach($this->dataKeys as $dataKey){
					if(in_array($dataKey, $this->encryptColumns))
						$dataValues[] = 'AES_ENCRYPT(\''.$this->data[$dataKey].'\', \''.$this->encryptKey.'\')';
					else
						$dataValues[] = '\''.$this->data[$dataKey].'\'';
				}
				
				$dataValues = implode(', ', $dataValues);
				$query 		= 'INSERT INTO '.$this->table.'('.$dataKeys.') VALUES('.$dataValues.')';
				$con 		= $this->db->prepare($query);
				$con1 		= $con->execute();
			}

			else{
				$dataKeys   = implode(', ', $this->dataKeys); //comma separated keys
				$dataValues = '\''.implode('\', \'', $this->dataValues).'\''; //comma and quoted values
				$query 		= 'INSERT INTO '.$this->table.'('.$dataKeys.') VALUES('.$dataValues.')';
				$con 		= $this->db->prepare($query);
				$con1 		= $con->execute();
			}
			if($con1)
				return $this->response = ['status'=>'1', 'message'=>'Data Added Successfully'];
			else
				return $this->response = ['status'=>'0', 'message'=>'Error Occured'];
		}

		public function update(){
			if($this->isExist()){
				$len 			= sizeof($this->data);
				$assignValues   = $this->dataKeys[0].' = "'.$this->dataValues[0].'"';
				$condition 		= $this->dataKeys[0].' = "'.$this->dataValues[0].'"'; 
				for($i=1;$i<$len;$i++){
					$assignValues .= ', '.$this->dataKeys[$i].' = "'.$this->dataValues[$i].'"';
					$condition 	  .= ' OR '.$this->dataKeys[$i].' = "'.$this->dataValues[$i].'"';
				}
				$query = 'UPDATE '.$this->table.' SET '.$assignValues.' WHERE '.$condition;
				$con   = $this->db->prepare($query);
				$con1  = $con->execute();
				return $con1 ? $this->response = ['status'=>'1', 'message'=>'Values Updated Successfully'] : $this->$response = ['status'=>'0', 'message'=>'Error Occured'];
			}else{
				echo "<p style='color: red;'>Data not exist</p>";
			}
		}

		public function isExist(){
			$dataKeys   	= implode(', ', $this->dataKeys); //comma separated keys
			$len 			= sizeof($this->data);
			$assignValues   = $this->dataKeys[0].' = "'.$this->dataValues[0].'"';
			$condition 		= $this->dataKeys[0].' = "'.$this->dataValues[0].'"'; 
			for($i=1;$i<$len;$i++){
				$assignValues .= ', '.$this->dataKeys[$i].' = "'.$this->dataValues[$i].'"';
				$condition 	  .= ' OR '.$this->dataKeys[$i].' = "'.$this->dataValues[$i].'"';
			}
			$query 		= 'SELECT '.$dataKeys.' FROM '.$this->table.' WHERE  '.$condition;
			$con = $this->db->prepare($query);
			$con->execute();
			$rowCount = $con->rowCount();
			if($rowCount >= 1)
				return 1;
			else
				return 0;
		}

		public function delete($db, $table, $values){
			if(self::isExist($db, $table, $values)){
				$condition = self::$values_keys[0].' = "'.self::$values_values[0].'"';
				$query 	= 'DELETE FROM '.$table.' WHERE '.$condition;
				$con = $db->prepare($query);
				$response = $con->execute();
				if($response){
					return self::$response = ['status'=>'1', 'message'=>'Deleted successfully'];
				}else{
					return self::$response = ['status'=>'0', 'message'=>'Error Occured'];
				}
			}
		}

		public function export($db, $table, $directory){
			$con = $db->prepare('SELECT * FROM '.$table);
			$con->execute();
			$data = [];
			while($row = $con->fetch(PDO::FETCH_ASSOC)){
				$data[] = $row;
			}
			// print_r($Users);
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename='.$table.csv);
			$output = fopen($directory, 'w');
			fputcsv($output, self::getTableColumns($db, $table));

			if (count($data) > 0) {
			    foreach ($data as $row) {
			        fputcsv($output, $row);
			    }
			}
		}

		public function columns($columns){
			if(is_array($columns))
				$this->columns = $columns;
			else
				$this->columns[] = $columns;
		}

		public function condition($condition){
			if(is_array($condition))
				$this->condition = $condition;
			else
				$this->condition[] = $condition;
		}

		public function read(){
			if(!count($this->condition))
				$condition = 1;
			else
				$condition = $this->condition;
			$columns = implode(', ', $this->columns);
			if($condition != 1){
				$condition = implode(' AND ', $this->condition);
			}
			$query = 'SELECT '.$columns.' FROM '.$this->table.' WHERE '.$condition;
			$con = $this->db->prepare($query);
			$con->execute();
			while($row = $con->fetch(PDO::FETCH_ASSOC)){
				$this->response[] = $row;
			}
			return $this->response;
		}
	} //class ends

?>