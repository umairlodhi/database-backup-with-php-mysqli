<?php
/*
*	change the sql connection
*	To tak the automatic backups of a specifc database you need to run a cron job.
*	Feel free to make changes according to your requirements.
*/
$link = mysqli_connect('localhost','root','');
?>
<!DOCTYPE html>
<html>
<head>
	<title>Database Backup Script by Umair</title>
	
</head>
<body>
<div class="container">
<form method="post">
<h1>Welcome to Get database backup manually with PHP & mysqli</h1>
  <span>Select your database</span>
  <select name="db_name">
	<?php
		//SHOW DATABASES list down all the databases in your server
	  	$result = mysqli_query($link, "SHOW DATABASES"); 
	  	while ($row = mysqli_fetch_array($result)) 
	  	{        
		    echo "<option>$row[0]</option>";        
		}
  	?>
  </select>
  <input type="submit" name="backup" value="Take Backup">
</div>
</form>
<?php

if(isset($_POST['backup']))
{
	$db_name = $_POST['db_name'];
	//calling the function with parame
	backup_tables($link,$db_name);
}
/* backup the db OR just a table */
function backup_tables($link,$name,$tables = '*')
{
	mysqli_select_db($link,$name);
	
	//get all of the tables
	if($tables == '*')
	{
		$tables = array();
		$result = mysqli_query($link, 'SHOW TABLES');
		while($row = mysqli_fetch_row($result))
		{
			$tables[] = $row[0];
		}
	}
	else
	{
		$tables = is_array($tables) ? $tables : explode(',',$tables);
	}
	
	//cycle through
	foreach($tables as $table)
	{
		$result = mysqli_query($link, 'SELECT * FROM '.$table);
		$num_fields = mysqli_num_fields($result);
		
		@$return.= 'DROP TABLE '.$table.';';
		$row2 = mysqli_fetch_row(mysqli_query($link, 'SHOW CREATE TABLE '.$table));
		$return.= "\n\n".$row2[1].";\n\n";
		
		for ($i = 0; $i < $num_fields; $i++) 
		{
			while($row = mysqli_fetch_row($result))
			{
				$return.= 'INSERT INTO '.$table.' VALUES(';
				for($j=0; $j < $num_fields; $j++) 
				{
					$row[$j] = addslashes($row[$j]);
					if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
					if ($j < ($num_fields-1)) { $return.= ','; }
				}
				$return.= ");\n";
			}
		}
		$return.="\n\n\n";
	}
	
	//save file
	$handle = fopen('db/'.$name.'-'.time().'-'.(md5(implode(',',$tables))).'.sql','w+');
	fwrite($handle,@$return);
	fclose($handle);
	if($handle){
		echo "<h3>Database bakeup taken Successfully</h3>";
	}
}
?>
</body>
</html>
