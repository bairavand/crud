<?php
	/**
     * @param Library for CRUD operation
     *********************************************************************************
     * @param v1.0 Beta
     *********************************************************************************
     * @param Copyright © 2018 PyRu Inc.
     */
	class DB{
		public static $tableColumns; //imploaded values
		public static $values_keys;
		public static $values_values;
		public static $newValues;
		public static $response = [];

		public static function getTableColumns($db, $table){
			$query = 'SHOW COLUMNS FROM '.$table;
			$con = $db->prepare($query);
			$con->execute();
			$getTableColumns = [];
			while($row = $con->fetch(PDO::FETCH_ASSOC)){
				$getTableColumns[] = ($row['Field']);
			}
			array_splice($getTableColumns, 0, 1);
			return $getTableColumns;
		}

		/*public static function create($db, $table, $values){
			$tableColumns = implode(', ', self::getTableColumns($db, $table));
			if(is_array($values)){
				$newValues = [];
				foreach($values as $value){
					$newValues[] = '\''.$value.'\'';
				}
				$newValues = implode(',', $newValues);
			}
			$query = 'INSERT INTO '.$table.'('.$tableColumns.') VALUES('.$newValues.')';
			$con = $db->prepare($query);
			$con1 = $con->execute();
			echo $con1 ? 'Values Added Successfully' : 'Failed to Add';
		}*/

		public static function create($db, $table, $values){
			if(self::isExist($db, $table, $values)['status'] != 1){
				$query = 'INSERT INTO '.$table.'('.self::$tableColumns.') VALUES('.self::$newValues.')';
				$con = $db->prepare($query);
				$con1 = $con->execute();
				if($con1)
					return self::$response = ['status'=>'1', 'message'=>'Values Added Successfully'];
				else
					return self::$response = ['status'=>'0', 'message'=>'Error Occured'];
			}else{
				return self::$response = ['status'=>'2', 'message'=>'Already Exist'];
			}
		}

		public static function update($db, $table, $values){
			if(self::isExist($db, $table, $values)){
				$len = sizeof($values);
				$assignValues = self::$values_keys[0].' = "'.self::$values_values[0].'"';
				$condition = self::$values_keys[0].' = "'.self::$values_values[0].'"'; 
				for($i=1;$i<$len;$i++){
					$assignValues .= ', '.self::$values_keys[$i].' = "'.self::$values_values[$i].'"';
					$condition .= ' OR '.self::$values_keys[$i].' = "'.self::$values_values[$i].'"';
				}
				$query = 'UPDATE '.$table.' SET '.$assignValues.' WHERE '.$condition;
				$con = $db->prepare($query);
				$con1 = $con->execute();
				return $con1 ? self::$response = ['status'=>'1', 'message'=>'Values Updated Successfully'] : self::$response = ['status'=>'0', 'message'=>'Error Occured'];
			}else{
				return self::$response = ['status'=>'2', 'message'=>'Not Found'];
			}
		}

		public static function isExist($db, $table, $values){
			if(is_array($values)){
				$newValues = [];
				$values_keys = array_keys($values);
				self::$values_keys = $values_keys;
				$tableColumns = implode(', ', $values_keys);
				self::$tableColumns = $tableColumns;
				$values_values = array_values($values);
				self::$values_values = $values_values;
				$newValues = '"'.implode('"," ' , $values_values).'"';
				self::$newValues = $newValues;
			}
			$query = 'SELECT '.$values_keys[0].' FROM '.$table.' WHERE '.$values_keys[0].' = \''.$values[$values_keys[0]].'\'';
			$con = $db->prepare($query);
			$con->execute();
			while($row = $con->fetch(PDO::FETCH_ASSOC)){
				$rowCount = $con->rowCount();
			}
			if(isset($rowCount)){
				$cond = $rowCount >= 1;
				return $cond ? self::$response = ['status'=>'1', 'message'=>'Already Exist'] : self::$response = ['status'=>'0', 'message'=>'Error Occured'];
			}else{
				return self::$response = ['status'=>'0', 'message'=>'Not Found(Exist)'];
			}
		}

		public static function delete($db, $table, $values){
			if(self::isExist($db, $table, $values)){
				$condition = self::$values_keys[0].' = "'.self::$values_values[0].'"';
				$query = 'DELETE FROM '.$table.' WHERE '.$condition;
				$con = $db->prepare($query);
				$response = $con->execute();
				if($response){
					return self::$response = ['status'=>'1', 'message'=>'Deleted successfully'];
				}else{
					return self::$response = ['status'=>'0', 'message'=>'Error Occured'];
				}
			}
		}
	} //class ends
?>