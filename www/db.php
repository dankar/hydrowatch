<?php
class sensor_db extends PDO
{
	function __construct()
	{
		parent::__construct('sqlite:../db/sensors.db');
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
			$response = $this->get_cache($num, $table);
		}
		if ($response == "") {
			// TODO: better query
			$sql = $this->prepare("SELECT strftime('%s',timestamp) as ts, value FROM " .  $table . " WHERE timestamp > date('now','localtime','-" . $num . " seconds') ORDER BY timestamp DESC");
			$sql->execute();
			$response = array();
			while($row = $sql->fetch(PDO::FETCH_NUM)){
				$response[] = [intval($row[0]), floatval($row[1])];
			}
			$response = json_encode($response);
			$this->save_cache($num, $table, $response);
		}
		return $response;
	}

	function get_states()
	{
		$response = "";

		$sql = $this->prepare("SELECT name, value FROM states");
		$sql->execute();

		while($row = $sql->fetch()){
			$response .= "<p>" . $row[0] . " = " . $row[1] . "</p>";
		}

		return $response;
	}

	function post_command($cmd)
	{
		$sql = $this->prepare("INSERT INTO commands (command_string) VALUES (:command)");
		$sql->bindParam(':command', $cmd);
		$sql->execute();

		print $this->errorInfo()[2];
	}

	function get_day($table)
	{
		return $this->get_data(86400, $table);
	}

	function get_hours($table)
	{
		return $this->get_data(3600, $table);
	}

	function get_current($table)
	{
		$sql = $this->prepare("SELECT value FROM " . $table . " ORDER BY timestamp DESC LIMIT 1");
		$sql->execute();
		$row = $sql->fetch();
		return $row[0];
	}
}

?>
