#!/usr/bin/env python

"""main.py:    
               -hosts main project code
               -hosts list and notes for unfinished project sections
"""

__author__    = "Logan Balkwill"
__date__      = "September 10, 2020"
__version__   = "1.2.1"
__maintainer__= "Logan Balkwill"
__email__     = "lgb0020@gmail.com"


import time
import math
import settings as s
import supporting_modules.looping as looping
import supporting_modules.logger as logging
import supporting_modules.actions as actions
import supporting_modules.device_info as device_info
import supporting_modules.database_use as db

"""
GENERAL ALGORITHM:
    
    STARTUP:
        -CHECK AVAILABLE PERIPHERALS
        -CHECK DATABASE CONNECTION
            -UPLOAD QUEUED LOGS (IF EXISTS)
        -CHECK EMAILING FUNCTIONALITY
        -CALCULATE SLEEP FREQUENCY BETWEEN LOOPS
        
    MAIN LOOP:
        -READ REQUIRED INSTRUMENTS
        -WRITE ^ TO DATABASE
        -CHECK VALUE AGAINST ALLOWABLE CONSTRAINTS
        -PERFORM ACTION(S) AS REQUIRED:
            -PERFORM PHYSICAL ACTION (I.E. WATER)
            -NOTIFY SUBSCRIBER(S) VIA EMAIL

-----------------------------------------------------------------

VERSIONING:
    1.0.1                   -   I AM BORN
    1.1.1                   -   Major code restructuring (no significant program impact)
    1.2.1   (2020-12-18)    -   Patches to fit new db structure 
"""

#TODO: find good way of resetting the loop counter


#########################
### STARTUP PROCEDURE ###
#########################

#Initialize Global Variables
i2c_available=[]
plant_devices_list=[]
action_freq_list=[]
loop_freq=s.read_frequency_mins
logs_queued=0

def startup():
    #Acknowledge global variables
    global i2c_available, plant_devices_list, action_freq_list, loop_freq, logs_queued
    
    #Check Peripherals
    #Build list of sensors in-use
    i2c_available=device_info.find_i2c_devices()
    plant_devices_list=db.build_plant_devices_list()
    action_freq_list=looping.get_action_freqs(plant_devices_list)
    

        #Calculate looping frequency
    loop_freq=looping.get_loop_frequency(action_freq_list)

    #Check Database Connection
        #Check for queued logs
    logs_queued_prev = logs_queued
    logs_queued, msg = logging.local_logs_exist()
    
    if logs_queued > 0 and logs_queued != logs_queued_prev:
        logging.log_info(log_level='i', message=msg)
    else:
        logging.log_info(log_level='p',message=msg)
    
    if logs_queued > 0:
        logging.upload_local_logs()

    #Check Emailing Functionality




#########################
####### MAIN LOOP #######
#########################
def main():
    
    loopcounter=1
    
    while 1==1:
        
        #Check sensors
        for action in plant_devices_list:
            Assigned_id, Assigned_type, Assigned_name, device_id, device_name, action_freq=action
            
            #Condition frequency value (if required)
            action_freq=looping.condition_frequency(action_freq)
            
            #Check if interval has elapsed
            if(loopcounter % action_freq==0):
                #Yes; Perform Action
                try:
                    actions.perform_action(action)
                    logging.log_action(action,"success")
                except:
                    logging.log_action(action,"failure")
        
        #Check if system check interval has elapsed
        if (loopcounter % s.refresh_mins == 0):
            logging.log_info( log_level='p', message="Checking for new information...")
            startup() #yep; run the startup procedure again
        
        
        #Increment interval counter
        try:
            loopcounter+=1
        except:
            looping.reset_loopcounter(loopcounter)
            
            
        time.sleep(60*loop_freq)
        

if __name__=='__main__':
    startup()
    main()