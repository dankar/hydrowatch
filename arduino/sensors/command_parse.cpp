#include <Arduino.h>
#include <string.h>

#include "pins.h"
#include "command_parse.h"
#include "commands.h"

int strcmp(const char *, const char*);

command_func lookup_command(char *requested_command)
{
	for(int i = 0; i < num_commands; i++)
	{
		if(strcmp(requested_command, commands[i].command) == 0)
			return commands[i].func;
	}

	return NULL;
}


char receive_buffer[512];

int serial_read_command(char *cmd[])
{
	bool done = false;

	int number = 0;
	int index = 0;

	char *start = receive_buffer;

	while(!done)
	{
		while(!Serial.available());

		receive_buffer[index] = Serial.read();

		if(receive_buffer[index] == '\n' || receive_buffer[index] == ' ')
		{
			if(receive_buffer[index] == '\n')
				done = true;

			receive_buffer[index] = '\0';
			cmd[number++] = start;

			start = &receive_buffer[++index];

		}
		else
			index++;
	}

	return number;
}

