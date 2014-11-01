#!/usr/bin/python

import config
import serial
import sqlite3
import datetime


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

# This is a bit ugly. It will wait ten seconds to clear device buffers and then start reading, assuming that valid data
# starts after a double newline
while True:
	recv = read_serial_line(dev)
	if recv == "\n": # here comes a new packet!
		current_time = datetime.datetime.now()
		if current_time > next_update:
			next_update = current_time + config.update_interval
			done = False
			while not done:
				recv = read_serial_line(dev)
				if recv == "\n":
					done = True
				else:
					data = recv.split(':')
					if len(data) == 2:
						cur = db.cursor()
						if data[0] == 'temp':
							cur.execute("INSERT INTO water_temperature (value) VALUES ('%s')" % data[1])
						elif data[0] == 'level':
							cur.execute("INSERT INTO water_level (value) VALUES ('%s')" % data[1])
						db.commit()

					

					
			
