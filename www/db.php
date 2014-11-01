<?

class sensor_db extends SQLite3
{
	function __construct()
	{
		$this->open('../db/sensors.db');
	}

	function get_data($num, $table)
	{
		// TODO: better query
		$sql = "SELECT (strftime('%s',timestamp)/60)*60, AVG(value) FROM " .  $table . " WHERE timestamp > date('now','localtime','-" . $num . " seconds') group by (strftime('%s',timestamp)/60)*60 ORDER BY (strftime('%s',timestamp)/60)*60 DESC";
		$ret = $this->query($sql);
		$response = array();
		while($row = $ret->fetchArray(SQLITE3_NUM)){ 
			$response[] = $row;
		}

		return $response;
	}

	function get_day($table)
	{
		return $this->get_data(86400, $table);
	}

	function get_hours($table)
	{
		return $this->get_data(3600, $table);
	}
}

?>
