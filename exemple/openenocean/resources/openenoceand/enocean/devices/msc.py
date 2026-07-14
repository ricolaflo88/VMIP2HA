# -*- encoding: utf-8 -*-
import logging
from enocean import utils
import globals
from enocean.protocol.packet import RadioPacket, UTETeachIn
from enocean.protocol.constants import PACKET, RORG

def parse(action,packet):
    for k in packet.parsed:
        action[k] = packet.parsed[k]
    if str(action['rorg']) == 'd1079':
        if str(action['func']) == '01':
            if str(action['destination']).lower() != 'ffffffff' and str(action['destination']).lower() != str(utils.to_hex_string(globals.COMMUNICATOR.base_id)).replace(':','').lower():
                logging.debug("Vmi message not for jeedom ignoring")
                action['ignore'] = 1
        if 'IDMACH' in action:
            action['IDMACH']['value'] = hex(int(action['IDMACH']['raw_value']))[2:].rstrip("L").zfill(8)
        if 'CAPTINDEX' in action:
            name = 'CAPTEUR'+ str(action['CAPTINDEX']['raw_value'])
            action[name]={}
            action[name]['value'] = hex(int(action['IDAPP']['raw_value']))[2:].rstrip("L").zfill(8) +'|'+action['PIECEAPP']['value']+'|'+action['PROFAPP']['value']
    return action

def send(rorg,datas,sender,destination):
    logging.debug("Handling MSC message " +str(destination) + ' ' + str(sender) + ' ' + str(datas) + ' ' + str(rorg))
    if str(rorg) == 'd1079':
        logging.debug('Msc message for Ventilairsec VMI')
        ventilairsecVMI(destination,sender,datas)

def ventilairsecVMI(dest,sender,datas):
    if str(datas['command']) == '0':
        logging.debug('VentilairsecVmi : This is a 0 CMD')
        rawcommand ='0790'
        if 'MODEFONC' in datas :
            rawcommand += hex(int(datas['MODEFONC']))[2:].zfill(2)
        else:
            rawcommand += 'FF'
        if 'FONC' in datas :
            rawcommand += hex(int(datas['FONC'],2))[2:].zfill(2)
        else:
            rawcommand += 'FF'
        if 'VACS' in datas :
            rawcommand += hex(int(datas['VACS']))[2:].zfill(2)
        else:
            rawcommand += 'FF'
        if 'BOOST' in datas :
            rawcommand += hex(int(datas['BOOST']))[2:].zfill(2)
        else:
            rawcommand += 'FF'
        if 'TEMPEL' in datas :
            rawcommand += hex(int(datas['TEMPEL']))[2:].zfill(2)
        else:
            rawcommand += 'FF'
        if 'TEMPSOUF' in datas :
            rawcommand += hex(int(datas['TEMPSOUF']))[2:].zfill(2)
        else:
            rawcommand += 'FF'
        if 'TEMPHYD' in datas :
            rawcommand += hex(int(datas['TEMPHYD']))[2:].zfill(2)
        else:
            rawcommand += 'FF'
        if 'TEMPSOL' in datas :
            rawcommand +=  hex(int(datas['TEMPSOL']))[2:].zfill(2)
        else:
            rawcommand += 'FF'
        if 'COMMAND' in datas :
            rawcommand += hex(int(datas['COMMAND']))[2:].zfill(2)
        else:
            rawcommand += 'FF'
        logging.debug(rawcommand)
        transmit(int('d1',16),rawcommand,sender,dest)
    elif str(datas['command']) == '1':
        logging.debug('VentilairsecVmi : This is a 1 CMD')
        rawcommand ='0791'
        if 'HOUR' in datas :
            rawcommand += datas['HOUR']
        logging.debug(rawcommand)
        transmit(int('d1',16),rawcommand,sender,dest)
    elif str(datas['command']) == '2':
        logging.debug('VentilairsecVmi : This is a 2 CMD')
        rawcommand ='0792'
        if 'AGENDA' in datas :
            rawcommand += datas['AGENDA']
        logging.debug(rawcommand)
        transmit(int('d1',16),rawcommand,sender,dest)

def transmit(rorg,raw,sender,destination):
    logging.debug('Sending Raw message ' + str(raw))
    data = [rorg] + utils.string_to_list(raw) + sender +[0x80]
    optional = [0x03] + [0xFF,0xFF,0xFF,0xFF] + [0xFF, 0x00]
    globals.COMMUNICATOR.send(RadioPacket(PACKET.RADIO, data=data, optional=optional))
