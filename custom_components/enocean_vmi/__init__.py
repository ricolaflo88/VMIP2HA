import logging
from types import SimpleNamespace

import voluptuous as vol

try:
    from homeassistant.const import CONF_PORT, CONF_BAUDRATE, CONF_DEVICES, EVENT_HOMEASSISTANT_STOP
    from homeassistant.helpers import config_validation as cv
    from homeassistant.helpers.discovery import async_load_platform
except ImportError:  # pragma: no cover - used for local test execution
    CONF_PORT = "port"
    CONF_BAUDRATE = "baudrate"
    CONF_DEVICES = "devices"
    EVENT_HOMEASSISTANT_STOP = "EVENT_HOMEASSISTANT_STOP"
    cv = SimpleNamespace(string=str, positive_int=int, ensure_list=lambda value: value if isinstance(value, list) else [value])

    async def async_load_platform(hass, platform_name, domain, config, discovery_info=None):
        return True

from homeassistant.helpers import issue_registry as ir

DOMAIN = "enocean_vmi"
CONF_SERIAL_PORT = "serial_port"
CONF_DEVICE_FILE = "device_file"
CONF_DEVICE_NAME = "name"
CONF_DEVICE_SENDER = "sender"
CONF_DEVICE_PROFILE = "profile"
CONF_DEVICE_DESTINATION = "destination"
DEFAULT_BAUDRATE = 57600
SUPPORTED_PROFILES = ["A5_04_01", "A5_09_04", "D1079", "D1_07_09", "D2_01_12"]

_LOGGER = logging.getLogger(__name__)


def normalize_profile(profile):
    if not isinstance(profile, str):
        return "D1079"
    value = profile.strip().replace("-", "_").upper()
    mapping = {
        "A5_09_04": "A5_09_04",
        "A5_04_01": "A5_04_01",
        "D1_07_09": "D1_07_09",
        "D2_01_12": "D2_01_12",
        "D1079": "D1079",
        "D1079_01_00": "D1079",
        "D1079_00_00": "D1079",
    }
    return mapping.get(value, value)


def normalize_sender(value):
    if value is None:
        return None
    if isinstance(value, int):
        value = f"{value:08X}"
    if isinstance(value, str):
        value = value.strip().replace("0x", "").replace("0X", "")
        if len(value) == 8 and all(c in "0123456789ABCDEFabcdef" for c in value):
            return value.upper()
        if len(value) == 10 and value.startswith("0"):
            return value[1:].upper()
    return None


class EnOceanVMIHub:
    def __init__(self, hass, serial_port, baudrate, devices):
        self.hass = hass
        self.serial_port = serial_port
        self.baudrate = baudrate
        self._device_configs = {}
        self._listeners = []
        self._states = {}
        self._last_command = None
        self._serial_communicator = None
        self._mqtt_subscription = None

        for device in devices:
            if not device:
                continue
            sender = normalize_sender(device[CONF_DEVICE_SENDER])
            if not sender:
                continue
            self._device_configs[sender] = {
                CONF_DEVICE_NAME: device[CONF_DEVICE_NAME],
                CONF_DEVICE_PROFILE: normalize_profile(device[CONF_DEVICE_PROFILE]),
                CONF_DEVICE_DESTINATION: device.get(CONF_DEVICE_DESTINATION),
            }

    def add_listener(self, callback):
        self._listeners.append(callback)
        return lambda: self._listeners.remove(callback) if callback in self._listeners else None

    def update_state(self, sender, updates):
        state = self._states.setdefault(sender, {})
        state.update(updates)
        for listener in list(self._listeners):
            try:
                listener(sender, updates)
            except Exception:  # pragma: no cover - defensive
                _LOGGER.exception("Error while notifying entity listener")

    async def async_setup(self):
        await self._async_setup_mqtt()
        await self._async_setup_serial()

    async def _async_setup_mqtt(self):
        try:
            from homeassistant.components import mqtt
        except ImportError:
            return
        try:
            self._mqtt_subscription = await mqtt.async_subscribe(self.hass, "enoceanmqtt/#", self._handle_mqtt_message)
        except Exception as err:  # pragma: no cover - defensive
            _LOGGER.debug("Unable to subscribe to EnOcean MQTT topic: %s", err)

    async def _async_setup_serial(self):
        if not self.serial_port:
            return
        try:
            from enocean.communicator import SerialCommunicator
        except ImportError:
            _LOGGER.debug("enocean library not available; serial listener not started")
            return

        try:
            self._serial_communicator = SerialCommunicator(self.serial_port, self._handle_package, baudrate=self.baudrate)
            self._serial_communicator.start()
        except Exception as err:  # pragma: no cover - defensive
            _LOGGER.debug("Unable to start EnOcean serial listener on %s: %s", self.serial_port, err)

    def _handle_package(self, packet):
        sender = None
        payload = None
        if hasattr(packet, "sender"):
            sender = normalize_sender(getattr(packet, "sender"))
        elif hasattr(packet, "sender_id"):
            sender = normalize_sender(getattr(packet, "sender_id"))
        if hasattr(packet, "data"):
            payload = getattr(packet, "data")
        elif hasattr(packet, "payload"):
            payload = getattr(packet, "payload")
        elif hasattr(packet, "telegram"):
            payload = getattr(packet, "telegram")
        if sender:
            self.update_state(sender, {"raw": payload})

    def _handle_mqtt_message(self, topic, payload, qos):
        if not isinstance(payload, (dict, str, bytes, bytearray)):
            return
        sender = None
        for device_sender in self._device_configs:
            if device_sender.lower() in topic.lower() or device_sender.lower() in str(payload).lower():
                sender = device_sender
                break
        if sender is None:
            sender = self._find_sender_from_topic(topic)
        self._apply_payload(sender, payload)

    def _find_sender_from_topic(self, topic):
        lowered = topic.lower()
        for sender in self._device_configs:
            if sender.lower() in lowered:
                return sender
        return None

    def _apply_payload(self, sender, payload):
        if sender is None:
            return
        if isinstance(payload, (bytes, bytearray)):
            self.update_state(sender, {"raw": payload})
            return
        if isinstance(payload, str):
            try:
                import json
                payload = json.loads(payload)
            except Exception:
                payload = {"raw": payload}
        if not isinstance(payload, dict):
            return
        updates = {}
        lowered = {str(key).lower(): value for key, value in payload.items()}
        if "hum" in lowered:
            updates["humidity"] = float(lowered["hum"])
        if "tmp" in lowered:
            updates["temperature"] = float(lowered["tmp"])
        if "conc" in lowered:
            updates["co2"] = float(lowered["conc"])
        if "temp" in lowered:
            updates["temperature"] = float(lowered["temp"])
        if "batt" in lowered:
            updates["battery"] = int(lowered["batt"])
        if "mode" in lowered:
            updates["mode"] = str(lowered["mode"])
        if "raw" in lowered:
            updates["raw"] = lowered["raw"]
        if updates:
            self.update_state(sender, updates)

    async def async_stop(self):
        if self._serial_communicator is not None:
            try:
                self._serial_communicator.stop()
            except Exception:  # pragma: no cover - defensive
                pass
        self._serial_communicator = None


def _build_device_config(device):
    return {
        CONF_DEVICE_NAME: device[CONF_DEVICE_NAME],
        CONF_DEVICE_SENDER: device[CONF_DEVICE_SENDER],
        CONF_DEVICE_PROFILE: normalize_profile(device[CONF_DEVICE_PROFILE]),
        CONF_DEVICE_DESTINATION: device.get(CONF_DEVICE_DESTINATION),
    }


DEVICE_SCHEMA = vol.Schema(
    {
        vol.Required(CONF_DEVICE_NAME): cv.string,
        vol.Required(CONF_DEVICE_SENDER): cv.string,
        vol.Required(CONF_DEVICE_PROFILE): cv.string,
        vol.Optional(CONF_DEVICE_DESTINATION): cv.string,
    }
)

CONFIG_SCHEMA = vol.Schema(
    {
        DOMAIN: vol.Schema(
            {
                vol.Optional(CONF_SERIAL_PORT, default="/dev/ttyS2"): cv.string,
                vol.Optional(CONF_DEVICE_FILE): cv.string,
                vol.Optional(CONF_BAUDRATE, default=DEFAULT_BAUDRATE): cv.positive_int,
                vol.Optional(CONF_DEVICES, default=[]): vol.All(cv.ensure_list, [DEVICE_SCHEMA]),
            }
        )
    },
    extra=vol.ALLOW_EXTRA,
)


async def async_setup(hass, config):
    if DOMAIN not in config:
        return True

    conf = config[DOMAIN]
    serial_port = conf.get(CONF_SERIAL_PORT, "/dev/ttyS2")
    baudrate = conf[CONF_BAUDRATE]
    devices = conf[CONF_DEVICES]

    hub = EnOceanVMIHub(hass, serial_port, baudrate, devices)
    await hub.async_setup()
    hass.data[DOMAIN] = hub

    hass.bus.async_listen_once(EVENT_HOMEASSISTANT_STOP, lambda event: hass.async_create_task(hub.async_stop()))

    await async_load_platform(hass, "sensor", DOMAIN, {}, config)
    await async_load_platform(hass, "climate", DOMAIN, {}, config)
    return True


async def async_setup_entry(hass, entry):
    serial_port = entry.data.get(CONF_SERIAL_PORT, "/dev/ttyS2")
    baudrate = entry.data.get(CONF_BAUDRATE, DEFAULT_BAUDRATE)
    devices = entry.data.get(CONF_DEVICES, [])
    hub = EnOceanVMIHub(hass, serial_port, baudrate, [_build_device_config(device) for device in devices])
    await hub.async_setup()
    hass.data[DOMAIN] = hub
    await async_load_platform(hass, "sensor", DOMAIN, {}, entry.data)
    await async_load_platform(hass, "climate", DOMAIN, {}, entry.data)
    return True


async def async_unload_entry(hass, entry):
    hub = hass.data.get(DOMAIN)
    if hub is not None:
        await hub.async_stop()
    hass.data.pop(DOMAIN, None)
    return True
