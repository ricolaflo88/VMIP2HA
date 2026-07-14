# Installation Home Assistant

## 1. Copier le composant
Depuis la machine Home Assistant, exécutez :

```bash
chmod +x /workspaces/VMIP2HA/install_homeassistant.sh
/workspaces/VMIP2HA/install_homeassistant.sh /config/custom_components
```

Si vous êtes déjà dans le dépôt local, vous pouvez aussi copier directement :

```bash
cp -r /workspaces/VMIP2HA/custom_components/enocran_vmi /config/custom_components/
```

## 2. Déposer les fichiers de configuration
Assurez-vous que les fichiers suivants sont présents dans /config :

- /config/enoceanmqtt.devices
- /config/mappingV2.yaml
- /config/EEPv2.xml

Vous pouvez les copier depuis ce dépôt :

```bash
cp /workspaces/VMIP2HA/enoceanmqtt.devices /config/enoceanmqtt.devices
cp /workspaces/VMIP2HA/mappingV2.yaml /config/mappingV2.yaml
cp /workspaces/VMIP2HA/EEPv2.xml /config/EEPv2.xml
```

## 3. Ajouter la configuration Home Assistant
Ajoutez ce qui suit à configuration.yaml :

```yaml
# Voir homeassistant_example_configuration.yaml
```

## 4. Redémarrer Home Assistant
Après le redémarrage, l’intégration doit apparaître dans la section Intégrations.
