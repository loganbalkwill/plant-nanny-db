import os
import sys

import time
from datetime import datetime
from picamera import PiCamera

sys.path.insert(0, '..')

import settings
import supporting_modules.database_use as database_use
import supporting_modules.sensors as sensors

camera=PiCamera()

def get_photos_path(plant_id, plant_name):   
    #Build the string of file name + location
    path= settings.image_directory
    sub_path=str(plant_id) + "|" + str(plant_name)
    now=datetime.now()
    filename=str(now.strftime("%m-%d-%Y %H:%M:%S"))
    filetype=settings.image_filetypels
    
    fullpath=path+ sub_path + '/' + filename + filetype
    
    #Make directory if doesnt already exist
    if os.path.exists(path + sub_path)==False:
        os.mkdir(path + sub_path + '/')
    
    return fullpath
    
def perform_action(action):
    #called by main loop when specified frequency has elapsed
    
    #input supplied as list in form:
    #PlantID, Plant Name, Device_ID, Device Name, Action_Frequency_Min
    
    Assigned_id, Assigned_type, Assigned_name, device_id, device_name, action_freq =action
    
    #decide what action to perform
    if device_name=='STEMMA Soil Sensor':
        #retrieve values and insert record into database
        #AVERAGE x READINGS
        x=30
        i=0
        soil_temp=0
        soil_moisture=0
        
        while i<x:
            soil_temp=soil_temp + sensors.get_soil_temp()
            soil_moisture=soil_moisture + sensors.get_soil_moisture()
        
            i+=1
            time.sleep(1)
    
        soil_temp=soil_temp/x
        soil_moisture=soil_moisture/x
        
        database_use.write_to_db(table='soilsensor_trans',
                                 write_info=[datetime.now(),
                                             Assigned_id,
                                             soil_temp,
                                             soil_moisture])
    elif device_name=='SGP30':
        #retrieve values and insert record into database
        air_eco2=sensors.get_air_co2()
        air_tvoc=sensors.get_air_tvoc()
        
        database_use.write_to_db(table='gassensor_trans',
                                 write_info=[datetime.now(),
                                             Assigned_id,
                                             air_eco2,
                                             air_tvoc])
    elif device_name=='APDS9960':
        #retrieve values and insert record into database
        r,g,b,c=sensors.get_light_rgbc()
        
        database_use.write_to_db(table='lightsensor_trans',
                                 write_info=[datetime.now(),
                                             Assigned_id,
                                             r, g, b, c])
    elif device_name=='BME680':
        #retrieve values and insert record into database
        temp=sensors.get_air_temp()
        humidity=sensors.get_air_humidity()
        pressure=sensors.get_air_pressure()
        gasses=sensors.get_air_gas()
        
        database_use.write_to_db(table='airsensor_trans',
                                 write_info=[datetime.now(),
                                             Assigned_id,
                                             temp,
                                             humidity,
                                             gasses,
                                             pressure])
    elif device_name=='PiCamera':
        #Takes photo, stores to predefined location, writes info to database
        
        fullpath=get_photos_path(Assigned_id, Assigned_name)
        
        camera.start_preview()
        time.sleep(5) #Important to allow camera to stabilize the image
        
        camera.capture(fullpath)
        camera.stop_preview()
        
        database_use.write_to_db(table='photo_trans',
                                 write_info=[datetime.now(),
                                             Assigned_id,
                                             fullpath])
        
    return    

# if __name__=='__main__':
