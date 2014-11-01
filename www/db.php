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
		$sql = "SELECT strftime('%s',timestamp), value FROM " .  $table . " WHERE timestamp > date('now','localtime','-" . $num . " seconds') ORDER BY timestamp DESC";
		$ret = $this->query($sql);
		$response = array();
		$first = true;
		while($row = $ret->fetchArray(SQLITE3_NUM))
		{
			$response[] = $row;
		}

		return json_encode($response);
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
