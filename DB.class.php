<?php
	/**
     * @package Library for CRUD operation
     *********************************************************************************
     * @param v2.5 Beta
     *********************************************************************************
     * @copyright © 2018 PyRu Inc.
     */
	class DB{
		private static $tableColumns; //splice first column i.e primary field
		private $db;
		private $table;
		private $data;
		private $encrypt 	= 0;
		private $encryptKey = 'DEFAULT';
		private $isEncryptColumns = 0;
		private $encryptColumns = [];
		private $dataKeys;
		private $dataValues;
		private $newValues;
		private $columns = [];
		private $condition = [];
		private $logicOperator = 'AND';
		// private $response= [];

		public function connect($db){
			$this->db = $db;
		}

		public function table($table){
			$this->table = $table;
		}

		public static function getTableColumns($table){
			try {
				$query 			 = 'SHOW COLUMNS FROM '.$table;
				$con 			 = $this->db->prepare($query);
				$con->execute();
				$getTableColumns = [];
				while($row = $con->fetch(PDO::FETCH_ASSOC)){
					$getTableColumns[] = ($row['Field']);
				}
				array_splice($getTableColumns, 0, 1);
				return self::$tableColumns = $getTableColumns;
			} catch(Exception $e){
				echo '<b>Query Error:</b> '.$query.'<br/>';
				echo $e->getMessage();
			}
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
			try {
				if($this->encrypt){
					$dataKeys   = implode(', ', $this->dataKeys); //comma separated keys
					$dataValues = "AES_ENCRYPT('".implode("', '$this->encryptKey'), AES_ENCRYPT('", $this->dataValues)."', '$this->encryptKey')"; //comma and quoted values
					$query 		= 'INSERT INTO '.$this->table.'('.$dataKeys.') VALUES('.$dataValues.')';
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
				}

				else{
					$dataKeys   = implode(', ', $this->dataKeys); //comma separated keys
					$dataValues = '\''.implode('\', \'', $this->dataValues).'\''; //comma and quoted values
					$query 		= 'INSERT INTO '.$this->table.'('.$dataKeys.') VALUES('.$dataValues.')';
				}
				$this->put($query);
			} catch(Exception $e){
				echo '<b>Query Error:</b> '.$query.'<br/>';
				echo $e->getMessage();
			}
		}

		public function update(){
			try {
				$len 			= sizeof($this->data);
				$assignValues   = $this->dataKeys[0].' = "'.$this->dataValues[0].'"';
				// $condition 		= $this->dataKeys[0].' = "'.$this->dataValues[0].'"';
				if(!count($this->condition))
					$condition = 1;
				if($this->condition != 1)
					$condition = implode(' '.$this->logicOperator.' ', $this->condition);
				for($i=1;$i<$len;$i++){
					$assignValues .= ', '.$this->dataKeys[$i].' = "'.$this->dataValues[$i].'"';
					// $condition 	  .= ' OR '.$this->dataKeys[$i].' = "'.$this->dataValues[$i].'"';
				}
				$query = 'UPDATE '.$this->table.' SET '.$assignValues.' WHERE '.$condition;
				$this->put($query);
				/*$con   = $this->db->prepare($query);
				$con1  = $con->execute();
				return $con1 ? $response = ['result'=>'1'] : $response = ['result'=>'0'];*/
			} catch(Exception $e){
				echo '<b>Query Error:</b> '.$query.'<br/>';
				echo $e->getMessage();
			}
		}

		public function isExist(){
			$columns = implode(', ', $this->columns);
			if(!count($this->condition))
				$condition = 1;
			if($this->condition != 1)
				$condition = implode(' '.$this->logicOperator.' ', $this->condition);
			$query 		= 'SELECT '.$columns.' FROM '.$this->table.' WHERE  '.$condition;
			try{
				$con = $this->db->prepare($query);
				$con->execute();
				$rowCount = $con->rowCount();
				if($rowCount >= 1)
					return 1;
				else
					return 0;
			} catch(Exception $e){
				echo '<b>Query Error</b>: '.$query.'<br/>';
				echo $e->getMessage();
			}
		}

		public function delete($db, $table, $values){
			try {
				if(self::isExist($db, $table, $values)){
					$condition = self::$values_keys[0].' = "'.self::$values_values[0].'"';
					$query 	= 'DELETE FROM '.$table.' WHERE '.$condition;
					$con = $db->prepare($query);
					$output = $con->execute();
					return $output ? $response = ['result'=>'1'] : $response = ['result'=>'0'];
				}
			} catch(Exception $e){
				echo '<b>Query Error:</b> '.$query.'<br/>';
				echo $e->getMessage();
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

		public function columns($columns = '*'){
			if(is_array($columns))
				$this->columns = $columns;
			else
				$this->columns[] = $columns;
		}

		public function condition($column, $operator = '', $value){
			$this->condition[] = $column.' '.$operator.' \''.$value.'\'';	
		}

		public function conditionType($logicOperator){
			$this->logicOperator = $logicOperator;
		}

		public function read(){
			try {
				$columns = implode(', ', $this->columns);
				if(!count($this->condition))
					$condition = 1;
				if($this->condition != 1)
					$condition = implode(' '.$this->logicOperator.' ', $this->condition);
				$query = 'SELECT '.$columns.' FROM '.$this->table.' WHERE '.$condition;
				return $this->get($query);
			} catch(Exception $e){
				echo '<b>Query Error:</b> '.$query.'<br/>';
				echo $e->getMessage();
			}
		}
		public function get($query){
			try{
				$con = $this->db->prepare($query);
				$con->execute();
				// print_r($con->errorInfo());
				$response = [];
				while($row = $con->fetch(PDO::FETCH_ASSOC)){
					$response[] = $row;
				}
				return $response;
			}catch(Exception $e){
				echo '<b>Query Error:</b> '.$query.'<br/>';
				echo $e->getMessage();
			}
		}

		public function getSingle($query){
			try{
				$con = $this->db->prepare($query.' LIMIT 1');
				$con->execute();
				// print_r($con->errorInfo());
				while($row = $con->fetch(PDO::FETCH_ASSOC))
				return $row;
			}catch(Exception $e){
				echo '<b>Query Error:</b> '.$query.'<br/>';
				echo $e->getMessage();
			}
		}

		public function put($query){
			try{
				$con 		= $this->db->prepare($query);
				$con1 		= $con->execute();
				if($con1)
					return $response = ['result'=>'1'];
				else
					return $response = ['result'=>'0'];
			}catch(Exception $e){
				echo '<b>Query Error:</b> '.$query.'<br/>';
				echo $e->getMessage();
			}
		}
	} //class ends

?>