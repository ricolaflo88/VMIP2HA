import logging
from types import SimpleNamespace

import voluptuous as vol

try:
    from homeassistant.const import EVENT_HOMEASSISTANT_STOP
    from homeassistant.helpers import config_validation as cv
    from homeassistant.helpers.discovery import async_load_platform
except ImportError:  # pragma: no cover - used for local test execution
    EVENT_HOMEASSISTANT_STOP = "EVENT_HOMEASSISTANT_STOP"
    cv = SimpleNamespace(string=str, positive_int=int, ensure_list=lambda value: value if isinstance(value, list) else [value])

    async def async_load_platform(hass, platform_name, domain, config, discovery_info=None):
        return True

from .const import (
    ATTR_COMMAND_TYPE,
    ATTR_DESTINATION,
    ATTR_DEVICE_NAME,
    ATTR_DEVICE_PROFILE,
    ATTR_DEVICE_SENDER,
    ATTR_SENDER,
    CONF_BAUDRATE,
    CONF_DEVICE_FILE,
    CONF_DEVICE_NAME as CONF_DEVICE_NAME_CONST,
    CONF_DEVICE_PROFILE as CONF_DEVICE_PROFILE_CONST,
    CONF_DEVICE_SENDER as CONF_DEVICE_SENDER_CONST,
    CONF_DEVICES,
    CONF_SERIAL_PORT,
    DEFAULT_BAUDRATE,
    DOMAIN,
    SERVICE_SEND_VMI_COMMAND,
    SUPPORTED_PROFILES,
)
from .hub import EnOceanHub, load_devices_from_file
from .services import async_register_services

_LOGGER = logging.getLogger(__name__)

DEVICE_SCHEMA = vol.Schema(
    {
        vol.Required(CONF_DEVICE_NAME_CONST): cv.string,
        vol.Required(CONF_DEVICE_SENDER_CONST): cv.string,
        vol.Required(CONF_DEVICE_PROFILE_CONST): vol.In(SUPPORTED_PROFILES),
        vol.Optional("destination"): cv.string,
    }
)

CONFIG_SCHEMA = vol.Schema(
    {
        DOMAIN: vol.Schema(
            {
                vol.Optional(CONF_SERIAL_PORT): cv.string,
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
    serial_port = conf.get(CONF_SERIAL_PORT)
    baudrate = conf[CONF_BAUDRATE]
    devices = conf[CONF_DEVICES]

    if not devices and CONF_DEVICE_FILE in conf:
        loaded_devices = load_devices_from_file(conf[CONF_DEVICE_FILE])
        devices = [
            {
                CONF_DEVICE_NAME_CONST: entry["name"],
                CONF_DEVICE_SENDER_CONST: entry["sender"],
                CONF_DEVICE_PROFILE_CONST: entry["profile"],
            }
            for entry in loaded_devices
        ]

    hub = EnOceanHub(hass, serial_port, baudrate, devices)
    hub.start()
    hass.data[DOMAIN] = hub

    async_register_services(hass, hub)
    hass.bus.async_listen_once(EVENT_HOMEASSISTANT_STOP, lambda event: hub.stop())

    await hub.async_setup_mqtt()
    hass.async_create_task(async_load_platform(hass, "sensor", DOMAIN, {}, config))
    return True
