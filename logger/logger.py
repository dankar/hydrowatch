#!/usr/bin/python

import config
import serial
import sqlite3
import datetime
import time


def read_serial_line(device):
	recv = ""
	ch = ''

	while True:
		ch = device.read();
		recv += ch;
		if ch == '\n' or ch == '\r':
			return recv

dev = serial.Serial(config.serial_device, baudrate=115200, timeout=3.0)

next_update = datetime.datetime.now()
dev.flushInput()

db = sqlite3.connect('../db/sensors.db')

# Wait for the Arduino to boot...
time.sleep(5)

while True:
	current_time = datetime.datetime.now()
	if current_time > next_update:
		next_update = current_time + config.update_interval		
		done = False
		# Write empty line to trigger report
		dev.write("\n") 
		while not done:
			recv = read_serial_line(dev)
			print recv
			if recv == "\n":
				done = True
			else:
				data = recv.split(':')
				if len(data) == 2:
					cur = db.cursor()
					cur.execute("INSERT INTO %s (value) VALUES ('%s')" % (data[0], data[1]))
					db.commit()
	time.sleep(1)
					

					
			
