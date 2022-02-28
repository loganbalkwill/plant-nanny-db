import sys
import math

sys.path.insert(0, '..')

import settings

def get_loop_frequency(numlist):
    #returns the frequency of looping in the main procedure (minutes)
    
    #default returns 1 minute
    GCD=settings.read_frequency_mins
    reclist=[]
    
    #include startup procedure refresh frequency in numlist
    numlist.append(settings.refresh_mins)
    
    #if any of the numbers provided are less than 1 minute (non-negative),
    #a greatest common divisor (GCD) is calculated
    
    for num in numlist:
        if num<0: 
            print("ERROR: negative number provided for loop frequency; please correct")
            #This shouldn't happen because of the database table settings
            break
        elif num>1 and num!=math.ceil(num):
            print("WARNING: partial minute provided for loop frequency (%s mins); number will be rounded to %s mins for calculation" % (num, math.floor(num)))
            
            num=math.floor(num)
        reclist.append(num)
    
    num_min=min(reclist)
    if num_min<1 and num_min>0:
        #need to calculate new GCM
        GCD=(math.gcd(math.floor(num_min*60),60))/60
    
    return GCD        

def get_action_freqs(plant_device_list):
    #returns list of action frequencies from main list
    
    #input list in the form of:
    #PlantID, Plant Name, Device_ID, Device Name, Action_Frequency_Min
    
    freq_list=[]
    
    for rw in plant_device_list:
        Assigned_id, Assigned_type, Assigned_name, device_id, device_name, action_freq=rw
        freq_list.append(action_freq)
            
    
    return freq_list

def condition_frequency(num):
    #return suitable frequency
    
    if num>1 and num!=math.ceil(num):    
        num=math.floor(num)
    return num


def reset_loopcounter(counter, frequency_list):
    return 1