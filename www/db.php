<?

class sensor_db extends SQLite3
{
	function __construct()
	{
		$this->open('../db/sensors.db');
	}
}

?>
