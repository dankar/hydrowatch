#include <Arduino.h>
#include <string.h>
#include "pins.h"
#include "tds.h"
#include <FreqCounter.h>

volatile byte triggered = 0;
volatile unsigned long trig_time = 0;
volatile unsigned long last1_trig_time = 0;
volatile unsigned long last2_trig_time = 0;
volatile byte send_data = 0;
byte power_on = 0;

void isr()
{
  if(!triggered)
  {
    trig_time = micros();
    triggered = 1;
  }
}

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
  FreqCounter::f_comp = 10;
  pinMode(TDS_INT_PIN, INPUT);
  pinMode(TDS_FREQ_PIN, INPUT);
  digitalWrite(TDS_POWER_PIN, LOW);
  pinMode(TDS_POWER_PIN, OUTPUT);

  digitalWrite(TDS_INT_PIN, LOW);
  digitalWrite(TDS_FREQ_PIN, LOW);
  attachInterrupt(1, isr, FALLING); 
  
  cycle_power();
  
  while(true)
  {
    if(triggered)
    {
      FreqCounter::start(32);
      while(FreqCounter::f_ready == 0);
      freq = FreqCounter::f_freq;
      
      noInterrupts();
        
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
      interrupts();
       
      if(send_data)
      {
        send_data = 0;
		detachInterrupt(1);
        cycle_power();
        return freq;
      }
        
      triggered = 0;
    }
  }
}