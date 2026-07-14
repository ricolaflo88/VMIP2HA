PROGRESS_FILE=/tmp/dependancy_openenocean_in_progress
if [ ! -z $1 ]; then
	PROGRESS_FILE=$1
fi
touch ${PROGRESS_FILE}
echo 0 > ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation des dépendances             *"
echo "********************************************************"
sudo apt-get update
echo 50 > ${PROGRESS_FILE}
sudo apt-get remove -y python-enum
echo 60 > ${PROGRESS_FILE}
sudo apt-get install -y python3-dev build-essential python3-requests python3-serial python3-pyudev
echo 70 > ${PROGRESS_FILE}
sudo pip3 install setuptools
echo 72 > ${PROGRESS_FILE}
sudo pip3 install wheel
echo 75 > ${PROGRESS_FILE}
sudo pip3 install enum-compat
echo 85 > ${PROGRESS_FILE}
sudo apt remove -f -y --purge python-bs4
sudo pip3 install beautifulsoup4
echo 100 > ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
rm ${PROGRESS_FILE}
