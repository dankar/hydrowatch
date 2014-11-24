#include <Arduino.h>
#include <string.h>
#include "pins.h"
#include "tds.h"
#include <FreqCounter.h>

volatile unsigned long trig_time = 0;
volatile unsigned long last1_trig_time = 0;
volatile unsigned long last2_trig_time = 0;
volatile byte send_data = 0;
byte power_on = 0;

unsigned long start_time = 0;

void cycle_power()
{
	digitalWrite(TDS_POWER_PIN, HIGH);
	delay(100);
	digitalWrite(TDS_POWER_PIN, LOW);
	power_on = !power_on;
}

unsigned long get_tds()
{
	unsigned long freq = 0;

	start_time = millis();
	FreqCounter::f_comp = 10;
	pinMode(TDS_FREQ_PIN, INPUT);
	digitalWrite(TDS_POWER_PIN, LOW);
	pinMode(TDS_POWER_PIN, OUTPUT);

	digitalWrite(TDS_FREQ_PIN, LOW);
	cycle_power();

	while(true)
	{
		if(millis() - start_time > 1000) // Timeout after 1s
		{
			cycle_power();
			return 0;
		}
		if(!digitalRead(TDS_FREQ_PIN))
		{
			trig_time = micros();
			FreqCounter::start(32);
			while(FreqCounter::f_ready == 0);
			freq = FreqCounter::f_freq;

			if(last2_trig_time > 0)
			{
				if(
					((last1_trig_time - last2_trig_time) < 50000)
					 && ((trig_time - last1_trig_time) < 50000)
					)
				{
					// We have data
					send_data = 1;
				}
			}
			last2_trig_time = last1_trig_time;
			last1_trig_time = trig_time;
			trig_time = 0;

			if(send_data)
			{
				send_data = 0;
				cycle_power();
				return freq;
			}
		}
	}
}
