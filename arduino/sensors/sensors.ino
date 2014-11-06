#include <DallasTemperature.h>
#include <OneWire.h>

#define ONE_WIRE_PIN 2
#define WATER_LEVEL_PIN 0
#define LIGHT_LEVEL_PIN 4

OneWire one_wire(ONE_WIRE_PIN);
DallasTemperature sensor(&one_wire);

void setup(void)
{
  Serial.begin(115200);
  sensor.begin();
}

void loop(void)
{
  while(!Serial.available() > 0);
  while(Serial.available() > 0) Serial.read();
  float water_temperature, water_level, light_level;
  sensor.requestTemperatures();
  water_temperature = sensor.getTempCByIndex(0);
  water_level = analogRead(WATER_LEVEL_PIN);
  light_level = analogRead(LIGHT_LEVEL_PIN);
  Serial.print("water_temperature: ");
  Serial.print(water_temperature);
  Serial.print("\n");
  Serial.print("water_level: ");
  Serial.print(water_level);
  Serial.print("\n");
  Serial.print("light_level: ");
  Serial.print(light_level);
  Serial.print("\n\n"); // Two newlines marks end of packet
}
