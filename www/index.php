<?
include('db.php');
$db = new sensor_db();
if(!$db)
{
	echo $db->lastErrorMsg();
} else {
 	echo "Opened!";
}
?>
