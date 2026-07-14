# -*- encoding: utf-8 -*-
import logging
from enocean import utils
import globals

def parse(packet):
    action = {}
    logging.debug('Receive response packet : ' + str(packet.packet_type))
    response = packet.response
    if response == 0x00 :
        response = 'OK'
    elif response == 0x01 :
        response = 'ERROR'
    elif response == 0x02 :
        response = 'NOT_SUPPORTED'
    elif response == 0x03 :
        response = 'WRONG_PARAM'
    elif response == 0x04 :
        response = 'OPERATION_DENIED'
    elif response == 0x05 :
        response = 'RET_LOCK_SET'
    elif response == 0x06 :
        response = 'RET_BUFFER_TO_SMALL'
    elif response == 0x07 :
        response = 'RET_NO_FREE_BUFFER'
    logging.debug('Response is : ' + str(response))
    if utils.to_hex_string(packet.data[5:9]) != '':
        action['return_code'] = utils.to_hex_string(packet.data[0])
        action['app_version'] = utils.to_hex_string(packet.data[1:5])
        action['api_version'] = utils.to_hex_string(packet.data[5:9])
        action['chip_version'] = utils.to_hex_string(packet.data[13:17])
        action['app_description'] = str(bytearray(packet.data[17:]))
        globals.JEEDOM_COM.send_change_immediate(action)
    globals.WAITING_RES = False
    return
