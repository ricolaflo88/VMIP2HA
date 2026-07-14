#!/bin/bash

logger_write(){
    NOW=$(date +"%Y-%m-%d %T")
    echo "[$NOW][$1] : $2"
}

logger_write "INFO" "Lancement de la synchronisation des configurations"
logger_write "INFO" "Déplacement dans le répertoire de travail"
cd /tmp
logger_write "INFO" "Nettoyage du répertoire de travail"
sudo rm -rf /tmp/plugin-openenocean > /dev/null 2>&1
logger_write "INFO" "Récupération des sources (cette étape peut durer quelques minutes)"
sudo git clone --depth=1 https://github.com/jeedom/plugin-openenocean.git
if [ $? -ne 0 ]; then
    logger_write "ERROR" "Unable to fetch Plugin-openenocean git. Please check your internet connexion and github access"
    exit 1
fi

BASEDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
logger_write "INFO" "Suppression des configurations Jeedom existantes"
sudo rm -rf ${BASEDIR}/../core/config/devices/*

logger_write "INFO" "Copie des nouvelles configurations Jeedom"
cd /tmp/plugin-openenocean/core/config/devices
sudo mv * ${BASEDIR}/../core/config/devices/

logger_write "INFO" "Suppression des profils existants"
sudo rm -rf ${BASEDIR}/../resources/openenoceand/enocean/eep/*

logger_write "INFO" "Copie des nouveaux profils"
cd /tmp/plugin-openenocean/resources/openenoceand/enocean/eep
sudo mv * ${BASEDIR}/../resources/openenoceand/enocean/eep/

logger_write "INFO" "Nettoyage du répertoire de travail"
sudo rm -R /tmp/plugin-openenocean
sudo chown -R www-data:www-data ${BASEDIR}/../resources/openenoceand/enocean/eep/
sudo chown -R www-data:www-data ${BASEDIR}/../core/config/devices/

logger_write "INFO" "Vos configurations sont maintenant à jour !"
