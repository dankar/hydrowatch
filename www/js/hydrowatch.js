var iosocket;

$(function(){
	iosocket = io.connect();
	iosocket.on('connect', function(){
		iosocket.on('message', function(message){

		if(message.msg == 'states')
		{
			for(var key in message.data.data)
			{
				var name = message.data.data[key][0];
				var data = message.data.data[key][1];
				if(name == 'current_light')
				{
					var color = Math.floor(data * 255);
					$('#light_status').css({"background-color": "rgba(" + [color, color, color, 255] + ")"});
				}
				if(name == 'light_setting')
				{
					update_light(data);
				}
				if(name == 'motor_setting')
				{
					update_pump(data);
				}
			}
		}
		if(message.msg == 'history')
		{
			var graph = "";
			if(message.data.name == 'water_temperature' || message.data.name == 'water_level')
				graph = 'water_graph';
			else if(message.data.name == 'light_level')
				graph = 'light_graph';
			else if(message.data.name == 'tds_level')
				graph = 'tds_graph';
			update_graph(graph, message.data.name, message.data.data);
		}
		if(message.msg == 'current')
		{
			$('#' + message.data.name).html(message.data.data);
		}
		});
	});
});
function update_light(value)
{
	var buttons = [$('#light_off'), $('#light_on'), $('#light_auto')];
	$('#light_off').css({"background-color":"#000000"});
	$('#light_on').css({"background-color":"#000000"});
	$('#light_auto').css({"background-color":"#000000"});
	
	buttons[value].css({"background-color":"#ffffff"});
}

function update_pump(value)
{
	var buttons = [$('#pump_off'), $('#pump_on')];
	$('#pump_off').css({"background-color":"#000000"});
	$('#pump_on').css({"background-color":"#000000"});

	buttons[value].css({"background-color":"#ffffff"});
}

function set_light(value)
{
	iosocket.emit('message', {msg: 'set-light', data: value});
	update_light(value);
}

function set_motor(value)
{
	iosocket.emit('message', {msg: 'set-motor', data: value});
	update_pump(value);
}
