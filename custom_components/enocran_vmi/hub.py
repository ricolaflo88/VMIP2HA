import binascii
import configparser
import json
import logging
import threading
import time
from pathlib import Path

import serial

from .const import (
    ATTR_AGENDA,
    ATTR_BOOST,
    ATTR_BATTERY,
    ATTR_COMMAND,
    ATTR_COMMAND_TYPE,
    ATTR_CO2,
    ATTR_DESTINATION,
    ATTR_FONC,
    ATTR_HOUR,
    ATTR_HUMIDITY,
    ATTR_MODEFONC,
    ATTR_RAW,
    ATTR_SENDER,
    ATTR_SIGNAL_STRENGTH,
    ATTR_TEMPERATURE,
    ATTR_TEMPEL,
    ATTR_TEMPHYD,
    ATTR_TEMPSOL,
    ATTR_TEMPSOUF,
    ATTR_VACS,
    CONF_DEVICE_NAME,
    CONF_DEVICE_PROFILE,
    CONF_DEVICE_SENDER,
    DOMAIN,
)

_LOGGER = logging.getLogger(__name__)


def _crc8(data):
    crc = 0
    for byte in data:
        crc ^= byte
        for _ in range(8):
            if crc & 0x80:
                crc = ((crc << 1) & 0xFF) ^ 0x07
            else:
                crc = (crc << 1) & 0xFF
    return crc


def _bytes_to_hex(data):
    return ":".join(f"{byte:02X}" for byte in data)


def _normalize_hex(value):
    value = value.replace(" ", "").replace(":", "").lower()
    if len(value) != 8:
        raise ValueError("Sender and destination identifiers must be 8 hex digits.")
    return ":".join(value[i : i + 2] for i in range(0, 8, 2)).upper()


def _hex_to_bytes(text):
    normalized = _normalize_hex(text)
    return [int(part, 16) for part in normalized.split(":")]


def _bytes_to_bits(data):
    bits = []
    for value in data:
        for bit in range(8):
            bits.append(bool((value >> (7 - bit)) & 0x01))
    return bits


def _bits_to_int(bits):
    value = 0
    for bit in bits:
        value = (value << 1) | (1 if bit else 0)
    return value


def load_devices_from_file(path):
    parsed = []
    if not path:
        return parsed

    device_file = Path(path)
    if not device_file.exists():
        return parsed

    config = configparser.ConfigParser()
    config.read(device_file)

    for section in config.sections():
        try:
            address = int(config.get(section, "address"), 16)
        except Exception:
            continue
        sender_hex = f"{address:08X}"

        rorg = config.get(section, "rorg", fallback="")
        func = config.get(section, "func", fallback="")
        typ = config.get(section, "type", fallback="")

        profile = None
        if rorg and func and typ:
            profile = _profile_from_rorg_func_type(rorg, func, typ)

        parsed.append(
            {
                "name": section,
                "sender": sender_hex,
                "profile": profile or "D1079",
            }
        )

    return parsed


def _profile_from_rorg_func_type(rorg, func, typ):
    rorg = rorg.lower().replace("0x", "")
    func = func.lower().replace("0x", "")
    typ = typ.lower().replace("0x", "")

    if rorg == "a5" and func == "04" and typ == "01":
        return "A5_04_01"
    if rorg == "a5" and func == "09" and typ == "04":
        return "A5_09_04"
    if rorg == "d1" and func == "07" and typ == "09":
        return "D1_07_09"
    return "D1079"


def _coerce_payload(payload):
    if isinstance(payload, str):
        try:
            return json.loads(payload)
        except Exception:
            return {}
    return payload if isinstance(payload, dict) else {}


def _normalized_payload(payload):
    normalized = {}
    for key, value in payload.items():
        if isinstance(key, str):
            normalized[key.lower()] = value
    return normalized


def build_updates_from_mqtt_payload(topic, payload):
    updates = {}
    payload = _coerce_payload(payload)
    if not isinstance(payload, dict):
        return updates

    payload_map = _normalized_payload(payload)
    topic = topic or ""
    normalized_topic = topic.lower()

    if "sensor/c02_vmi" in normalized_topic or "sensor/sdb_vmi" in normalized_topic:
        if "hum" in payload_map:
            updates[ATTR_HUMIDITY] = float(payload_map["hum"])
        if "tmp" in payload_map:
            updates[ATTR_TEMPERATURE] = float(payload_map["tmp"])
        if "conc" in payload_map:
            updates[ATTR_CO2] = float(payload_map["conc"])
        if "tsn" in payload_map:
            updates["tsn"] = int(payload_map["tsn"])
        if "hsn" in payload_map:
            updates["hsn"] = int(payload_map["hsn"])
        return updates

    if "sensor/t_vmi" in normalized_topic:
        if "batt" in payload_map:
            updates[ATTR_BATTERY] = int(payload_map["batt"])
        if "temp" in payload_map:
            updates[ATTR_TEMPERATURE] = float(payload_map["temp"])
        if "hum" in payload_map:
            updates[ATTR_HUMIDITY] = float(payload_map["hum"])
        return updates

    if "vmi" in normalized_topic and "cmd" in normalized_topic:
        if "debas" in payload_map:
            updates["debas"] = float(payload_map["debas"])
        if "pchauff" in payload_map:
            updates["pchauff"] = float(payload_map["pchauff"])
        if "etats" in payload_map:
            updates["etats"] = int(payload_map["etats"])
        if "cvitm" in payload_map:
            updates["cvitm"] = int(payload_map["cvitm"])
        if "tempext" in payload_map:
            updates["tempext"] = float(payload_map["tempext"])
        if "cpdiff" in payload_map:
            updates["cpdiff"] = float(payload_map["cpdiff"])
        if "iefil" in payload_map:
            updates["iefil"] = float(payload_map["iefil"])
        if "rcbeau" in payload_map:
            updates["rcbeau"] = float(payload_map["rcbeau"])
        if "dfonc" in payload_map:
            updates["dfonc"] = float(payload_map["dfonc"])
        if "idapp" in payload_map:
            updates["idapp"] = float(payload_map["idapp"])
        if "pieceapp" in payload_map:
            updates["pieceapp"] = int(payload_map["pieceapp"])
        if "profapp" in payload_map:
            updates["profapp"] = int(payload_map["profapp"])
        if "captindex" in payload_map:
            updates["captindex"] = int(payload_map["captindex"])
        return updates

    return updates


class EnOceanHub:
    def __init__(self, hass, serial_port, baudrate, devices):
        self.hass = hass
        self.serial_port = serial_port
        self.baudrate = baudrate
        self._device_configs = {}
        self._listeners = []
        self._stop_event = threading.Event()
        self._buffer = bytearray()
        self._thread = None
        self._serial = None
        self._states = {}
        self._last_command = None

        for device in devices:
            if not device:
                continue
            sender_hex = _normalize_hex(device[CONF_DEVICE_SENDER])
            self._device_configs[sender_hex] = {
                CONF_DEVICE_NAME: device[CONF_DEVICE_NAME],
                CONF_DEVICE_PROFILE: device[CONF_DEVICE_PROFILE],
                "destination": device.get("destination"),
            }

    async def async_setup_mqtt(self):
        try:
            from homeassistant.components import mqtt
        except ImportError:
            _LOGGER.debug("MQTT integration is not available in this Home Assistant build")
            return

        topic = "enoceanmqtt/#"
        try:
            await mqtt.async_subscribe(self.hass, topic, self._handle_mqtt_message)
        except Exception as err:
            _LOGGER.debug("Unable to subscribe to MQTT topic %s: %s", topic, err)

    def start(self):
        if self.serial_port:
            try:
                self._serial = serial.Serial(self.serial_port, self.baudrate, timeout=1)
            except Exception as err:
                _LOGGER.error("Unable to open serial port %s: %s", self.serial_port, err)
                return

        self._thread = threading.Thread(target=self._run, daemon=True)
        self._thread.start()

    def stop(self):
        self._stop_event.set()
        if self._thread is not None:
            self._thread.join(2)
        if self._serial is not None and self._serial.is_open:
            try:
                self._serial.close()
            except Exception:
                pass

    def add_listener(self, callback):
        self._listeners.append(callback)

        def remove():
            if callback in self._listeners:
                self._listeners.remove(callback)

        return remove

    def _run(self):
        while not self._stop_event.is_set():
            try:
                packet = self._read_packet()
            except Exception as err:
                _LOGGER.debug("Serial read error: %s", err)
                time.sleep(1)
                continue

            if packet is None:
                continue

            if packet["packet_type"] != 0x01:
                continue

            radio = self._parse_radio(packet["data"], packet["optional"])
            if radio is None:
                continue

            self._handle_radio(radio)

    def _read_packet(self):
        if self._serial is None or not self._serial.is_open:
            return None

        data = self._serial.read(128)
        if not data:
            return None

        self._buffer.extend(data)

        while True:
            if len(self._buffer) < 6:
                return None
            if self._buffer[0] != 0x55:
                self._buffer.pop(0)
                continue

            data_len = (self._buffer[1] << 8) | self._buffer[2]
            opt_len = self._buffer[3]
            packet_type = self._buffer[4]
            full_len = 6 + data_len + opt_len + 1

            if len(self._buffer) < full_len:
                return None

            header_crc = self._buffer[5]
            if _crc8(self._buffer[1:5]) != header_crc:
                _LOGGER.warning("Discarding invalid ESP3 header CRC")
                self._buffer.pop(0)
                continue

            packet = self._buffer[:full_len]
            self._buffer = self._buffer[full_len:]
            data = packet[6 : 6 + data_len]
            optional = packet[6 + data_len : 6 + data_len + opt_len]
            data_crc = packet[-1]
            if _crc8(data + optional) != data_crc:
                _LOGGER.warning("Discarding invalid ESP3 packet CRC")
                continue

            return {"packet_type": packet_type, "data": list(data), "optional": list(optional)}

    def _parse_radio(self, data, optional):
        if len(data) < 6:
            return None

        rorg = data[0]
        sender = data[-5:-1]
        destination = optional[1:5] if len(optional) >= 6 else []
        dBm = -optional[5] if len(optional) >= 6 else None
        payload = data[1:-5]

        return {
            "rorg": rorg,
            "sender": sender,
            "sender_hex": _bytes_to_hex(sender),
            "destination": destination,
            "destination_hex": _bytes_to_hex(destination) if destination else None,
            "payload": payload,
            "dBm": dBm,
        }

    def _handle_radio(self, radio):
        sender = radio["sender_hex"]
        profile = self._device_configs.get(sender, {}).get(CONF_DEVICE_PROFILE)
        updates = {ATTR_SIGNAL_STRENGTH: radio["dBm"]}

        if radio["rorg"] == 0xA5:
            sensor_updates = self._parse_a5_payload(radio["payload"], profile)
            if sensor_updates:
                updates.update(sensor_updates)
        elif radio["rorg"] == 0xD1:
            device_updates = self._parse_d1079_payload(radio["payload"])
            if device_updates:
                updates.update(device_updates)

        if len(updates) > 1:
            self._states.setdefault(sender, {}).update(updates)
            self._dispatch(sender, updates)

    def _handle_mqtt_message(self, msg):
        try:
            topic = getattr(msg, "topic", "")
            payload = getattr(msg, "payload", "")
        except Exception:
            topic = ""
            payload = ""

        self._handle_mqtt_payload(topic, payload)

    def _handle_mqtt_payload(self, topic, payload):
        updates = build_updates_from_mqtt_payload(topic, payload)
        if not updates:
            return

        sender = None
        for device in self._device_configs.values():
            device_name = (device.get(CONF_DEVICE_NAME) or "").lower()
            topic_name = (topic or "").lower()
            if device_name and device_name in topic_name:
                sender = next((key for key, value in self._device_configs.items() if value is device), None)
                break

        if sender is None:
            return

        if isinstance(payload, dict):
            rssi = payload.get("_RSSI_")
            if rssi is not None:
                updates[ATTR_SIGNAL_STRENGTH] = int(rssi)

        self._states.setdefault(sender, {}).update(updates)
        self._dispatch(sender, updates)

    def _parse_a5_payload(self, payload, profile):
        if len(payload) != 4:
            return None

        raw0, raw1, raw2, raw3 = payload
        if profile == "A5_09_04":
            return {
                ATTR_HUMIDITY: round(raw0, 1),
                ATTR_CO2: round(raw1 * 10.0, 1),
                ATTR_TEMPERATURE: round((raw2 * 51.0) / 255.0, 1),
            }

        if profile == "A5_04_01":
            return {
                ATTR_HUMIDITY: round(raw1, 1),
                ATTR_TEMPERATURE: round((raw2 * 40.0) / 250.0, 1),
            }

        # Fallback heuristic: if first byte is plausible CO2 and second byte is plausible humidity,
        # use A5_09_04 semantics; otherwise parse as A5_04_01.
        if raw0 <= 100 and raw1 <= 255 and raw2 <= 51:
            return {
                ATTR_HUMIDITY: round(raw0, 1),
                ATTR_CO2: round(raw1 * 10.0, 1),
                ATTR_TEMPERATURE: round((raw2 * 51.0) / 255.0, 1),
            }

        return {
            ATTR_HUMIDITY: round(raw1, 1),
            ATTR_TEMPERATURE: round((raw2 * 40.0) / 250.0, 1),
        }

    def _parse_d1079_payload(self, payload):
        bits = _bytes_to_bits(payload)
        if len(bits) < 48:
            return None
        manufacturer = _bits_to_int(bits[0:12])
        if manufacturer != 0x1079:
            return None

        command = _bits_to_int(bits[12:16])
        battery = _bits_to_int(bits[16:24])
        temp_raw = _bits_to_int(bits[24:40])
        hum_raw = _bits_to_int(bits[40:48])

        temperature = round((temp_raw * 0.01) - 40.0, 1)
        return {
            ATTR_BATTERY: battery,
            ATTR_TEMPERATURE: temperature,
            ATTR_HUMIDITY: hum_raw,
        }

    def _dispatch(self, sender, updates):
        for listener in self._listeners.copy():
            try:
                self.hass.add_job(listener, sender, updates)
            except Exception as err:
                _LOGGER.debug("Error dispatching update to listener: %s", err)

    def send_vmi_command(self, **data):
        destination = data.get(ATTR_DESTINATION)
        if destination is None:
            raise ValueError("VMI command needs a destination address")

        sender = data.get(ATTR_SENDER)
        if sender is None:
            raise ValueError("VMI command needs a sender address")

        destination_bytes = _hex_to_bytes(destination)
        sender_bytes = _hex_to_bytes(sender)
        command_type = int(data.get(ATTR_COMMAND_TYPE, 0))

        if command_type == 0:
            raw = [0x07, 0x90]
            raw.append(int(data.get(ATTR_MODEFONC, 0xFF)))
            raw.append(int(data.get(ATTR_FONC, 0xFF)))
            raw.append(int(data.get(ATTR_VACS, 0xFF)))
            raw.append(int(data.get(ATTR_BOOST, 0xFF)))
            raw.append(int(data.get(ATTR_TEMPEL, 0xFF)))
            raw.append(int(data.get(ATTR_TEMPSOUF, 0xFF)))
            raw.append(int(data.get(ATTR_TEMPHYD, 0xFF)))
            raw.append(int(data.get(ATTR_TEMPSOL, 0xFF)))
            raw.append(int(data.get(ATTR_COMMAND, 0xFF)))
        elif command_type == 1:
            raw = [0x07, 0x91]
            hour = data.get(ATTR_HOUR, "")
            raw += self._hex_string_to_bytes(hour, expected_bytes=2)
        elif command_type == 2:
            raw = [0x07, 0x92]
            agenda = data.get(ATTR_AGENDA, "")
            raw += self._hex_string_to_bytes(agenda, expected_bytes=8)
        else:
            raise ValueError("Unsupported VMI command type %s" % command_type)

        packet = self._build_esp3_packet(raw, sender_bytes, destination_bytes)
        if self._serial is None or not self._serial.is_open:
            raise ConnectionError("Serial port is not open")

        self._serial.write(bytes(packet))
        self._last_command = {
            ATTR_COMMAND_TYPE: command_type,
            ATTR_DESTINATION: destination,
            ATTR_SENDER: sender,
            ATTR_RAW: _bytes_to_hex(raw),
        }

    def _hex_string_to_bytes(self, hex_string, expected_bytes=0):
        if not hex_string:
            return [0xFF] * expected_bytes
        value = hex_string.replace(" ", "").replace(":", "")
        if len(value) != expected_bytes * 2:
            raise ValueError("Raw field must contain %s hex bytes" % expected_bytes)
        return [int(value[i : i + 2], 16) for i in range(0, len(value), 2)]

    def _build_esp3_packet(self, data, sender, destination):
        packet_type = 0x01
        optional = [0x03] + destination + [0xFF, 0x00]
        payload = [0xD1] + data + sender + [0x80]
        header = [0x55, (len(payload) >> 8) & 0xFF, len(payload) & 0xFF, len(optional), packet_type]
        header.append(_crc8(header[1:5]))
        packet = header + payload + optional
        packet.append(_crc8(payload + optional))
        return packet
