# -*- encoding: utf-8 -*-
import logging
from enocean import utils
import globals
from enocean.protocol.packet import RadioPacket
from enocean.protocol.constants import PACKET, RORG

def send(rorg,raw,sender,destination):
    logging.debug('Sending Raw message ' + str(raw))
    data = [rorg] + utils.string_to_list(raw) + sender +[0x80]
    optional = [0x03] + [0xFF,0xFF,0xFF,0xFF] + [0xFF, 0x00]
    globals.COMMUNICATOR.send(RadioPacket(PACKET.RADIO, data=data, optional=optional))
