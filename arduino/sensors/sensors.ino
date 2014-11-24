#include "pins.h"
#include "command_parse.h"
#include "commands.h"

void setup(void)
{
        Serial.begin(115200);
        init_commands();

	/* Set PWM on pins 11 and 3 to different frequency
	 	Setting 	Divisor 	Frequency
		0x01 	 	1  		31372.55
		0x02 	 	8 	 	3921.16
		0x03  		32  		980.39
		0x04 	 	64 	 	490.20   <--DEFAULT
		0x05 	 	128  		245.10
		0x06  		256  		122.55
		0x07 	 	1024  		30.64
	
		TCCR2B = TCCR2B & 0b11111000 | <setting>;
	*/

	TCCR2B = TCCR2B & 0b11111000 | 0x02;

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
