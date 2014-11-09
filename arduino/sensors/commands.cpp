#include <DallasTemperature.h>
#include <OneWire.h>
#include "pins.h"
#include "command_parse.h"
#include "commands.h"

int num_commands = 0;


OneWire one_wire(ONE_WIRE_PIN);
DallasTemperature sensor(&one_wire);

static int is_inited = 0;

static float light_setting = 0;

int print_states()
{
	Serial.print("state light_setting=");
        Serial.print(light_setting);
        Serial.print("\n");
	return 1;
}

int print_reports()
{
	if(!is_inited)
        {
                sensor.begin();
                is_inited = 1;
        }

        float water_temperature, water_level, light_level;
        sensor.requestTemperatures();
        water_temperature = sensor.getTempCByIndex(0);
        water_level = analogRead(WATER_LEVEL_PIN);
        light_level = analogRead(LIGHT_LEVEL_PIN);

        Serial.print("log_value water_temperature=");
        Serial.print(water_temperature);
        Serial.print("\n");

        Serial.print("log_value water_level=");
        Serial.print(water_level);
        Serial.print("\n");

        Serial.print("log_value light_level=");
        Serial.print(light_level);
        Serial.print("\n");

        Serial.print("state light_setting=");
        Serial.print(light_setting);
        Serial.print("\n");
	return 1;
}

int get_states(char *argts[], int arg_num)
{
	print_states();
	Serial.print("\n");
}

int get_report(char *args[], int arg_num)
{

	print_reports();
	print_states();
        Serial.print("\n"); // Two newlines marks end of packet
        return 1;
}

void set_light_pwm(float pwm)
{
	analogWrite(LIGHT_PWM_PIN, pwm*255);
}

int set_light(char *args[], int arg_num)
{
	if(arg_num != 2)
	{
		return 0;
	}

	light_setting = atof(args[1]);
	set_light_pwm(light_setting);

	return 1;
}

command_t commands[] =
{
{"get-report", &get_report},
{"get-states", &get_states},
{"set-light", &set_light}
};

void init_commands()
{
	num_commands = sizeof(commands)/sizeof(command_t);
	set_light_pwm(0.0);
}

