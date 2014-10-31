<?
include('db.php');

$db = new sensor_db();

if(!$db)
{
	echo $db->lastErrorMsg();
}

?>
<html>
	<head>
		<title>Hydrowatch</title>
		<script src="js/grapho.js"></script>
	</head>
	<body style="background-color: #252420; color: #CACACA; font-size: 12px; font-family: 'Droid Sans';">
		<div id="day_level" style="margin-top: 50px; margin-left: 50px; width: 500; height: 300;">
			<span><h2>Water level</h2></span>
			<script>
				var day_level_graph = new Grapho({place: 'day_level'});
				day_level_graph.addDataset({
					type:'line',
					strokeStyle: '#F0F066',
					y: {
						scaleStyle: '#F0F066',
						name: "Level",
						showGridLines: true,
						showLabels: true,
						max: 1024,
						min: 100
					},
					x: {
						showLabels: true,
						labelRotation: 45,
						labelFormat: Grapho.formats.time
					},
					data: [<? echo $db->get_day("water_level"); ?>]
				});
			</script>
		</div>
		<div id="day_temperature" style="margin-top: 50px; margin-left: 50px; width: 500; height: 300;">
                        <span><h2>Water temperature</h2></span>
                        <script>
                                var day_temperature_graph = new Grapho({place: 'day_temperature'});
                                day_temperature_graph.addDataset({
                                        type:'line',
                                        strokeStyle: '#F0F066',
                                        y: {
                                                scaleStyle: '#F0F066',
                                                showGridLines: true,
                                                showLabels: true,
                                                name: "Centigrades",
						min: 15,
						max: 50
                                        },
                                        x: {
                                                showLabels: true,
                                                labelRotation: 45,
                                                labelFormat: Grapho.formats.time
                                        },
                                        data: [<? echo $db->get_day("water_temperature"); ?>]
                                });
                        </script>
                </div>
	</body>
</html>
