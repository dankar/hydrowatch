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
	</head>
	<body style="background-color: #242526; color: #CACACA; font-size: 12px; font-family: 'Droid Sans';">
		<div id="day_level" style="margin-top: 50px; margin-left: 50px; width: 600px; height: 400px;">
			<span><h2>Water level</h2></span>
			<script>
				var graphs = new Grapho({place: 'day_level'});
                graphs.addDataSet({
                        type:'line',
                        strokeStyle: '#F0F066',
                        y: {
                                scaleStyle: '#F0F066',
                                showGridLines: true,
                                labels: true,
                                name: "Centigrades",
                                nameFont: '13px Droid Sans', 
								min: 15,
								max: 50
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
						max: 1024,
						min: 100
					},
					data: <? echo $db->get_day("water_level"); ?>
				});
			</script>
		</div>
	</body>
</html>
