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
		$sql = "SELECT * FROM (SELECT value, timestamp FROM " .  $table . " ORDER BY timestamp DESC LIMIT " . $num . ") sub ORDER BY timestamp ASC";
		$ret = $this->query($sql);
		$response = "";
		$first = true;
		while($row = $ret->fetchArray(SQLITE3_ASSOC))
		{
			$data = "[" . strtotime($row["timestamp"]) . ", " . $row["value"] . "]";
			if($first)
			{
				$response = $response . $data;
				$first = false;
			}
			else
			{
				$response = $response . ", " . $data;
			}
		}

		return $response;
	}

	function get_day($table)
	{
		return $this->get_data(8640, $table);
	}

	function get_hours($table)
	{
		return $this->get_data(360, $table);
	}
}

?>
