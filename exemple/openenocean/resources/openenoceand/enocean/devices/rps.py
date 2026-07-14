# -*- encoding: utf-8 -*-
import logging
from enocean import utils
import globals

def parse(action,packet):
    logging.debug("Its a RPS message")
    for k in packet.parsed:
        action[k] = packet.parsed[k]
    if globals.KNOWN_DEVICES[action['id']][0]['ignoreRelease'] == 1 :
        if 'EB' in action and action['EB']['raw_value'] == 0:
            logging.debug('Release button and module configured to ignore release so I pass')
            action['ignore'] = 1
            return action
    else:
        action['immediate'] = 1
    if 'SA' in action and action['SA']['value'] == '2nd action valid' :
        action['multiple'] = ''
        if action['R1']['raw_value'] == 0:
            action['multiple'] = 'A0'
        elif action['R1']['raw_value'] == 1:
            action['multiple'] = 'A1'
        elif action['R1']['raw_value'] == 2:
            action['multiple'] = 'B0'
        elif action['R1']['raw_value'] == 3:
            action['multiple'] = 'B1'
        if action['R2']['raw_value'] == 0:
            action['multiple'] += 'A0'
        elif action['R2']['raw_value'] == 1:
            action['multiple'] += 'A1'
        elif action['R2']['raw_value'] == 2:
            action['multiple'] += 'B0'
        elif action['R2']['raw_value'] == 3:
            action['multiple'] += 'B1'
    else :
        if globals.KNOWN_DEVICES[action['id']][0]['allButtons'] == 1 and 'R1' in action :
            if globals.KNOWN_DEVICES[action['id']][0]['ignoreRelease'] == 1 :
                if action['R1']['raw_value'] == 0:
                    action['bt_3'] = 'toggle'
                elif action['R1']['raw_value'] == 1:
                    action['bt_1'] = 'toggle'
                elif action['R1']['raw_value'] == 2:
                    action['bt_4'] = 'toggle'
                elif action['R1']['raw_value'] == 3:
                    action['bt_2'] = 'toggle'
            else:
                if 'EB' in action and action['EB']['raw_value'] == 0:
                    action['bt_3'] = 0
                    action['bt_1'] = 0
                    action['bt_4'] = 0
                    action['bt_2'] = 0
                else:
                    if action['R1']['raw_value'] == 0:
                        action['bt_3'] = 1
                    elif action['R1']['raw_value'] == 1:
                        action['bt_1'] = 1
                    elif action['R1']['raw_value'] == 2:
                        action['bt_4'] = 1
                    elif action['R1']['raw_value'] == 3:
                        action['bt_2'] = 1
        else:
            if 'R1' in action:
                if action['R1']['raw_value'] == 0:
                    action['bt_a'] = 0
                elif action['R1']['raw_value'] == 1:
                    action['bt_a'] = 1
                elif action['R1']['raw_value'] == 2:
                    action['bt_b'] = 0
                elif action['R1']['raw_value'] == 3:
                    action['bt_b'] = 1
            if 'WAS' in action:
                if action['WAS']['raw_value'] == 17 and action['NU']['raw_value'] == 1:
                    action['liquid'] = 1
                else:
                    action['liquid'] = 0
            if 'WIN' in action:
                if action['WIN']['raw_value'] == 1:
                    action['position'] = 1
                    action['positionlabel'] = 'haut'
                    action['positionbinary'] = 1
                elif action['WIN']['raw_value'] == 3:
                    action['position'] = 0
                    action['positionlabel'] = 'bas'
                    action['positionbinary'] = 0
                else:
                    action['position'] = 2
                    action['positionlabel'] = 'horizontal'
                    action['positionbinary'] = 1
    return action

