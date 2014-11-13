var socketio = require('socket.io');
var server = require('./server.js');
var config = require('./config.json');
var datasource = require('./datasource.js');

var io;

function get_history(table)
{
	var sql = "SELECT strftime('%s',timestamp) as ts, value FROM " + table + " WHERE timestamp > datetime('now','localtime','-86400 seconds') ORDER BY timestamp DESC";

	datasource.db.all(sql,
		function(err, data){
			
			if(!err)
			{
				io.sockets.emit('message', {
						msg: 'history', 
						data: 
							{ 
								name: table, 
								data: data.map(
									function(row){ 
										return [row.ts, row.value];
									}) 
								}
							}
						);
			}
		});
}

function get_current(table)
{
	var sql = "SELECT value as value FROM " + table + " ORDER BY timestamp DESC LIMIT 1";


	datasource.db.each(sql,
		function(err, row){
			if(!err)
				io.sockets.emit('message', {msg: 'current', data: { name: table, data: row.value}});
		});
}

function get_states()
{
	var sql = "SELECT name, value FROM states";
	
	datasource.db.all(sql,
		function(err, data){
			if(!err)
			{
				io.sockets.emit('message', {
						msg: 'states',
						data:
							{
								name: 'states',
								data: data.map(
									function(row){
										return [row.name, row.value];
									})
								}
						}

						);
			}
		});
}
						

function send_data(socket)
{
	get_current('water_temperature');
	get_current('water_level');
	get_current('light_level');

	get_history('water_temperature');
	get_history('water_level');
	get_history('light_level');
	
	get_states();
}

function post_command(cmd)
{
	var sql = "INSERT INTO commands (command_string) VALUES ('" + cmd + "')";
	datasource.db.run(sql);
}
	

function parse_message(msg)
{
	if(msg.msg == 'set-light')
	{
		post_command('set-light ' + msg.data);
	}
}

datasource.Init(function(){
	io = socketio.listen(server).set('log level', 0);
	io.on('connection', function(socket){
			send_data(socket);
			socket.on('message', parse_message);
			socket.on('error', function(err){ console.log(err); });
	});

});

function updater()
{
	send_data(io.sockets);
	setTimeout(updater, 30000);
}

updater();
	