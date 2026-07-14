import globals
import json
import time
from enocean import utils
from enocean.devices import vld, rps, bs4p, bs1,response
from enocean.protocol.constants import PACKET, RORG
from enocean.protocol.packet import RadioPacket, UTETeachIn

try:
    from jeedom.jeedom import *
except ImportError:
    print("Error: importing module from jeedom folder")
    sys.exit(1)

def learn_packet_message(message):
    if message['type'] == 'repeater':
        repeater(message['dest'],message['level'],message['profile'])
    elif message['type'] == 'remMan':
        remMan(message)

def remote_learnin(remoteid):
    logging.debug('Sending learnin remote message')
    data = [0xC5,0x40,0x01,0x7F,0xF2,0x20] + \
        [0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x00]
    optional = [0x03] + utils.destination_sender_to_list(remoteid) + [0xFF, 0x00,0xE4]
    globals.COMMUNICATOR.send(RadioPacket(PACKET.RADIO, data=data, optional=optional))
    return
def remote_learnout(remoteid):
    logging.debug('Sending learnout remote message')
    data = [0xC5,0xC0,0x01,0x7F,0xF2,0x20] + \
        [0x40,0x00,0x00,0x00,0x00,0x00,0x00,0x8F]
    optional = [0x03] + utils.destination_sender_to_list(remoteid) + [0xFF, 0x00]
    globals.COMMUNICATOR.send(RadioPacket(PACKET.RADIO, data=data, optional=optional))
    return
def remote_learnexit(remoteid):
    logging.debug('Sending learnexit remote message')
    data = [0xC5,0x40,0x01,0x7F,0xF2,0x20] + \
        [0x80,0x00,0x00,0x00,0x00,0x00,0x00,0x8F]
    optional = [0x03] + utils.destination_sender_to_list(remoteid) + [0xFF, 0x00]
    globals.COMMUNICATOR.send(RadioPacket(PACKET.RADIO, data=data, optional=optional))
    return
def remote_co(packet):
    return
def repeater(remoteid,level,profile):
    logging.debug('Sending Repeater remote message for profile ' + str(profile))
    dataheader = [0xD1,0x46,0x00,0x08]
    if profile['rorg'] == 'd2' and profile['type'] == '0a' and profile['func'] == '01' :
        logging.debug('Should invert two bytes for Sending Repeater remote message for profile ' + str(profile))
        dataheader = [0xD1,0x00,0x46,0x08]
    frelevel=0x01
    if level == '1':
        relevel= 0x01
    elif level == '2':
        relevel= 0x02
    else:
        relevel= 0x00
        frelevel=0x00
    data = dataheader + [frelevel,relevel] + \
        globals.COMMUNICATOR.base_id + [0x00]
    optional = [0x03] + utils.destination_sender_to_list(remoteid) + [0xFF, 0x00]
    utils.sender(RadioPacket(PACKET.RADIO, data=data, optional=optional),remoteid)
    return
def remMan(message):
    logging.debug('Sending RemMan remote message')
    if message['subtype'] == 'unlock':
        logging.debug('Sending unlock with code ' + message['code'].zfill(8))
        data = [0xC5,0x40,0x02,0x7F,0xF0,0x01] + utils.destination_sender_to_list(message['code']) +\
        [0x00,0x00,0x00,0x00] + [0x8F]
        optional = [0x03] + [0xFF,0xFF,0xFF,0xFF]+ [0xFF, 0x00]
        globals.COMMUNICATOR.send(RadioPacket(PACKET.RADIO, data=data, optional=optional))
    elif message['subtype'] == 'lock':
        logging.debug('Sending lock with code ' + message['code'].zfill(8))
        data = [0xC5,0x40,0x02,0x7F,0xF0,0x02] + utils.destination_sender_to_list(message['code']) +\
        [0x00,0x00,0x00,0x00] + [0x8F]
        optional = [0x03] + [0xFF,0xFF,0xFF,0xFF]+ [0xFF, 0x00]
        globals.COMMUNICATOR.send(RadioPacket(PACKET.RADIO, data=data, optional=optional))
    elif message['subtype'] == 'setcode':
        logging.debug('Sending setcode with code ' + message['code'].zfill(8))
        data = [0xC5,0x40,0x02,0x7F,0xF0,0x03] + utils.destination_sender_to_list(message['code']) +\
        [0x00,0x00,0x00,0x00] + [0x8F]
        optional = [0x03] + [0xFF,0xFF,0xFF,0xFF]+ [0xFF, 0x00]
        globals.COMMUNICATOR.send(RadioPacket(PACKET.RADIO, data=data, optional=optional))
    elif message['subtype'] == 'calibration':
        logging.debug('Sending calibration')
        data = [0xC5,0xC0,0x03,0x7F,0xF2,0x31, 0x00,0x01,0x03,0xC0,0x00,0x00,0x00,0x00,0x8f]
        optional = [0x03] + utils.destination_sender_to_list(message['dest'])+ [0xFF, 0x00]
        globals.COMMUNICATOR.send(RadioPacket(PACKET.RADIO, data=data, optional=optional))
        data = [0xC5,0xC1,0x56,0x01,0x00,0x00, 0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x8f]
        optional = [0x03] + utils.destination_sender_to_list(message['dest'])+ [0xFF, 0x00]
        globals.COMMUNICATOR.send(RadioPacket(PACKET.RADIO, data=data, optional=optional))
    elif message['subtype'] == 'getAlltable':
        logging.debug('Sending get All Table')
        data = [0xC5,0xC0,0x01,0xFF,0xF2,0x11, 0x00,0x00,0x17,0xC0,0x00,0x00,0x00,0x00,0x8f]
        optional = [0x03] + utils.destination_sender_to_list(message['dest'])+ [0xFF, 0x00]
        globals.COMMUNICATOR.send(RadioPacket(PACKET.RADIO, data=data, optional=optional))
    return
