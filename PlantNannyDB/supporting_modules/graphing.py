import sys
sys.path.insert(0, '..')

import datetime as dt
import matplotlib.pyplot as plt
import numpy as np
import supporting_modules.database_use as db
import settings as s
import supporting_modules.dictionaries as dictionaries


"""
TODO:
    Add proper logging
    Expand dataset formatter
    Remaining graphs, SQL
    Standardizing graphs
        create graph class?
    
    Bring into main program
        incorporate as an action???

DONE:
    SQL libraries for easier lookup
"""


axis_intervals_hours = [0,3,6,9,12,15,18,21,24]

save_folder=s.graphics_directory
save_filetype=s.graphics_filetype 

#Retrieve Dictionaries
sql_select=dictionaries.sql_select.copy()

#Storing information

def airtemp_graph(plant_id, target_date=str(dt.date.today())):
    SQL=sql_select['air_temp']
    
    #Clear any pre-existing plots
    plt.clf()
    
    #Setup plotting region
    plt.ylabel('Degrees Celcius')
    plt.xlabel(target_date)
    plt.xticks(axis_intervals_hours)
    plt.suptitle('Air Temperature')
    plt.legend()

    #Get all data
    try:
        for plant in plant_id:
            times, temps = get_plot_data(plantid=plant, SQL_base=SQL , input_date=target_date)
            times=format_data(dat=times,conversion="datetime-to-timestamp_decimal")
            #Add new plot
            plt.plot(times, temps, label=('Plant ID: ' + str(plant)))
    except:
        raise Exception("Error gathering data for Air Temperature Graph")

    #Publish Results
    #plt.show()
    plt.savefig(save_folder + target_date +'__Air-Temp' + save_filetype)

def airhumidity_graph(plant_id, target_date=str(dt.date.today())):
    SQL=sql_select['air_humidity']

    #Clear any pre-existing plots
    plt.clf()
    
    #Setup plotting region
    plt.ylabel('Air Humidity (%)')
    plt.xlabel(target_date)
    plt.xticks(axis_intervals_hours)
    plt.suptitle('Air Humidity')
    plt.legend()
    
    #Get all data
    try:
        for plant in plant_id:
            times, humidity = get_plot_data(plantid=plant, SQL_base=SQL , input_date=target_date)
            times=format_data(dat=times,conversion="datetime-to-timestamp_decimal")
            #Add new plot
            plt.plot(times, humidity, label=('Plant ID: ' + str(plant)))
    except:
        raise Exception("Error gathering data for Air Humidity Graph")

    #Publish Results
    #plt.show()
    plt.savefig(save_folder + target_date +'__Air-Humidity' + save_filetype)

def get_plot_data(plantid, SQL_base, input_date=str(dt.date.today())):
    #returns datasets x,y data for a given date, plant_id, SQL string
    
    #validate date input
    try:
        date1=dt.date.fromisoformat(input_date)
    except:
        raise Exception("Date value '%s' supplied is not in a recognizable date format" % input_date)
    
    #build query parameters
    date2 = date1 + dt.timedelta(days=1)
    SQL_complete= (SQL_base % (date1, date2, plantid))
    
    #run query
    data = db.query_db(sql_string=SQL_complete)

    #unpack data
    x=[]
    y=[]
    
    for i in data:
        x.append(i[0])
        y.append(i[1])
    
    return x, y

def format_data(dat, conversion):
    dat_formatted=[]
    
    for i in dat:
        if conversion=='datetime-to-timestamp_decimal':
            dat_formatted.append((i.seconds)/3600)
        else:
            raise Exception("Datatype '%s' could not be handled properly" % datatype)
    
    return dat_formatted

if __name__=='__main__':
        
    airtemp_graph(plant_id=[2])
    airhumidity_graph(plant_id=[2])