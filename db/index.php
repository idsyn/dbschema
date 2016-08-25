<?php
 	$dbname = 'test';
 	$conn = @new mysqli('127.0.0.1', 'root', 'root', $dbname, '3306');
 	$sql = "select table_name,table_comment from information_schema.tables where table_schema='$dbname' order by table_name";
 	$ispost = $_SERVER['REQUEST_METHOD'] == 'POST';
 	$posttablename = @$_POST['tablename'];
 	if (empty($posttablename)) {
 		$ispost = false;
 	} else if ($ispost && !empty($posttablename)) {
 		$sql = "select c.column_name as name,c.column_comment as `comment`,c.data_type as datatype,case when c.character_maximum_length is null then 0 else character_maximum_length end as length,case when c.is_nullable='YES' then 1 else 0 end as nullable,case when c.column_default is null then '' else column_default end as defaultvalue,case when c.column_key='PRI' then 1 else 0 end as isprimary, case when c.extra='auto_increment' then 1 else 0 end as autocreate,c.TABLE_NAME as tablename,t.table_comment as tablecomment from information_schema.COLUMNS c left join information_schema.tables t on c.table_name=t.table_name and c.table_schema=t.table_schema where c.TABLE_SCHEMA='$dbname' and c.TABLE_NAME in (select  distinct table_name from information_schema.COLUMNS where TABLE_SCHEMA='$dbname') and c.table_name='$posttablename';";
 	}
 	$result = @mysqli_query($conn, $sql);
 	$data = array();
	while ($tmp = @mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$data[] = $tmp;
	}
?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0,user-scalable=no">
	<title>数据库表结构</title>
	<style type="text/css">
		td,th { width:220px;text-align: left;border:none; }
		.mouseover { background-color: #ccc; }
	</style>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript">
		function mouseover(o) {
			$(o).addClass('mouseover');
		}
		function mouseout(o) {
			$(o).removeClass('mouseover');
		}
	</script>
</head>
<body>
	<form action="index.php" method="post">
		<div style="height: 35px;position:fixed;background: #fff;top:0px;margin: 0px;width: 100%;padding: 10px 0px 0px 0px;">
			<label for="tablename">tablename:</label>
			<input type="text" name="tablename" id="tablename" value="<?php echo $posttablename;?>">
			<input type="submit" value="select"/>
			<hr>
		</div>
	</form>
	<table rules=rows>
		<tr style="height: 35px;position: fixed;background: #fff;top: 44px;margin: 0px; width: 100%;padding: 10px 0px 0px 0px;">
			<?php if (!$ispost) { ?>
				<th>tablename</th>
				<th>comment</th>
			<?php } else { ?>
				<th>columnname</th>
				<th>comment</th>
				<th>datatype</th>
				<th>length</th>
				<th>nullable</th>
				<th>defaultvalue</th>
				<th>isprimary</th>
				<th>autocreate</th>
				<th>tablename</th>
				<th>tablecomment</th>
			<?php } ?>
		</tr>
		<tr style="height:80px;"></tr>
		<?php foreach ($data as $item) { ?>
			<tr onmouseover="mouseover(this)" onmouseout="mouseout(this)">
				<?php foreach ($item as $value) { ?>
					<td><?php echo $value; ?></td>
				<?php } ?>
			</tr>
		<?php } ?>
	</table>
</body>
</html>
