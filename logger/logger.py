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
	try:
		if row[0] == 0:
			cur.execute(store_state_value % (name, value))
		else:
			cur.execute(update_state_value % (value, name))
		db.commit()
	except:
		print("Could not use database, state not written.")

def insert_log_row(table, value):
	cur = db.cursor()
	try:
	        cur.execute(store_log_value % (table, value))
	        db.commit()
	except:
		print("Could not use database, log not written.")

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
		device.write(str(command))
		device.write('\n')
		anything_done = True
	try:
		cur.execute(clear_command_table)
		db.commit()
	except:
		print("Could not use database, command table not cleared.")

	if anything_done:
		get_states(device)

def update_light(device):
	day_secs = 86400.0
	hour_secs = 3600.0
	now = datetime.datetime.now()
	midnight = datetime.datetime.combine(now.date(), datetime.time(0))
	delta = now - midnight
	light = 0.0

	current_hour = delta.seconds / 3600.0 - 12

	if math.fabs(current_hour) < config.light_hours / 2:
		light = config.light_max
	elif math.fabs(current_hour) < (config.light_hours/2) + config.light_fade_hours:
		sign = current_hour / current_hour
		fade_amount = (math.fabs(current_hour) - (config.light_hours/2)) * sign
		fade_amount = fade_amount / config.light_fade_hours

		light = (math.cos(fade_amount * math.pi) + 1.0) / 2.0 * config.light_max
	else:
		light = 0.0
	
	device.write("set-light-value " + str(light) + "\n");

	

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
