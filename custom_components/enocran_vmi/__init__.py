import logging

import voluptuous as vol
from homeassistant.const import EVENT_HOMEASSISTANT_STOP
from homeassistant.helpers import config_validation as cv
from homeassistant.helpers.discovery import async_load_platform

from .const import (
    ATTR_COMMAND_TYPE,
    ATTR_DESTINATION,
    ATTR_DEVICE_NAME,
    ATTR_DEVICE_PROFILE,
    ATTR_DEVICE_SENDER,
    ATTR_SENDER,
    CONF_BAUDRATE,
    CONF_DEVICES,
    CONF_SERIAL_PORT,
    DEFAULT_BAUDRATE,
    DOMAIN,
    SERVICE_SEND_VMI_COMMAND,
    SUPPORTED_PROFILES,
)
from .hub import EnOceanHub
from .services import async_register_services

_LOGGER = logging.getLogger(__name__)

DEVICE_SCHEMA = vol.Schema(
    {
        vol.Required(CONF_DEVICE_NAME): cv.string,
        vol.Required(CONF_DEVICE_SENDER): cv.string,
        vol.Required(CONF_DEVICE_PROFILE): vol.In(SUPPORTED_PROFILES),
        vol.Optional("destination"): cv.string,
    }
)

CONFIG_SCHEMA = vol.Schema(
    {
        DOMAIN: vol.Schema(
            {
                vol.Required(CONF_SERIAL_PORT): cv.string,
                vol.Optional(CONF_BAUDRATE, default=DEFAULT_BAUDRATE): cv.positive_int,
                vol.Optional(CONF_DEVICES, default=[]): vol.All(cv.ensure_list, [DEVICE_SCHEMA]),
            }
        )
    },
    extra=vol.ALLOW_EXTRA,
)


def async_setup(hass, config):
    if DOMAIN not in config:
        return True

    conf = config[DOMAIN]
    serial_port = conf[CONF_SERIAL_PORT]
    baudrate = conf[CONF_BAUDRATE]
    devices = conf[CONF_DEVICES]

    hub = EnOceanHub(hass, serial_port, baudrate, devices)
    hub.start()
    hass.data[DOMAIN] = hub

    async_register_services(hass, hub)
    hass.bus.async_listen_once(EVENT_HOMEASSISTANT_STOP, lambda event: hub.stop())

    hass.async_create_task(async_load_platform(hass, "sensor", DOMAIN, {}, config))
    return True
