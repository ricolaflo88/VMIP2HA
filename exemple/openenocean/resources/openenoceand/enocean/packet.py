import globals
import json
import time
from enocean import utils
from enocean.devices import vld, rps, bs4p, bs1,response, rawhandler, remMan ,msc
from enocean.protocol.constants import PACKET, RORG
from enocean.protocol.packet import RadioPacket, UTETeachIn

try:
    from jeedom.jeedom import *
except ImportError:
    print("Error: importing module from jeedom folder")
    sys.exit(1)

def packet_get_eep(packet):
    packet_id = str(packet.sender_hex).replace(":","")
    packet_rorg = str(jeedom_utils.dec2hex(packet.rorg))
    if packet_id in globals.KNOWN_DEVICES:
        for info in globals.KNOWN_DEVICES[packet_id]:
            if packet.rorg == RORG.MSC:
                if str(info['rorg']) == str(jeedom_utils.dec2hex(packet.rorg)).zfill(2)+str(jeedom_utils.dec2hex(packet.rorg_manufacturer)).zfill(3):
                    return info,packet
            else:
                if str(info['rorg']) == packet_rorg:
                    return info,packet
    if packet.contains_eep:
        try:
            rorg = jeedom_utils.dec2hex(packet.rorg_of_eep)
        except:
            rorg = packet_rorg
        if rorg == jeedom_utils.dec2hex(RORG.MSC):
            return {'rorg' : str(jeedom_utils.dec2hex(packet.rorg_of_eep)).zfill(2)+str(jeedom_utils.dec2hex(packet.rorg_manufacturer)).zfill(3), 'func' : str(jeedom_utils.dec2hex(packet.rorg_func)).zfill(2) , 'type' : str(jeedom_utils.dec2hex(packet.rorg_type)).zfill(2)},packet
        else:
            return {'rorg' : rorg, 'func' : str(jeedom_utils.dec2hex(packet.rorg_func)).zfill(2) , 'type' : str(jeedom_utils.dec2hex(packet.rorg_type)).zfill(2)},packet
    
    if packet.rorg == RORG.BS4:
        return {'rorg' : packet_rorg, 'func' : '02' , 'type' : '05'},packet

    if packet.rorg == RORG.BS1:
        return {'rorg' : packet_rorg, 'func' : '00' , 'type' : '01'},packet

    if packet.rorg == RORG.RPS:
        return {'rorg' : packet_rorg, 'func' : '02' , 'type' : '02'},packet

    if packet.rorg == RORG.VLD:
        return {'rorg' : packet_rorg, 'func' : '01' , 'type' : '01'},packet
    
    if packet.rorg == RORG.CHAINED:
        logging.debug('This is a chained telegram ')
        logging.debug('Chained data ' + str(packet.data))
        data = packet.data
        binary = bin(int(str(data[1])))[2:].zfill(8)
        logging.debug('Chained telegram binary seq idx ' + str(binary))
        seq = int(str(binary)[0:4].zfill(8),2)
        idx = int(str(binary)[5:].zfill(8),2)
        key = packet_id+'.'+str(seq)
        logging.debug('Chained telegram binary seq is ' + str(seq) + ' and idx is ' + str(idx))
        if idx == 0 :
            logging.debug('First message of the chain with seq ' + str(seq))
            logging.debug('First message of the chainhas optional ' + str(packet.optional))
            lendata = int(str(data[2])+str(data[3]))
            logging.debug('Chain Len will be ' + str(lendata) + ' and sender is ' + str(packet_id))
            storagedata={}
            storagedata['len']=lendata
            storagedata['optional']=packet.optional
            storagedata['data']=data[4:-5]
            storagedata['final']=data[-5:]
            storagedata['time']=int(time.time())
            globals.STORAGE_CHAIN[key] = storagedata
            logging.debug('Storage Chain ' + str(globals.STORAGE_CHAIN))
        else:
            logging.debug('This is number ' + str(idx) + ' message of the chain with seq ' + str(seq))
            if key in globals.STORAGE_CHAIN:
                logging.debug('Found data in storage chain : appending')
                globals.STORAGE_CHAIN[key]['data'] = globals.STORAGE_CHAIN[key]['data']+data[2:-5]
                logging.debug('Storage Chain ' + str(globals.STORAGE_CHAIN))
                expectedlen = globals.STORAGE_CHAIN[key]['len']
                currentlen = len(globals.STORAGE_CHAIN[key]['data'])
                logging.debug('Chain expected len for ' + key + ' is ' + str(expectedlen) + ' current len is ' + str(currentlen))
                if currentlen >= expectedlen:
                    logging.debug('Chain Data is complete and is ' + str(globals.STORAGE_CHAIN[key]['data']) + ' with final ' + str(globals.STORAGE_CHAIN[key]['final']))
                    packet.data=globals.STORAGE_CHAIN[key]['data']+globals.STORAGE_CHAIN[key]['final']
                    del globals.STORAGE_CHAIN[key]
                    logging.debug('Reparsing chained final data')
                    packet.parse()
                    logging.debug('Reanalysing chained final data')
                    return packet_get_eep(packet)
                else:
                    logging.debug('Chain Data is still not complete')
            else:
                logging.debug('No data in storage chain can\'t do anything with this data')
    if packet.rorg == RORG.MSC:
        data = packet.data
        logging.debug('This is a MSC telegram ' + str(data))
        return {'rorg' : str(jeedom_utils.dec2hex(packet.rorg)).zfill(2)+str(jeedom_utils.dec2hex(packet.rorg_manufacturer)).zfill(3), 'func' : str(jeedom_utils.dec2hex(packet.rorg_func)).zfill(2) , 'type' : str(jeedom_utils.dec2hex(packet.rorg_type)).zfill(2)},packet
    if packet.rorg == RORG.UTE:
        logging.debug('This is a UTE telegram ' + str(packet.data))
    return None,packet
    
def decode_packet(packet):
    action = {}
    if packet.packet_type == PACKET.RESPONSE:
        response.parse(packet)
        return
    elif packet.packet_type == PACKET.EVENT:
        logging.debug('Received event packet : ' + str(packet))
        return
    elif packet.packet_type == PACKET.RADIO_SUB_TEL:
        logging.debug('Received radio sub tel packet : ' + str(packet))
        return
    elif packet.packet_type == PACKET.COMMON_COMMAND:
        logging.debug('Received common command packet : ' + str(packet))
        return
    elif packet.packet_type == PACKET.SMART_ACK_COMMAND:
        logging.debug('Received smart ack command packet : ' + str(packet))
        return
    elif packet.packet_type == PACKET.REMOTE_MAN_COMMAND:
        remMan.parse(packet)
        return
    elif packet.packet_type == PACKET.RADIO_MESSAGE:
        logging.debug('Received radio message packet : ' + str(packet))
        return
    elif packet.packet_type == PACKET.RADIO_ADVANCED:
        logging.debug('Received radio advanced packet : ' + str(packet))
        return
    if packet.packet_type != PACKET.RADIO:
        logging.debug('Not decode because it\'s not radio package : ' + str(packet.packet_type))
        return
    if packet.sender[0:3] == globals.COMMUNICATOR.base_id[0:3]:
        logging.debug('Ignore this is an echo')
        return
    eep,packet = packet_get_eep(packet)
    if eep is None:
        logging.debug('No eep found, no decoded')
        return
    try: 
        repeat = str(packet.repeater_count)
    except Exception as e:
        logging.debug(str(e))
        repeat = '0'
    logging.debug('Message is repeated ' + repeat + ' times')
    action['id'] = str(packet.sender_hex).replace(":","")
    action['rorg'] = eep['rorg']
    action['packet_type'] = str(packet.packet_type)
    action['dBm'] = str(packet.dBm)
    action['func'] = eep['func']
    action['type'] = eep['type']
    action['repeat'] = repeat
    action['destination'] = str(packet.destination_hex).replace(":","")
    action['manufacturer'] = str(jeedom_utils.dec2hex(packet.rorg_manufacturer)).zfill(3)
    action['cmd'] = packet.cmd
    logging.debug(str(action))
    if packet.learn and globals.EXCLUDE_MODE:
        if packet.rorg == RORG.VLD and eep['func'] == '01':
            logging.debug('It\'s should be a UTE packet for exclusion, i ignore')
            return
        globals.EXCLUDE_MODE = False
        logging.debug('It\'s learn packet and I am in exclude mode, i delete the device')
        globals.JEEDOM_COM.send_change_immediate({'exclude_mode' : 0, 'deviceId' : str(packet.sender_hex).replace(":","") });
        return
    if action['id'] not in globals.KNOWN_DEVICES:
        if not packet.learn or not globals.LEARN_MODE:
            logging.debug('Not decode because it\'s an unknown device or I\'am not in learn mode or telegram is not a learn telegram ' + str(action['id'] + ' packet learn is ' +str(packet.learn)))
            return
        elif packet.learn and globals.LEARN_MODE:
            if packet.rorg == RORG.VLD and eep['func'] == '01':
                logging.debug('It\'s should be a UTE packet for learn, i ignore')
                return
            logging.debug('It\'s learn packet and I don\'t known this device so I learn')
            try:
                if action['rorg'][0:2]=='d1':
                    logging.debug('It\'s a MSC learn device')
                    if utils.profile_from_action(action) in globals.KNOWN_MSC:
                        logging.debug('It\'s a MSC learn device that we want to include')
                    else:
                        logging.debug('It\'s a MSC learn device that we don\'t want to include')
                        return
                if utils.profile_from_action(action) in globals.LEARN_PROCEDURE['BS4VAR3']:
                    logging.debug('It\'s a BS4VAR3 learn device')
                    packet.parse_eep(utils.from_hex_string(eep['func']),utils.from_hex_string(eep['type']))
                    if packet.parsed['LRNB']['raw_value'] == 0 :
                        logging.debug('It\'s really a BS4VAR3 learn packet let\'s respond')
                        bs4p.response_learn_BS4VAR3(packet)
                    else:
                        logging.debug('It\'s not really a BS4VAR3 learn packet ignoring')
                        return
                action['learn'] = 1
                globals.JEEDOM_COM.add_changes('devices::'+action['id'],action)
                globals.JEEDOM_COM.send_change_immediate({'learn_mode' : 0});
                globals.LEARN_MODE = False
            except Exception as e:
                logging.debug(str(e))
        return
    if eep['func'] == '38' and eep['type']=='08':
        packet.cmd = 2
    packet.parse_eep(utils.from_hex_string(eep['func']),utils.from_hex_string(eep['type']) , command = packet.cmd )
    parse_packet(action,packet)

def parse_packet(action,packet):
    logging.debug("Parsing Packet")
    if packet.rorg == RORG.VLD:
        action = vld.parse(action,packet)
    if packet.rorg == RORG.RPS:
        action = rps.parse(action,packet)
    if packet.rorg == RORG.BS4:
        action = bs4p.parse(action,packet)
    if packet.rorg == RORG.BS1:
        action = bs1.parse(action,packet)
    if packet.rorg == RORG.MSC:
        action = msc.parse(action,packet)
    logging.debug('Decode data : '+json.dumps(action))
    try:
        if len(action) > 7 and (not 'ignore' in action or action['ignore'] != 1):
            if ('immediate' in action and action['immediate'] == 1) :
                globals.JEEDOM_COM.send_change_immediate({'devices' : {action['id']:action}})
            else:
                globals.JEEDOM_COM.add_changes('devices::'+action['id'],action)
    except Exception as e:
        pass
    return

def send_command(message,learn=False, immediate=False):
    kwargs={}
    command = None
    generic = ''
    direction = None
    delay = False
    learn = False
    type = ''
    raw = ''
    ismsc = ''
    sender = globals.COMMUNICATOR.base_id
    commandRorg = int(message['profile']['rorg'][0:2],16)
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
        elif data == 'generic':
            generic = message['command'][data]
        elif data == 'learn':
            learn = message['command'][data]
        elif data == 'profil':
            profil = message['command'][data]
        elif data == 'raw':
            raw = message['command'][data]
        elif data == 'MSC':
            logging.debug('This is a MSC message')
            msc.send(message['profile']['rorg'],message['command'],sender,message['dest'])
            return
        else:
            try :
                kwargs[data] = int(message['command'][data])
            except :
                kwargs[data] = message['command'][data]
    if learn != False and learn != '1':
        send_learn(learn,message)
        return
    if learn == '1':
        learn = True
    if delay and not immediate:
        globals.STORAGE_MESSAGE[message['dest']] = message
        logging.debug('Storing message')
        return
    logging.debug(str(kwargs) +' on command ' + str(command) + ' ' + str(commandRorg)+ ' '+ str(commandFunc) + ' ' + str(commandType))
    if generic != '':
        if type == 'switch':
            sender = utils.destination_sender_to_list(generic)
            commandDestination = None
            utils.sender(RadioPacket.create(rorg=commandRorg, rorg_func =commandFunc, rorg_type =commandType, destination=commandDestination, sender=sender, learn = learn, command = command, direction = direction, EB = 1, **kwargs),utils.to_hex_string(commandDestination))
            if 'R1' in kwargs:
                kwargs['R1']=0
            if 'R2' in kwargs:
                kwargs['R2']=0
            utils.sender(RadioPacket.create(rorg=commandRorg, rorg_func =commandFunc, rorg_type =commandType, destination=commandDestination, sender=sender, learn = learn, command = command, direction = direction, EB = 0, **kwargs),utils.to_hex_string(commandDestination))
            return
        else :
            sender = utils.destination_sender_to_list(generic)
            commandDestination = None
    if raw != '':
        rawhandler.send(commandRorg,raw,sender,message['dest'])
        return
    utils.sender(RadioPacket.create(rorg=commandRorg, rorg_func =commandFunc, rorg_type =commandType, destination=commandDestination, sender=sender, learn = learn, command = command, direction = direction, **kwargs),utils.to_hex_string(commandDestination))

def send_learn(learn,message):
    if learn == 'BS4':
        bs4p.send_learn(message)
    return
