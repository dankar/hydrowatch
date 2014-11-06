<?
include('db.php');

$db = new sensor_db();

if(!$db)
{
	echo $db->lastErrorMsg();
}

?><!DOCTYPE html>
<html>
	<head>
		<title>Hydrowatch</title>
		<script src="js/grapho.js"></script>
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<script>
			function generate_water_graph(div_name)
			{
				var graphs = new Grapho({place: div_name});
		                graphs.addDataSet({
		                        type:'line',
		                        strokeStyle: '#F0F066',
		                        y: {
		                                scaleStyle: '#F0F066',
		                                showGridLines: true,
		                                labels: true,
		                                name: "Temperature (C)",
		                                nameFont: '13px Droid Sans', 
										min: 17,
										max: 25
		                        },
		                        x: {
	                        		showGridLines: true,
	                        		gridStyle: '#221010',
		                                labels: true,
		                                labelRotation: 45,
		                                labelFormat: Grapho.formats.time
		                        },
		                        data: <? echo $db->get_day("water_temperature"); ?>
		                });
				graphs.addDataSet({
					type:'line',
					strokeStyle: '#6666F0',
					y: {
			                        axis: 2,
						scaleStyle: '#6666F0',
						showScale: true,
						name: "Level",
						nameFont: '13px Droid Sans',
						labels: true,
						max: 800,
						min: 200
					},
							data: <? echo $db->get_day("water_level"); ?>
				});
		
			}
			function generate_light_graph(div_name)
			{
				var graphs = new Grapho({place: div_name});
                                graphs.addDataSet({
                                        type:'line',
                                        strokeStyle: '#F0F066',
                                        y: {
                                                scaleStyle: '#F0F066',
                                                showGridLines: true,
                                                labels: true,
                                                name: "Light",
                                                nameFont: '13px Droid Sans',
                                                //                              min: 17,
                                                //                              max: 25
                                        },
                                        x: {
                                                showGridLines: true,
                                                gridStyle: '#221010',
                                                labels: true,
                                                labelRotation: 45,
                                                labelFormat: Grapho.formats.time
                                        },
                                        data: <? echo $db->get_day("light_level"); ?>
                                });
			}
		</script>
	</head>
	<body>

		<div class="panel_container">
			<div class="current_container">
				<div class="left_panel">
					<div class="current_text">
						<p><? echo $db->get_current('water_level'); ?></p>
					</div>
				</div>
				<div class="right_panel">
					<div class="current_text">
						<p><? echo $db->get_current('water_temperature'); ?></p>
					</div>
				</div>
			</div>
			<div id="water_graph" class="graph">
				<script>generate_water_graph('water_graph');</script>
			</div>
		</div>

		<div class="panel_container">
			<div class="current_container">
				<div class="left_panel">
					<div class="current_text">
						<p><? echo $db->get_current('light_level'); ?></p>
					</div>
				</div>
				<div class="right_panel">
					<div class="current_text">
						<p></p>
					</div>
				</div>
			</div>
			<div id="light_graph" class="graph">
				<script>generate_light_graph('light_graph');</script>
			</div>
		</div>
	</body>
</html>
