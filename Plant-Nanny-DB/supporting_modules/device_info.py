import subprocess
import supporting_modules.logger as logger

def find_i2c_devices():
    #Called when main.py is initialized, then regularly
    #used to detect available I2C devices
    device_list=[]
    
    p = subprocess.Popen(['i2cdetect', '-y','1'],stdout=subprocess.PIPE,) 
    #cmdout = str(p.communicate())
    
    for i in range(0,9):
        line = str(p.stdout.readline())
        #print(line)
        
        #Parse line for addresses
        if i==0:
            pass #Header row; no useful information available
        else:
            line_list=line.split()
            for addr in line_list:
                try:
                    addr=int(addr)
                    device_list.append('0x'+str(addr))
                except:
                    pass
    
    logger.log_info(log_level='d', message='Scanned I2C devices... Result: %s' % device_list)
    
    return device_list


if __name__=='__main__':
    print(find_i2c_devices())
