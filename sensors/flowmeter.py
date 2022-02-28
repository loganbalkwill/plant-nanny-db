import RPi.GPIO as GPIO
import time

GPIO_Pin=16

boardRevision = GPIO.RPI_REVISION
GPIO.setmode(GPIO.BCM) # use real GPIO numbering
GPIO.setup(GPIO_Pin,GPIO.IN, pull_up_down=GPIO.PUD_UP)


# set up the flow meter
pouring = False
lastPinState = False
pinState = 0
lastPinChange = int(time.time() * 1000)
pourStart = 0
pinChange = lastPinChange
pinDelta = 0
hertz = 0
flow = 0
litersPoured = 0


# main loop
while True:
    currentTime = int(time.time() * 1000)
    if GPIO.input(GPIO_Pin):
      pinState = False
    else:
      pinState = True

    #print("Time: %s , Pin State: %s" % (currentTime, pinState))  



# If we have changed pin states low to high...
    if(pinState != lastPinState and pinState == True):
        if(pouring == False):
          pourStart = currentTime
          print("starting pour (%s)" % pourStart)
        pouring = True
        # get the current time
        pinChange = currentTime
        pinDelta = pinChange - lastPinChange
        if (pinDelta < 1000):
          # calculate the instantaneous speed
          hertz = 1000.0000 / pinDelta
          flow = hertz / (60 * 7.5) # L/s
          litersPoured += flow * (pinDelta / 1000.0000)
          pintsPoured = litersPoured * 2.11338
      
    if (pouring == True and pinState == lastPinState and (currentTime - lastPinChange) > 3000):
    # set pouring back to false, tweet the current amt poured, and reset everything
        pouring = False
        if (pintsPoured > 0.1):
          pourTime = int((currentTime - pourStart)/1000) - 3
          #tweet = 'Someone just poured ' + str(round(pintsPoured,2)) + ' pints of root beer in ' + str(pourTime) + ' seconds'
          #t.statuses.update(status=tweet)
          print("Pouring Stopped (%s): Total poured= %s mL" % (currentTime, litersPoured*1000))
          
          litersPoured = 0
          pintsPoured = 0  
    
    lastPinChange = pinChange
    lastPinState = pinState
