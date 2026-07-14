import logging

try:
    from homeassistant.components.climate import ClimateEntity
    from homeassistant.components.climate.const import HVACMode
except ImportError:  # pragma: no cover - used for local test execution
    class ClimateEntity:  # type: ignore
        pass

    class HVACMode:  # type: ignore
        AUTO = "auto"
        OFF = "off"
        HEAT = "heat"

from . import CONF_DEVICE_NAME, CONF_DEVICE_PROFILE, CONF_DEVICE_SENDER, DOMAIN

_LOGGER = logging.getLogger(__name__)

CLIMATE_PROFILES = {"D1079", "D2_01_12"}


async def async_setup_platform(hass, config, async_add_entities, discovery_info=None):
    hub = hass.data.get(DOMAIN)
    if not hub:
        return

    entities = []
    for sender_hex, device in hub._device_configs.items():
        profile = device[CONF_DEVICE_PROFILE]
        if profile in CLIMATE_PROFILES:
            entities.append(EnOceanClimateEntity(hub, sender_hex, device[CONF_DEVICE_NAME], profile))
    async_add_entities(entities, True)


class EnOceanClimateEntity(ClimateEntity):
    def __init__(self, hub, sender_hex, name, profile):
        self._hub = hub
        self._sender_hex = sender_hex
        self._device_name = name
        self._profile = profile
        self._state = {}
        self._remove_listener = None

    @property
    def name(self):
        return self._device_name

    @property
    def unique_id(self):
        return f"{self._sender_hex}_climate"

    @property
    def current_temperature(self):
        return self._state.get("temperature")

    @property
    def target_temperature(self):
        return self._state.get("temperature")

    @property
    def hvac_mode(self):
        if self._state.get("mode") == "off":
            return HVACMode.OFF
        return HVACMode.AUTO

    @property
    def hvac_modes(self):
        return [HVACMode.AUTO, HVACMode.OFF]

    @property
    def preset_mode(self):
        return self._state.get("mode")

    @property
    def preset_modes(self):
        return ["auto", "boost", "eco", "<uncertain>"]

    @property
    def extra_state_attributes(self):
        return {
            "sender": self._sender_hex,
            "profile": self._profile,
            "raw": self._state.get("raw"),
        }

    async def async_added_to_hass(self):
        self._remove_listener = self._hub.add_listener(self._handle_update)
        self._state = self._hub._states.get(self._sender_hex, {})

    async def async_will_remove_from_hass(self):
        if self._remove_listener:
            self._remove_listener()

    def _handle_update(self, sender, updates):
        if sender != self._sender_hex:
            return
        self._state.update(updates)
        self.async_write_ha_state()
