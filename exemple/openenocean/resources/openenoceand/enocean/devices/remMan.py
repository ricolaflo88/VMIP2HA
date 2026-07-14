# -*- encoding: utf-8 -*-
import logging
from enocean import utils
import globals
from enocean.protocol.packet import RadioPacket
from enocean.protocol.constants import PACKET, RORG

def parse(packet):
    action = {}
    logging.debug('Received remote man command packet : ' + str(packet.packet_type))
    remoteco = packet.remoteco
    if remoteco == 0x240 :
        remoteco = 'Remote Commissioning Acknowledge'
        globals.JEEDOM_COM.send_change_immediate({'Remcoack' :  {utils.to_hex_string(packet.optional[-6:-2]).replace(":","") : 'ok'}});
    elif remoteco == 0x210 :
        remoteco = 'Get Link Table Metadata Query'
    elif remoteco == 0x810 :
        remoteco = 'Get Link Table Metadata Response'
    elif remoteco == 0x211 :
        remoteco = 'Get Link Table Query'
    elif remoteco == 0x811 :
        remoteco = 'Get Link Table Response'
        link={}
        link['inbound'] =utils.to_hex_string(packet.remoteco_data[2:3])
        link['number'] =utils.to_hex_string(packet.remoteco_data[3:4])
        link['id'] =utils.to_hex_string(packet.remoteco_data[4:8])
        link['profil'] =utils.to_hex_string(packet.remoteco_data[8:11])
        link['channel'] =utils.to_hex_string(packet.remoteco_data[11:12])
        globals.JEEDOM_COM.send_change_immediate({'LinkTable' :  {utils.to_hex_string(packet.optional[-6:-2]).replace(":","") : link}});
    elif remoteco == 0x212 :
        remoteco = 'Set Link Table Content'
    elif remoteco == 0x213 :
        remoteco = 'Get Link Table GP Entry Query'
    elif remoteco == 0x813 :
        remoteco = 'Get Link Table GP Entry Response'
    elif remoteco == 0x214 :
        remoteco = 'Set Link Table GP Entry Content'
    elif remoteco == 0x220 :
        remoteco = 'Remote Set Learn Mode'
    elif remoteco == 0x221 :
        remoteco = 'Trigger Outbound Remote Teach Request'
    elif remoteco == 0x230 :
        remoteco = 'Get Device Configuration Query'
    elif remoteco == 0x830 :
        remoteco = 'Get Device Configuration Response'
    elif remoteco == 0x231 :
        remoteco = 'Set Device Configuration Query'
    elif remoteco == 0x232 :
        remoteco = 'Get Link Based Configuration Query'
    elif remoteco == 0x832 :
        remoteco = 'Get Link Based Configuration Response'
    elif remoteco == 0x233 :
        remoteco = 'Set Link Based Configuration Query'
    elif remoteco == 0x226 :
        remoteco = 'Apply Changes'
    elif remoteco == 0x224 :
        remoteco = 'Reset Device Defaults'
    elif remoteco == 0x225 :
        remoteco = 'Radio Link Test Control'
    elif remoteco == 0x227 :
        remoteco = 'Get Product ID'
    elif remoteco == 0x827 :
        remoteco = 'Get Product ID Response'
    logging.debug('Remoteco message is : ' + str(remoteco))
    return