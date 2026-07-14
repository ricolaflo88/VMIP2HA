import logging

from homeassistant.components.sensor import SensorEntity
from homeassistant.helpers.icon import icon_for_battery_level

from .const import (
    ATTR_BATTERY,
    ATTR_CO2,
    ATTR_HUMIDITY,
    ATTR_LAST_COMMAND,
    ATTR_SIGNAL_STRENGTH,
    ATTR_TEMPERATURE,
    CONF_DEVICE_NAME,
    CONF_DEVICE_PROFILE,
    CONF_DEVICE_SENDER,
    CONF_DEVICE_DESTINATION,
    DOMAIN,
)

_LOGGER = logging.getLogger(__name__)


SENSOR_DEFINITIONS = {
    "A5_04_01": [
        {"key": ATTR_TEMPERATURE, "device_class": "temperature", "unit": "°C"},
        {"key": ATTR_HUMIDITY, "device_class": "humidity", "unit": "%"},
    ],
    "A5_09_04": [
        {"key": ATTR_TEMPERATURE, "device_class": "temperature", "unit": "°C"},
        {"key": ATTR_HUMIDITY, "device_class": "humidity", "unit": "%"},
        {"key": ATTR_CO2, "device_class": "carbon_dioxide", "unit": "ppm"},
    ],
    "D1079": [
        {"key": ATTR_TEMPERATURE, "device_class": "temperature", "unit": "°C"},
        {"key": ATTR_HUMIDITY, "device_class": None, "unit": "%"},
        {"key": ATTR_BATTERY, "device_class": "battery", "unit": "%"},
    ],
}


async def async_setup_platform(hass, config, async_add_entities, discovery_info=None):
    hub = hass.data.get(DOMAIN)
    if not hub:
        return

    entities = []
    for sender_hex, device in hub._device_configs.items():
        profile = device[CONF_DEVICE_PROFILE]
        name = device[CONF_DEVICE_NAME]
        destination = device.get(CONF_DEVICE_DESTINATION)
        for sensor_def in SENSOR_DEFINITIONS.get(profile, []):
            entities.append(
                EnOceanSensorEntity(hub, sender_hex, name, profile, destination, sensor_def)
            )

    entities.append(LastCommandSensor(hub))
    async_add_entities(entities, True)


class EnOceanSensorEntity(SensorEntity):
    def __init__(self, hub, sender_hex, name, profile, destination, sensor_def):
        self._hub = hub
        self._sender_hex = sender_hex
        self._device_name = name
        self._profile = profile
        self._destination = destination
        self._sensor_key = sensor_def["key"]
        self._device_class = sensor_def["device_class"]
        self._unit = sensor_def["unit"]
        self._state = None
        self._attrs = {}
        self._remove_listener = None

    @property
    def name(self):
        return f"{self._device_name} {self._sensor_key.replace('_', ' ').title()}"

    @property
    def unique_id(self):
        return f"{self._sender_hex}_{self._sensor_key}"

    @property
    def native_value(self):
        return self._state

    @property
    def native_unit_of_measurement(self):
        return self._unit

    @property
    def device_class(self):
        return self._device_class

    @property
    def extra_state_attributes(self):
        return self._attrs

    async def async_added_to_hass(self):
        self._remove_listener = self._hub.add_listener(self._handle_update)
        state = self._hub._states.get(self._sender_hex, {})
        self._state = state.get(self._sensor_key)
        self._attrs["sender"] = self._sender_hex
        self._attrs["profile"] = self._profile
        self._attrs["destination"] = self._destination
        self._attrs["signal_strength"] = state.get(ATTR_SIGNAL_STRENGTH)

    async def async_will_remove_from_hass(self):
        if self._remove_listener:
            self._remove_listener()

    async def _handle_update(self, sender, updates):
        if sender != self._sender_hex:
            return
        if self._sensor_key not in updates and ATTR_SIGNAL_STRENGTH not in updates:
            return

        if self._sensor_key in updates:
            self._state = updates[self._sensor_key]
        self._attrs["signal_strength"] = updates.get(ATTR_SIGNAL_STRENGTH, self._attrs.get("signal_strength"))
        self.async_write_ha_state()


class LastCommandSensor(SensorEntity):
    def __init__(self, hub):
        self._hub = hub
        self._state = None
        self._attrs = {}

    @property
    def name(self):
        return "EnOcean VMI Last Command"

    @property
    def unique_id(self):
        return "enocran_vmi_last_command"

    @property
    def native_value(self):
        return self._state

    @property
    def extra_state_attributes(self):
        return self._attrs

    async def async_added_to_hass(self):
        self._state = None
        self._attrs = {}

    async def async_update(self):
        if self._hub._last_command is None:
            return
        self._state = self._hub._last_command.get("raw")
        self._attrs = self._hub._last_command.copy()
