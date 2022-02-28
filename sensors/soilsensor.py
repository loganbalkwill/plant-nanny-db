#THIS MODULE IS DEPRICATED!!!



import time

from board import SCL, SDA
import busio

from adafruit_seesaw.seesaw import Seesaw
#import settings

i2c_bus = busio.I2C(SCL, SDA)

ss = Seesaw(i2c_bus, addr=0X36)


def get_moisure():
    #retrieve latest moisture value
    return ss.moisture_read()

def get_temp():
    #retrieve latest temperature value (Degrees Celcius)
    return ss.get_temp()


if __name__=='__main__':
    while True:
        # read moisture level through capacitive touch pad
        touch = ss.moisture_read()

        # read temperature from the temperature sensor
        temp = ss.get_temp()

        print("temp: " + str(temp) + "  moisture: " + str(touch))
        time.sleep(1)
