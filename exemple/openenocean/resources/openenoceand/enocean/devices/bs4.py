# -*- encoding: utf-8 -*-
import logging
from enocean import utils
import globals
from enocean.protocol.packet import RadioPacket, UTETeachIn
from enocean.protocol.constants import PACKET, RORG

def parse(action,packet):
    logging.debug("Its a BS4 message")
    for k in packet.parsed:
        action[k] = packet.parsed[k]
    if utils.profile_from_action(action) in globals.NEEDS_RESPONSE:
            logging.debug('This packets needs response')
            if action['id'] in globals.STORAGE_MESSAGE:
                logging.debug('A message is stored sending it')
                send_command(globals.STORAGE_MESSAGE[action['id']],immediate=True)
            else:
                logging.debug('Sending same message response to BS4')
                send_response(action)
    if action['func'] == '06':
        if 'RS' in action:
            if action['RS']['raw_value'] == 1:
                action['ILL'] = action['ILL2']['value']
            else :
                action['ILL'] = action['ILL1']['value']
    if action['func'] == '12' and action['type'] in ['10','01','00'] :
        channel = 1
        if 'CH' in action :
            channel = action['CH']['value'] + 1
        type = 'P'
        if action['DT']['raw_value']== 0:
            type = 'C'
        value = action['MR']['raw_value']
        if action['DIV']['raw_value'] == 1:
            finalValue = int(value)/float(10)
        elif action['DIV']['raw_value'] == 2:
            finalValue = int(value)/float(100)
        elif action['DIV']['raw_value'] == 3:
            finalValue = int(value)/float(1000)
        else:
            finalValue = value
        action[type+str(int(channel))] = finalValue
    if action['func'] == '09' and action['type'].lower() in ['05','0c']:
        action = parseVOC(action)
    if action['func'] == '11' and action['type'] == '02':
        if 'FAN' in action :
            if action['FAN']['raw_value'] in [0,16]:
                action['FANTECH'] = 190
            elif action['FAN']['raw_value'] in [1,17]:
                action['FANTECH'] = 165 
            elif action['FAN']['raw_value'] in [2,18]:
                action['FANTECH'] = 145 
            elif action['FAN']['raw_value'] in [3,19]:
                action['FANTECH'] = 0 
            else:
                action['FANTECH'] = 210
    return action

def response_learn_BS4VAR3(packet):
    data = [165] + utils.from_bitarray_split(utils.bitarray_sizing(utils.to_bitarray(packet.rorg_func),6) + \
            utils.bitarray_sizing(utils.to_bitarray(packet.rorg_type),7) +\
            utils.bitarray_sizing(utils.to_bitarray(packet.rorg_manufacturer),11)) + \
            [utils.from_bitarray([True,True,True,True,False,False,False,False])] + globals.COMMUNICATOR.base_id + [0]
    optional = [0x03] + utils.from_hex_string(packet.sender_hex) + [0xFF, 0x00]
    globals.COMMUNICATOR.send(RadioPacket(PACKET.RADIO, data=data, optional=optional))
    return

def send_learn(message):
    logging.debug('Sending BS4 learn message')
    data = [165] + utils.from_bitarray_split(utils.bitarray_sizing(utils.to_bitarray(utils.from_hex_string(message['profile']['func'])),6) + \
        utils.bitarray_sizing(utils.to_bitarray(utils.from_hex_string(message['profile']['type'])),7) +\
        utils.bitarray_sizing(utils.to_bitarray(0xFF),11)) + \
        [utils.from_bitarray([True,False,False,False,False,False,False,False])] + globals.COMMUNICATOR.base_id + [0]
    optional = [0x03] + [0xFF,0xFF,0xFF,0xFF] + [0xFF, 0x00]
    globals.COMMUNICATOR.send(RadioPacket(PACKET.RADIO, data=data, optional=optional))
    return

def parseVOC(action):
    vocname = utils.vocid_to_name(action['VOCID']['raw_value'])
    if vocname:
        value = action['CONC']['raw_value']
        if action['SCM']['raw_value'] == 0 :
            finalValue = int(value)/float(100)
        elif action['SCM']['raw_value'] == 1 :
            finalValue = int(value)/float(10)
        elif action['SCM']['raw_value'] == 3 :
            finalValue = int(value)*10
        else:
            finalValue = value
        action[vocname] = finalValue
    return action

def send_command(message,learn=False, immediate=False):
    kwargs={}
    command = None
    generic = ''
    direction = None
    delay = False
    learn = False
    type = ''
    raw = ''
    sender = globals.COMMUNICATOR.base_id
    commandRorg = int(message['profile']['rorg'],16)
    commandFunc = utils.from_hex_string(message['profile']['func'])
    commandType = utils.from_hex_string(message['profile']['type'])
    commandDestination = utils.destination_sender_to_list(message['dest'])
    for data in message['command']:
        if data == 'command' :
            command = int(message['command'][data])
        elif data == 'direction' :
            direction = int(message['command'][data])
        elif data == 'type':
            type = message['command'][data]
        elif data == 'delay':
            delay = message['command'][data]
        elif data == 'profil':
            profil = message['command'][data]
        else:
            try :
                kwargs[data] = int(message['command'][data])
            except :
                kwargs[data] = message['command'][data]
    logging.debug(str(kwargs) +' on command ' + str(command) + ' ' + str(commandRorg)+ ' '+ str(commandFunc) + ' ' + str(commandType))
    globals.COMMUNICATOR.send(RadioPacket.create(rorg=commandRorg, rorg_func =commandFunc, rorg_type =commandType, destination=commandDestination, sender=sender, learn = learn, command = command, direction = direction, **kwargs))

def send_response(action):
    logging.debug(str(action))
    message = {"cmd" : "send" , "dest" : action['id'], "profile" : {"func" : action['func'], "rorg" : action['rorg'], "type" : action["type"]}, "command" : {"direction" : 2 , "SP" : action['CV']['value']}}
    send_command(message)

