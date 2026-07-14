# -*- encoding: utf-8 -*-
from __future__ import print_function, unicode_literals, division, absolute_import
import logging
import serial
import time

from enocean.communicators.communicator import Communicator


class SerialCommunicator(Communicator):
    ''' Serial port communicator class for EnOcean radio '''
    logger = logging.getLogger('enocean.communicators.SerialCommunicator')

    def __init__(self, port='/dev/ttyAMA0', callback=None):
        super(SerialCommunicator, self).__init__(callback)
        # Initialize serial port
        self.__ser = serial.Serial(port, 57600, timeout=0.1)

    def run(self):
        logging.info('SerialCommunicator started')
        while not self._stop_flag.is_set():
            # If there's messages in transmit queue
            # send them
            while True:
                packet = self._get_from_send_queue()
                if not packet:
                    break
                try:
                    self.__ser.write(bytearray(packet.build()))
                    time.sleep(0.02)
                except serial.SerialException:
                    logging.error('Serial port exception!')
                    self.stop()

            # Read chars from serial port as hex numbers
            try:
                self._buffer.extend(bytearray(self.__ser.read(16)))
            except serial.SerialException:
                logging.error('Serial port exception! (device disconnected or multiple access on port?)')
                self.stop()
            self.parse()

        self.__ser.close()
        logging.info('SerialCommunicator stopped')
