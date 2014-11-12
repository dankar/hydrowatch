<?
include('db.php');

$db = new sensor_db();

if(!$db)
{
	echo $db->lastErrorMsg();
}

if($_GET)
{
	if($_GET['cmd'])
	{
		$db->post_command(urldecode($_GET['cmd']));
	}
	exit(0);
}

?>

<!DOCTYPE html>
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
										max: 27
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
						min: 0
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

			function load_url_callback(url, callback) {
				var xhr;

				if(typeof XMLHttpRequest !== 'undefined') xhr = new XMLHttpRequest();
				else {
					var versions = ["MSXML2.XmlHttp.5.0", 
						 	"MSXML2.XmlHttp.4.0",
						 	"MSXML2.XmlHttp.3.0", 
						 	"MSXML2.XmlHttp.2.0",
						 	"Microsoft.XmlHttp"]

					for(var i = 0, len = versions.length; i < len; i++) {
					try {
						xhr = new ActiveXObject(versions[i]);
						break;
					}
						catch(e){}
					} // end for
				}

				xhr.onreadystatechange = ensureReadiness;

				function ensureReadiness() {
					if(xhr.readyState < 4) {
						return;
					}

					if(xhr.status !== 200) {
						return;
					}

					// all is well
					if(xhr.readyState === 4) {
						callback(xhr);
					}
				}

				xhr.open('GET', url, true);
				xhr.send('');
			}
			function load_url(url)
			{
				load_url_callback(url, function(xhr){});
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
		<div id="states">
			<h2>States</h2>
			<? echo $db->get_states(); ?>
			<form action="#">
                                <label><input type="radio" name="light" onclick="load_url('?cmd=set-light 1');">Light on</label>
                                <label><input type="radio" name="light" onclick="load_url('?cmd=set-light 0');">Light off</label>
				<label><input type="radio" name="light" onclick="load_url('?cmd=set-light 2');">Light auto</label>
			</form>
		</div>
	</body>
</html>
