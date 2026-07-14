import logging

import voluptuous as vol
from homeassistant.helpers import config_validation as cv

from .const import (
    ATTR_AGENDA,
    ATTR_BOOST,
    ATTR_COMMAND,
    ATTR_COMMAND_TYPE,
    ATTR_DESTINATION,
    ATTR_FONC,
    ATTR_HOUR,
    ATTR_MODEFONC,
    ATTR_SENDER,
    ATTR_TEMPEL,
    ATTR_TEMPHYD,
    ATTR_TEMPSOL,
    ATTR_TEMPSOUF,
    ATTR_VACS,
    DOMAIN,
    SERVICE_SEND_VMI_COMMAND,
)

_LOGGER = logging.getLogger(__name__)

VMI_COMMAND_SCHEMA = vol.Schema(
    {
        vol.Required(ATTR_SENDER): cv.string,
        vol.Required(ATTR_DESTINATION): cv.string,
        vol.Required(ATTR_COMMAND_TYPE): vol.All(vol.Coerce(int), vol.In([0, 1, 2])),
        vol.Optional(ATTR_MODEFONC): vol.Coerce(int),
        vol.Optional(ATTR_FONC): vol.Coerce(int),
        vol.Optional(ATTR_VACS): vol.Coerce(int),
        vol.Optional(ATTR_BOOST): vol.Coerce(int),
        vol.Optional(ATTR_TEMPEL): vol.Coerce(int),
        vol.Optional(ATTR_TEMPSOUF): vol.Coerce(int),
        vol.Optional(ATTR_TEMPHYD): vol.Coerce(int),
        vol.Optional(ATTR_TEMPSOL): vol.Coerce(int),
        vol.Optional(ATTR_COMMAND): vol.Coerce(int),
        vol.Optional(ATTR_HOUR): cv.string,
        vol.Optional(ATTR_AGENDA): cv.string,
    }
)


async def async_register_services(hass, hub):
    def handle_send_vmi_command(service_call):
        try:
            hub.send_vmi_command(**service_call.data)
        except Exception as exc:
            _LOGGER.error("Error sending VMI command: %s", exc)
            raise

    hass.services.async_register(
        DOMAIN,
        SERVICE_SEND_VMI_COMMAND,
        handle_send_vmi_command,
        schema=VMI_COMMAND_SCHEMA,
    )
