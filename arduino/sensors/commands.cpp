#include <DallasTemperature.h>
#include <OneWire.h>
#include "pins.h"
#include "command_parse.h"
#include "commands.h"

int num_commands = 0;


OneWire one_wire(ONE_WIRE_PIN);
DallasTemperature sensor(&one_wire);

static int is_inited = 0;

#define LIGHT_OFF 0
#define LIGHT_ON 1
#define LIGHT_AUTO 2

#define MOTOR_OFF 0
#define MOTOR_ON 1
#define MOTOR_SILENT 2

static float current_light = 0.0;
static float light_value = 0.0;
static int light_setting = LIGHT_AUTO;
static int motor_setting = MOTOR_ON;

int print_states()
{
	Serial.print("state light_setting=");
        Serial.print(light_setting);
	Serial.print("\n");
	Serial.print("state light_value=");
	Serial.print(light_value);
        Serial.print("\n");
	Serial.print("state current_light=");
	Serial.print(current_light);
	Serial.print("\n");
	
	Serial.print("state motor_setting=");
	Serial.print(motor_setting);
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
		
		Serial.print("log_value tds_level=");
		Serial.print(get_tds());
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
	current_light = pwm;
	analogWrite(LIGHT_PWM_PIN, pwm*255);
}

int set_light(char *args[], int arg_num)
{
	if(arg_num != 2)
	{
		return 0;
	}

	light_setting = atoi(args[1]);

	if(light_setting == LIGHT_AUTO)
		set_light_pwm(light_value);
	else if(light_setting == LIGHT_ON)
		set_light_pwm(1.0);
	else if(light_setting == LIGHT_OFF)
		set_light_pwm(0.0);

	return 1;
}

int set_light_value(char *args[], int arg_num)
{
	if(arg_num != 2)
	{
		return 0;
	}

	light_value = atof(args[1]);

	if(light_setting == LIGHT_AUTO)
		set_light_pwm(light_value);

	return 1;
}

int set_motor(char *args[], int arg_num)
{
	if(arg_num != 2)
		return 0;
		
	motor_setting = atoi(args[1]);
	
	return 1;
}

command_t commands[] =
{
{"get-report", &get_report},
{"get-states", &get_states},
{"set-light", &set_light},
{"set-light-value", &set_light_value}
{"set-motor", &set_motor}
};

void init_commands()
{
	num_commands = sizeof(commands)/sizeof(command_t);
	set_light_pwm(0.0);
}

