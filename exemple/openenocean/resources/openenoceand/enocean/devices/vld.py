# -*- encoding: utf-8 -*-
import logging
from enocean import utils
import globals

def parse(action,packet):
    logging.debug("Its a VLD message")
    for k in packet.parsed:
        action[k] = packet.parsed[k]
    if 'OV' in action:
        channel = action['IO']['raw_value']
        action['channel'+str(channel)+'-OV']= action['OV']['raw_value']
    if 'DIV' in action and action['DIV']['raw_value'] == 1:
        for x in action:
            if x[0:2] == 'CH':
                action[x]['value'] = action[x]['raw_value']/float(10)
    if 'MV' in action:
        channel = 1
        if 'IO' in action :
            channel = action['IO']['raw_value'] + 1
        type = 'P'
        if action['UN']['raw_value'] in [0,1,2]:
            type = 'C'
        value = action['MV']['value']
        if action['UN']['raw_value'] == 0:
            finalValue = int(value)/float(3600000)
        elif action['UN']['raw_value'] == 1:
            finalValue = int(value)/float(1000)
        elif action['UN']['raw_value'] == 4:
            finalValue = int(value)*float(1000)
        else:
            finalValue = value
        action[type+str(int(channel))] = finalValue
        
    return action

