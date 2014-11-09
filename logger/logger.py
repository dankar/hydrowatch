#!/usr/bin/python

import config
import serial
import sqlite3
import datetime
import time
import math

db = sqlite3.connect('../db/sensors.db')

create_log_value_table = 'CREATE TABLE %s (id INTEGER PRIMARY KEY, value INT, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP)'
create_state_value_table = 'CREATE TABLE states (id INTEGER PRIMARY KEY, name TEXT, value INT)'

store_state_value = 'INSERT INTO states (name, value) VALUES ("%s", "%s")'
update_state_value = 'UPDATE states SET value = "%s" WHERE name = "%s"'
get_state_value_exists = 'SELECT COUNT(*) FROM states WHERE name = "%s"'
store_log_value = 'INSERT INTO %s (value) VALUES ("%s")'

create_command_table_query = 'CREATE TABLE commands (command_string TEXT)'
get_commands_query = 'SELECT * FROM commands'
clear_command_table = 'DELETE FROM commands'

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

def create_command_table(cur):
	cur.execute(create_command_table_query)

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
		create_state_table()
		insert_state(name, value)

def parse_report(report):
	for row in report:
		(command, data) = tuple(row.split(' '))
		if command == 'log_value':
			store_log_row(data)
		if command == 'state':
			store_state(data)

def parse_response(device):
	done = False
	report = []
        while not done:
                recv = read_serial_line(device)
                if recv == '\n':
                        done = True
                else:
                        report.append(recv)

        if len(report) > 0:
                parse_report(report)

def get_report(device):
	device.write('get-report\n') 
	parse_response(device)

def get_states(device):
	device.write('get-states\n')
	parse_response(device)

def get_commands(cur):
	return [row[0] for row in cur.execute(get_commands_query)]
	

def do_commands(device):
	commands = []
	anything_done = False
	cur = db.cursor()
	try:
		commands = get_commands(cur)
	except sqlite3.OperationalError:
		create_command_table(cur)
		commands = get_commands(cur)

	for command in commands:
		device.write(command)
		device.write('\n')
		anything_done = True

	cur.execute(clear_command_table)
	db.commit()

	if anything_done:
		get_states(device)

def update_light(device):
	seconds_in_a_day = 86400.0
	now = datetime.datetime.now()
	midnight = datetime.datetime.combine(now.date(), datetime.time(0))
	delta = now - midnight
	light = 0.0
	
	seconds_from_midday = delta.seconds - (seconds_in_a_day / 2)
	print "Secs from mid: ", seconds_from_midday

	normalized = seconds_from_midday / (seconds_in_a_day / 2)
	print "Normalized: ", normalized

	if math.fabs(normalized) > 0.5:
		print "Is down"
		light = 0.0
	else:
		light = math.cos(normalized * 2.0 * math.pi)
		print "Is up: ", light

	device.write("set-light " + str(light) + "\n");

	

dev = serial.Serial(config.serial_device, baudrate=115200, timeout=3.0)

next_update = datetime.datetime.now()
dev.flushInput()


# Wait for the Arduino to boot...
time.sleep(5)

while True:
	current_time = datetime.datetime.now()
	if current_time > next_update:
		update_light(dev)
		get_report(dev)
		next_update = current_time + config.update_interval
	else:
		do_commands(dev)

	time.sleep(1)
