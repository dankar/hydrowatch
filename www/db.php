<?php
class sensor_db extends SQLite3
{
	function __construct()
	{
		$this->open('../db/sensors.db');
	}

	function get_cache($num, $table) {
		$cache_file = "cache/$num$table.cache";
		$cache_life = 30;
		if (!file_exists($cache_file) or (time() - filemtime($cache_file) >= $cache_life)){
			return "";
		} else {
			return file_get_contents($cache_file);
		}
	}

	function save_cache($num, $table, $response) {
		file_put_contents("cache/$num$table.cache",$response);
	}

	function get_data($num, $table, $use_cache = TRUE)
	{
		$response = "";
		if ($use_cache) {
			$response = get_cache($num, $table);
		}
		if ($response == "") {
			// TODO: better query
			$sql = "SELECT 0+strftime('%s',timestamp), value FROM " .  $table . " WHERE timestamp > date('now','localtime','-" . $num . " seconds') ORDER BY timestamp DESC";
			$ret = $this->query($sql);
			$response = array();
			while($row = $ret->fetchArray(SQLITE3_NUM)){ 
				$response[] = $row;
			}
			$response = json_encode($response);
			save_cache($num, $table, $response);
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
