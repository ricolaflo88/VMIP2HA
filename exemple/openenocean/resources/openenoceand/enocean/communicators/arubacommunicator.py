# -*- encoding: utf-8 -*-
from __future__ import print_function, unicode_literals, division, absolute_import
import logging
import socket
import json
import time
import binascii
import globals

from enocean.communicators.communicator import Communicator


class ArubaCommunicator(Communicator):
    ''' Socket communicator class for EnOcean radio '''

    def __init__(self, host='127.0.0.1', port=9637):
        super(ArubaCommunicator, self).__init__()
        self.host = host
        self.port = port

    def run(self):
        logging.info('Aruba Communicator started')
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.bind((self.host, self.port))
        sock.listen(5)
        sock.settimeout(0.5)
        try:
            while not self._stop_flag.is_set():
                while True:
                    packet = self._get_from_send_queue()
                    if not packet:
                        break
                    try:
                        time.sleep(0.02)
                        globals.JEEDOM_COM.send_change_immediate({'arubaMessage' : binascii.hexlify(bytearray(packet.build())).decode('ascii')});
                    except Exception as e:
                        logging.error('Aruba communication exception! ' + str(e))
                        self.stop()
                try:
                    (client, addr) = sock.accept()
                except socket.timeout:
                    continue
                logging.debug('Client ' + str(addr))
                client.settimeout(0.5)
                while True and not self._stop_flag.is_set():
                    try:
                        data = client.recv(2048)
                    except socket.timeout:
                        break
                    if not data:
                        break
                    logging.debug('Received Aruba Data ' + str(data))
                    self._buffer.extend(bytearray.fromhex(json.loads(data)['payload']))
                self.parse()
                client.close()
                logging.debug('Client disconnected')
            sock.close()
            logging.info('Aruba communicator stopped')
        except Exception as e:
            logging.error(str(e))
            sock.close()
