#include <DallasTemperature.h>
#include <OneWire.h>
#include "pins.h"
#include "command_parse.h"
#include "commands.h"

int num_commands = 0;

command_t commands[] =
{"get-report", &get_report};

OneWire one_wire(ONE_WIRE_PIN);
DallasTemperature sensor(&one_wire);

static int is_inited = 0;

void init_commands()
{
	num_commands = sizeof(commands)/sizeof(command_t);
}

int get_report(char *args[], int arg_num)
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

        Serial.print("state light_setting=2");
        Serial.print("\n");

        Serial.print("\n"); // Two newlines marks end of packet
        return 1;
}
