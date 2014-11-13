var sqlite3 = require('sqlite3');
var config = require('./config.json');
var fs = require('fs');
var path = require('path');

module.exports = {
	Init: function(callback) {
		var db_file = path.join(__dirname, config.database.file);
		var exists = fs.existsSync(db_file);
		db = new sqlite3.Database(db_file);
		this.db = db;

		callback();
	}
}