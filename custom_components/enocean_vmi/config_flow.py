import logging

import voluptuous as vol

from homeassistant import config_entries
from homeassistant.helpers import config_validation as cv

from . import CONF_BAUDRATE, CONF_DEVICE_FILE, CONF_DEVICE_NAME, CONF_DEVICE_PROFILE, CONF_DEVICE_SENDER, CONF_DEVICES, CONF_SERIAL_PORT, DEFAULT_BAUDRATE, DOMAIN

_LOGGER = logging.getLogger(__name__)


class EnOceanVMIConfigFlow(config_entries.ConfigFlow, domain=DOMAIN):
    VERSION = 1

    async def async_step_user(self, user_input=None):
        if user_input is not None:
            return self.async_create_entry(title="EnOcean VMI", data=user_input)

        schema = vol.Schema(
            {
                vol.Optional(CONF_SERIAL_PORT, default="/dev/ttyS2"): cv.string,
                vol.Optional(CONF_BAUDRATE, default=DEFAULT_BAUDRATE): cv.positive_int,
                vol.Optional(CONF_DEVICE_FILE): cv.string,
                vol.Optional(CONF_DEVICES, default=[]): vol.All(cv.ensure_list, [vol.Schema({
                    vol.Required(CONF_DEVICE_NAME): cv.string,
                    vol.Required(CONF_DEVICE_SENDER): cv.string,
                    vol.Required(CONF_DEVICE_PROFILE): cv.string,
                })]),
            }
        )
        return self.async_show_form(step_id="user", data_schema=schema)
