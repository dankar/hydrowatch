#!/usr/bin/python

import config
import serial
import sqlite3
import datetime
import time

db = sqlite3.connect('../db/sensors.db')

create_log_value_table = 'CREATE TABLE %s (id INTEGER PRIMARY KEY, value INT, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP)'
create_state_value_table = 'CREATE TABLE states (id INTEGER PRIMARY KEY, name TEXT, value INT)'
store_state_value = 'INSERT INTO states (name, value) VALUES ("%s", "%s")'
update_state_value = 'UPDATE states SET value = "%s" WHERE name = "%s"'
get_state_value_exists = 'SELECT COUNT(*) FROM states WHERE name = "%s"'
store_log_value = 'INSERT INTO %s (value) VALUES ("%s")'

def read_serial_line(device):
	recv = ""
	ch = ''

	while True:
		ch = device.read();
		if ch != '\r':
			recv += ch;
		if ch == '\n':
			return recv

def create_log_table(table):
	cur = db.cursor()
	cur.execute(create_log_value_table % table)
	db.commit()

def create_state_table():
	cur = db.cursor()
	cur.execute(create_state_value_table)
	db.commit()

def insert_state(name, value):
	cur = db.cursor()
	cur.execute(get_state_value_exists % name)
	row = cur.fetchone()
	if row[0] == 0:
		cur.execute(store_state_value % (name, value))
	else:
		cur.execute(update_state_value % (value, name))
	db.commit()

def insert_log_row(table, value):
	cur = db.cursor()
        cur.execute(store_log_value % (table, value))
        db.commit()

def store_log_row(data):
	(table, value) = tuple(data.split('='))

	try:
		insert_log_row(table, value)
	except sqlite3.OperationalError:
		create_log_table(table)
		insert_log_row(table, value)

def store_state(data):
	(name, value) = tuple(data.split('='))

	try:
		insert_state(name, value)
	except sqlite3.OperationalError:
		print "Woops, creating"
		create_state_table()
		insert_state(name, value)

def parse_report(report):
	for row in report:
		(command, data) = tuple(row.split(' '))
		if command == 'log_value':
			store_log_row(data)
		if command == 'state':
			store_state(data)

def get_report(device):
	done = False
	device.write('get-report\n') 
	report = []
	while not done:
		recv = read_serial_line(device)
		if recv == '\n':
			done = True
		else:
			report.append(recv)

	if len(report) > 0:
		parse_report(report)

dev = serial.Serial(config.serial_device, baudrate=115200, timeout=3.0)

next_update = datetime.datetime.now()
dev.flushInput()


# Wait for the Arduino to boot...
time.sleep(5)

while True:
	current_time = datetime.datetime.now()
	if current_time > next_update:
		get_report(dev);
		next_update = current_time + config.update_interval
	time.sleep(1)
