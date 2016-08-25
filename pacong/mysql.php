<?php 

function select_db($sql) {
	$result = array();
	$conn = connect_db();

	try {
		$result1 = mysqli_query($conn, $sql);
		while ($tmp = mysqli_fetch_array($result1, MYSQLI_ASSOC)) {
			$result[] = $tmp;
		}
	} catch (Exception $e) {
		die(mysqli_error($conn));
	}
	mysqli_close($conn);
 	return $result;
}

function insert_db($sql) { 
	$conn = connect_db();

	try {
		$arr_sql = explode(';', $sql);
		mysqli_autocommit($conn, false);
		foreach ($arr_sql as $str_sql) {
			if ($str_sql) {
				mysqli_query($conn, $str_sql);
			}
		}
		mysqli_commit($conn);
	} catch (Exception $e) {
		mysqli_rollback($conn);
		die(mysqli_error($conn).'^^^^^^'.$e);
	}

	mysqli_close($conn);
}

function init_db($dbname, $arr_table_sql) {
	$arr_table = array_keys($arr_table_sql);
	$table_name = implode("','", $arr_table);
	$select_sql = "select `table_name` from `information_schema`.`tables` where `table_schema`='{$dbname}' and `table_name` in ('{$table_name}');";
	
	$arr_table_db = select_db($select_sql);
	$arr_table_db1 = array();
	foreach ($arr_table_db as $table_db) {
		$arr_table_db1[] = $table_db['table_name'];
	}

	$arr_table_diff = array_diff($arr_table, $arr_table_db1);

	$arr_sql = array();
	foreach ($arr_table_diff as $table_diff) {
		$arr_sql[] = $arr_table_sql[$table_diff];
	}

	if (count($arr_sql) > 0) {
		$insert_sql = implode(';', $arr_sql);
		insert_db($insert_sql);
	}
}

function connect_db() {
	global $config;

	$conn = mysqli_connect($config['db']['host'], $config['db']['user'], $config['db']['password'], $config['db']['dbname'], $config['db']['port']);
	if (mysqli_connect_errno($conn)) {
		die('连接失败,'.mysqli_connect_error());
	}
	return $conn;
}

?>