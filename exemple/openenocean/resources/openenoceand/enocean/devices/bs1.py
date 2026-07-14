# -*- encoding: utf-8 -*-
import logging
from enocean import utils
import globals
from enocean.protocol.packet import RadioPacket, UTETeachIn
from enocean.protocol.constants import PACKET, RORG

def parse(action,packet):
    logging.debug("Its a BS1 message")
    for k in packet.parsed:
        action[k] = packet.parsed[k]
    return action

