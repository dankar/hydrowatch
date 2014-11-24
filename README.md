This project logs and displays data from an arduino with connected sensors.

* sudo apt-get install arduino-mk nodejs npm
* Modify arduino/sensors/Makefile accordingly before building. See commands.cpp for adding/removing sensors
* Upload to arduino
* pip install pyserial
* pip install pysqlite (or whatever it's called)
* npm install sqlite3
* npm install socket.io
* Start logger/logger.py. It will create the database according to the data reported from the arduino
* node hydrowatch.js