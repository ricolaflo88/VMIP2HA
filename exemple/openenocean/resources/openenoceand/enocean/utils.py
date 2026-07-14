# -*- encoding: utf-8 -*-
from __future__ import print_function, unicode_literals, division, absolute_import
import globals
import logging
import threading
import time

def sender(packet,destination):
    if destination in globals.LAST_SENT:
        delta = time.time()-globals.LAST_SENT[destination]
        logging.debug('Last packet for this ' + destination + ' was emited ' + str(delta) + ' seconds ago')
        if delta<0.5 and delta>0 and destination != 'Broadcast':
            timer=0.5-delta
            threading.Thread( target=threadedsender, args= (packet,timer,destination,)).start()
            return
    globals.COMMUNICATOR.send(packet)
    globals.LAST_SENT[destination] = time.time()
    return

def threadedsender(packet,timer,destination):
    logging.debug('Waiting ' + str(timer) + ' seconds ' + str(packet))
    time.sleep(timer)
    globals.COMMUNICATOR.send(packet)
    globals.LAST_SENT[destination] = time.time()

def get_bit(byte, bit):
    ''' Get bit value from byte '''
    return (byte >> bit) & 0x01


def combine_hex(data):
    ''' Combine list of integer values to one big integer '''
    output = 0x00
    for i, value in enumerate(reversed(data)):
        output |= (value << i * 8)
    return output


def to_bitarray(data, width=8):
    ''' Convert data (list of integers, bytearray or integer) to bitarray '''
    if isinstance(data, list) or isinstance(data, bytearray):
        data = combine_hex(data)
    return [True if digit == '1' else False for digit in bin(data)[2:].zfill(width)]


def from_bitarray(data):
    ''' Convert bit array back to integer '''
    return int(''.join(['1' if x else '0' for x in data]), 2)

def from_bitarray_split(data):
    ''' Convert bit array back to list of integer '''
    chunks = [data[x:x+8] for x in xrange(0, len(data), 8)]
    list=[]
    for chunk in chunks:
        list.append(int(''.join(['1' if x else '0' for x in chunk]), 2))
    return list

def bitarray_sizing(data,size):
    ''' Add or delete first element to list of bitarray to fit size '''
    if len(data)>size :
        for x in range(0,len(data)-size):
            data.pop(0)
    if len(data)<size :
        for x in range(0,size-len(data)):
            data.insert(0,False)
    return data


def to_hex_string(data):
    ''' Convert list of integers to a hex string, separated by ":" '''
    if isinstance(data, int):
        return '%02X' % data
    if data == None:
        return 'Broadcast'
    return ':'.join([('%02X' % o) for o in data])


def from_hex_string(hex_string):
    reval = [int(x, 16) for x in hex_string.split(':')]
    if len(reval) == 1:
        return reval[0]
    return reval

def profile_from_action(action):
    return action['rorg'] + '-' + action['func'] +'-' + action['type']

def destination_sender_to_list(destination):
    result = []
    destination = destination.zfill(8)
    for i in range(0,8,2):
        result.append(from_hex_string(destination[i:i+2]))
    return result

def string_to_list(string):
    result = []
    for i in range(0,len(string),2):
        result.append(from_hex_string(string[i:i+2]))
    return result

def dec2hex(dec):
    if dec is None:
        return 0
    return hex(dec)[2:]

def vocid_to_name(voc):
    VocDict = {0 : 'total',\
             1 : 'formaldehyde',\
             2 : 'benzene',\
             3 : 'styrene',\
             4 : 'toluene',\
             5 : 'tetrachloroethylene',\
             6 : 'xylene',\
             7 : 'n-hexane',\
             8 : 'n-octane',\
             9 : 'cyclopentane',\
             10 : 'methanol',\
             11 : 'ethanol',\
             12 : '1-pentanol',\
             13 : 'acetone',\
             14 : 'ethylene-oxyde',\
             15 : 'acetaldehyde-ue',\
             16 : 'acetic-acid',\
             17 : 'propionice-acid',\
             18 : 'valeric-acid',\
             19 : 'butyric-acid',\
             20 : 'ammoniac',\
             22 : 'hydrogen-sulfide',\
             23 : 'dimethylsulfide',\
             24 : '2-butanol',\
             25 : '2-methylpropanol',\
             26 : 'diethyl-ether',\
             27 : 'naphtalene',\
             28 : '4-phenylcyclohexene',\
             29 : 'limonene',\
             30 : 'trichloroethylene',\
             31 : 'isovaleric-acid',\
             32 : 'indole',\
             33 : 'cadaverine',\
             34 : 'putrescine',\
             35 : 'caproic-acid',\
             255 : 'ozone'}

    if voc in VocDict:
        return VocDict[voc]
    else:
        return False
