var sqlite3 = require('sqlite3'),
	config = require('./config.json'),
	datasource = require('./datasource.js'),
	util = require('util'),
	SerialPort = require("serialport").SerialPort;

module.exports = {
	Init: function (callback) {

		this.queries = Array();
		this.queries['create_log_value_table'] = 'CREATE TABLE %s (id INTEGER PRIMARY KEY, value INT, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP)';
		this.queries['create_state_value_table'] = 'CREATE TABLE states (id INTEGER PRIMARY KEY, name TEXT, value INT)';
		this.queries['store_state_value'] = 'INSERT INTO states (name, value) VALUES ("%s", "%s")';
		this.queries['update_state_value'] =  'UPDATE states SET value = "%s" WHERE name = "%s"';
		this.queries['remove_state_value'] = 'DELETE FROM states WHERE name = "%s"';
		this.queries['store_log_value'] = 'INSERT INTO %s (value) VALUES ("%s")';
		this.queries['create_command_table_query'] = 'CREATE TABLE commands (command_string TEXT)';
		this.queries['get_commands_query'] = 'SELECT * FROM commands';
		this.queries['clear_command_table'] = 'DELETE FROM commands';

		this.next_update = new Date().getTime();

		// Set up serial port
		this.device = new SerialPort(config.logger.serial_device, { baudrate: 115200 });

		// Wait five seconds to make sure the arduino is booted
  		this.device.on("open", function () {
			console.log('Serial port opened');
	
			// Pass incoming data to this.readSerialLine
			this.device.on('data', function(data) {
				this.readSerialLine(data);
			}.bind(this));

			// Now when the serial port is up and running, we can start the recursion towards infinity
			this.doEvents();

		}.bind(this));

		callback && callback();
	},
	doEvents: function () {

		current_time = new Date().getTime();

		if (current_time > this.next_update) {
			this.updateLight();
			this.getReport();
			this.next_update = current_time + config.logger.update_interval;
		} else {
			this.doCommands();
		}

		// Wait one second and recurse into infinity
		setTimeout(this.doEvents.bind(this), 1000, this);
	},
	writeSerialLine: function (data, callback) {
		this.device.write(data, function(err, results) {
			if (err) {
				console.log("Serial write error: " + err)
			}
			callback && callback();
		});
	},
	readSerialLine: function (data, callback) {
		this.parseReport(data, callback);
	},
	createLogTable: function (table, callback) {
		datasource.db.run(util.format(this.queries['create_log_value_table'],table), function() {
			callback && callback();
		});
	},
	createStateTable: function (callback) {
		datasource.db.run(this.queries['create_state_value_table'], function () {
			callback && callback();
		});
		
	},
	createCommandTable: function (callback) {
		datasource.db.run(this.queries['create_command_table_query'], function() {
			callback && callback();	
		});
	},
	insertState: function (name, value, callback) {
		datasource.db.run(util.format(this.queries['remove_state_value'],name),function () {
			datasource.db.run(util.format(this.queries['store_state_value'],name,value),function () {
				callback && callback();
			});
		});
	},
	insertLogRow: function (table, value, callback) {
		datasource.db.run(util.format(this.queries['store_log_value'],table,value), function() {
			callback && callback();	
		});
	},
	storeLogRow: function (data, callback) {
		var result = data.split("=");
		// We might need to use err callbacks instead of try {} catch {} here, not sure anything is thrown
		try {
			insertLogRow(result[0], result[1], callback);
		} catch (e) {
			createLogTable(table);
			insertLogRow(result[0], result[1], callback);
		}
	},
	storeState: function (data,callback) {
		var result = data.split("=");
		// We might need to use err callbacks instead of try {} catch {} here, not sure anything is thrown
		try {
			this.insertState(result[0], result[1], callback);
		} catch (e) {
			this.createStateTable(table);
			this.insertState(result[0], result[1], callback);
		}
	},
	parseReport: function (report, callback) {
		for (row in report) {
			var result = row.split(' ');
			if (result[0] == 'log_value') {
				this.storeLogRow(result[1],callback);
			}
			if (result[0] == 'state') {
				this.storeState(result[1],callback);
			}
		}
	},
	getReport: function (callback) {
		this.writeSerialLine('get-report\n',callback);
	},
	getStates: function (callback) {
		this.writeSerialLine('get-states\n',callback);
	},
	getCommands: function (callback,err) {
		datasource.db.all(this.queries['get_commands_query'],
			function(err, data){
				if(!err)
				{
					callback(data);
				} else {
					err();
				}
			}
		);
	},
	doCommandsCallback: function (commands, callback) {


		var anythingDone = false;

		for (command in commands) {
			var txVal = commands[command].command_string;
			this.writeSerialLine(txVal,function() {
				this.writeSerialLine('\n');
			}.bind(this));
			anything_done = true; 
		}

		datasource.db.run(this.queries['clear_command_table']);

		if (anythingDone) {
			this.getStates(device,callback());
		} else {
			callback && callback();
		}

	},
	doCommands: function (device, callback) {
		this.getCommands(
			function(data) {
				this.doCommandsCallback(data);
			}.bind(this),
			function(err) {
				createCommandTable(function() {
					getCommands(function(data) {
						this.doCommandsCallback(data);
					}.bind(this));
				}.bind(this));
			}.bind(this)
		);
	},
	updateLight: function (device, callback) {
		var day_secs = 86400.0,
			hour_secs = 3600.0,
			now = new Date().getTime(),
			midnight = new Date().setHours(0,0,0,0),
			delta = now - midnight,
			light = 0.0,
			current_hour = delta / 3600.0 - 12;

		if (Math.abs(current_hour) < config.logger.light_hours / 2) {
			light = config.logger.light_max;
		} else if (Math.abs(current_hour) < (config.logger.light_hours/2) + config.logger.light_fade_hours) {
			sign = current_hour / current_hour;
			fade_amount = (Math.abs(current_hour) - (config.logger.light_hours/2)) * sign;
			fade_amount = fade_amount / config.logger.light_fade_hours;
			light = (Math.cos(fade_amount * Math.PI) + 1.0) / 2.0 * config.logger.light_max;
		} else {
			light = 0.0
		}
		this.writeSerialLine("set-light-value " + light + "\n");
		console.log("Setting light to " + light,(Math.abs(current_hour), (config.logger.light_hours/2) + config.logger.light_fade_hours) );
	}
};
