#include <DallasTemperature.h>
#include <OneWire.h>

#define ONE_WIRE_PIN 2
#define WATER_LEVEL_PIN

OneWire one_wire(ONE_WIRE_PIN);
DallasTemperature sensor(&one_wire);

void setup(void)
{
  Serial.begin(115200);
  Serial.println("start");
  sensor.begin();
}

void loop(void)
{
  float water_temperature, water_level;
  sensor.requestTemperatures();
  water_temperature = sensor.getTempCByIndex(0);
  water_level = analogRead(0);
  Serial.print("temp: ");
  Serial.println(water_temperature);
  Serial.print("level: ");
  Serial.println(water_level);
  Serial.print("\n\n"); // Two newlines marks end of packet
}
