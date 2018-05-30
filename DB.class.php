<?php
	/**
     * Library for CRUD operation
     * @param 
     */
	class DB{

		public static function getTableColumns($db, $table){
			$query = 'SHOW COLUMNS FROM '.$table;
			$con = $db->prepare($query);
			$stmt = $con->execute();
			$tableColumns = [];
			while($row = $con->fetch(PDO::FETCH_ASSOC)){
				$tableColumns[] = ($row['Field']);
			}
			array_splice($tableColumns, 0, 1);
			return $tableColumns;
		}

		public static function create($db, $table, $values){
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
		}

	} //class ends
?>