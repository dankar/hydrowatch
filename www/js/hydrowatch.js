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

function set_buttons(arr, value)
{
	for(var i = 0; i < arr.length; i++)
        {
                if(i == value)
                {
                        arr[i].attr("class", "button_on");
                }
                else
                {
                        arr[i].attr("class", "button_off");
                }
        }
}

function update_light(value)
{
	var buttons = [$('#light_off'), $('#light_on'), $('#light_auto')];
	set_buttons(buttons, value);
}

function update_pump(value)
{
	var buttons = [$('#pump_off'), $('#pump_on')];
	set_buttons(buttons, value);
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
