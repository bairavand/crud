<?php
	require_once('init.php');
	require_once('DB.class.php');

	/*GET SINGLE BY DIRECT QUERY STARTS*/
	$obj = new DB();
	$obj->connect($db);
	$res = $obj->getSingle('select * from students');
	print_r($res);
	/*GET SINGLE BY DIRECT QUERY ENDS*/

	/*GET ALL RECORDS BY DIRECT QUERY STARTS*/
	$obj = new DB();
	$obj->connect($db);
	$res = $obj->get('select * from students');
	print_r($res);
	/*GET ALL RECORDS BY DIRECT QUERY ENDS*/

	/*INSERT BY DIRECT QUERY STARTS*/
	$obj = new DB();
	$obj->connect($db);
	$res = $obj->put("INSERT INTO students (student_name) VALUES('test_name')");
	print_r($res);
	/*INSERT BY DIRECT QUERY ENDS*/

	/*READ DATA STARTS*/
	$obj = new DB();
	$obj->connect($db);
	$obj->table('students');
	$obj->columns(array('student_name', 'student_country'));
	$res = $obj->condition('student_id', '=', '1');
	print_r($res);
	/*READ DATA ENDS*/
	
	/*INSERT STARTS*/
	$data = array('student_name'=>'bairavan', 'student_country'=>'India');
	$obj = new DB();
	$obj->connect($db);
	$obj->table('students');
	$obj->data($data);
	$res = $obj->create();
	print_r($res);
	/*INSERT ENDS*/

	/*UPDATE STARTS*/
	$data = array('student_name'=>'bairavan');
	$obj = new DB();
	$obj->connect($db);
	$obj->table('students');
	$obj->data($data);
	$obj->condition('student_id', '=', '1');
	$res = $obj->update();
	print_r($res);
	/*UPDATE ENDS*/

	/*DELETE STARTS*/
	/*DELETE ENDS*/
?>
