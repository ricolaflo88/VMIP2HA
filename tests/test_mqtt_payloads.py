from pathlib import Path

from custom_components.enocran_vmi.hub import build_updates_from_mqtt_payload, load_devices_from_file


def test_a5_09_04_payload_mapping():
    updates = build_updates_from_mqtt_payload(
        "enoceanmqtt/Sensor/C02_VMI",
        {"HUM": 71.5, "Conc": 1120.0, "TMP": 27.6, "HSN": 1, "TSN": 1},
    )

    assert updates["humidity"] == 71.5
    assert updates["co2"] == 1120.0
    assert updates["temperature"] == 27.6


def test_d1079_payload_mapping():
    updates = build_updates_from_mqtt_payload(
        "enoceanmqtt/Sensor/T_VMI",
        {"Batt": 5, "TEMP": 28.22, "HUM": 63.0},
    )

    assert updates["battery"] == 5
    assert updates["temperature"] == 28.22
    assert updates["humidity"] == 63.0


def test_devices_file_parser():
    devices = load_devices_from_file(Path("/workspaces/VMIP2HA/enoceanmqtt.devices"))

    names = {device["name"] for device in devices}
    assert "Sensor/C02_VMI" in names
    assert any(device["name"] == "Sensor/C02_VMI" and device["profile"] == "A5_09_04" for device in devices)
    assert any(device["name"] == "Sensor/SDB_VMI" and device["profile"] == "A5_04_01" for device in devices)
    assert any(device["name"] == "Sensor/T_VMI" and device["profile"] == "D1_07_09" for device in devices)
