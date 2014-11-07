#include "pins.h"
#include "command_parse.h"
#include "commands.h"

void setup(void)
{
        Serial.begin(115200);
        init_commands();
}

void loop(void)
{
        int command_num = 0;
        char *cmd[10];

        command_num = serial_read_command(cmd);

	command_func command_ptr = lookup_command(cmd[0]);

	if(command_ptr)
		command_ptr(cmd, command_num);
}
