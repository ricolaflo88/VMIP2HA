<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */

class openenocean extends eqLogic {
	/*     * ***********************Methode static*************************** */
	
	public static function cron() {
		$eqLogics = eqLogic::byType('openenocean');
		foreach ($eqLogics as $eqLogic){
			if ($eqLogic->getConfiguration('device') == 'a5-20-01'){
				$eqLogic->computeTherm();
			}
		}
	}
	
	public static function createFromDef($_def) {
		event::add('jeedom::alert', array(
			'level' => 'warning',
			'page' => 'openenocean',
			'message' => __('Nouveau module detecté', __FILE__),
		));
		$banId = explode(' ', config::byKey('banId', 'openenocean'));
		if (in_array($_def['id'], $banId)) {
			event::add('jeedom::alert', array(
				'level' => 'danger',
				'page' => 'openenocean',
				'message' => __('Le module a un id banni. Inclusion impossible', __FILE__),
			));
			return false;
		}
		if (!isset($_def['rorg']) || !isset($_def['func']) || !isset($_def['type'])) {
			log::add('openenocean', 'error', 'Information manquante pour ajouter l\'équipement : ' . print_r($_def, true));
			event::add('jeedom::alert', array(
				'level' => 'danger',
				'page' => 'openenocean',
				'message' => __('Information manquante pour ajouter l\'équipement. Inclusion impossible', __FILE__),
			));
			return false;
		}
		$device_type = $_def['rorg'] . '-' . $_def['func'] . '-' . $_def['type'];
		$device = self::devicesParameters($device_type);
		$openenocean = openenocean::byLogicalId($_def['id'], 'openenocean');
		if (!is_object($openenocean)) {
			$eqLogic = new openenocean();
			$eqLogic->setName($_def['id']);
		}
		$eqLogic->setLogicalId($_def['id']);
		$eqLogic->setEqType_name('openenocean');
		$eqLogic->setIsEnable(1);
		$eqLogic->setIsVisible(1);
		$eqLogic->setConfiguration('device', $device_type);
		$eqLogic->setConfiguration('rorg', $_def['rorg']);
		$eqLogic->setConfiguration('func', $_def['func']);
		$eqLogic->setConfiguration('type', $_def['type']);
		$model = $eqLogic->getModelListParam();
		$default ='';
		if (isset($device['configuration']['default'])){
			$default =$device['configuration']['default'];
		}
		if ($default != '') {
			$eqLogic->setConfiguration('iconModel', $default);
		}
		else if (count($model) > 0) {
			$eqLogic->setConfiguration('iconModel', array_keys($model[0])[0]);
		}
		$eqLogic->save();
		
		event::add('jeedom::alert', array(
			'level' => 'warning',
			'page' => 'openenocean',
			'message' => __('Module inclu avec succès', __FILE__),
		));
		return $eqLogic;
	}
	
	public static function devicesParameters($_device = '') {
		$return = array();
		foreach (ls(dirname(__FILE__) . '/../config/devices', '*') as $dir) {
			$path = dirname(__FILE__) . '/../config/devices/' . $dir;
			if (!is_dir($path)) {
				continue;
			}
			$files = ls($path, '*.json', false, array('files', 'quiet'));
			foreach ($files as $file) {
				try {
					$return += is_json(file_get_contents($path . '/' . $file), array());
				} catch (Exception $e) {
					
				}
			}
		}
		if (isset($_device) && $_device != '') {
			if (isset($return[$_device])) {
				return $return[$_device];
			}
			return array();
		}
		return $return;
	}
	
	public static function deamon_info() {
		$return = array();
		$return['log'] = 'openenocean';
		$return['state'] = 'nok';
		$pid_file = jeedom::getTmpFolder('openenocean') . '/deamon.pid';
		if (file_exists($pid_file)) {
			if (@posix_getsid(trim(file_get_contents($pid_file)))) {
				$return['state'] = 'ok';
			} else {
				shell_exec(system::getCmdSudo() . 'rm -rf ' . $pid_file . ' 2>&1 > /dev/null');
			}
		}
		$return['launchable'] = 'ok';
		$port = config::byKey('port', 'openenocean');
		if ($port != 'auto' && $port != 'aruba') {
			$port = jeedom::getUsbMapping($port);
			if (@!file_exists($port)) {
				$return['launchable'] = 'nok';
				$return['launchable_message'] = __('Le port n\'est pas configuré', __FILE__);
			}
			exec(system::getCmdSudo() . 'chmod 777 ' . $port . ' > /dev/null 2>&1');
		}
		return $return;
	}
	
	public static function dependancy_info() {
		$return = array();
		$return['progress_file'] = jeedom::getTmpFolder('openenocean') . '/dependance';
		$return['state'] = 'ok';
		if (exec(system::getCmdSudo() . system::get('cmd_check') . '-E "python3\-serial|python3\-request|python3\-pyudev" | wc -l') < 3) {
			$return['state'] = 'nok';
		}
		if (exec(system::getCmdSudo() . 'pip3 list | grep -E "beautifulsoup4|enum-compat" | wc -l') < 2) {
			$return['state'] = 'nok';
		}
		return $return;
	}
	
	public static function dependancy_install() {
		log::remove(__CLASS__ . '_update');
		return array('script' => dirname(__FILE__) . '/../../resources/install_#stype#.sh ' . jeedom::getTmpFolder('openenocean') . '/dependance', 'log' => log::getPathToLog(__CLASS__ . '_update'));
	}
	
	public static function deamon_start() {
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') {
			throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
		}
		$port = config::byKey('port', 'openenocean');
		if ($port != 'auto' && $port != 'aruba') {
			$port = jeedom::getUsbMapping($port);
		}
		$openenocean_path = realpath(dirname(__FILE__) . '/../../resources/openenoceand');
		$cmd = system::getCmdSudo().'/usr/bin/python3 ' . $openenocean_path . '/openenoceand.py';
		$cmd .= ' --device ' . $port;
		$cmd .= ' --loglevel ' . log::convertLogLevel(log::getLogLevel('openenocean'));
		$cmd .= ' --socketport ' . config::byKey('socketport', 'openenocean');
		$cmd .= ' --callback ' . network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/openenocean/core/php/jeeOpenEnOcean.php';
		$cmd .= ' --apikey ' . jeedom::getApiKey('openenocean');
		$cmd .= ' --cycle ' . config::byKey('cycle', 'openenocean');
		$cmd .= ' --pid ' . jeedom::getTmpFolder('openenocean') . '/deamon.pid';
		log::add('openenocean', 'info', 'Lancement démon openenocean : ' . $cmd);
		$result = exec($cmd . ' >> ' . log::getPathToLog('openenocean') . ' 2>&1 &');
		$i = 0;
		while ($i < 30) {
			$deamon_info = self::deamon_info();
			if ($deamon_info['state'] == 'ok') {
				break;
			}
			sleep(1);
			$i++;
		}
		if ($i >= 30) {
			log::add('openenocean', 'error', 'Impossible de lancer le démon openenocean, vérifiez le port', 'unableStartDeamon');
			return false;
		}
		message::removeAll('openenocean', 'unableStartDeamon');
		sleep(2);
		self::sendIdToDeamon();
		config::save('exclude_mode', 0, 'openenocean');
		config::save('include_mode', 0, 'openenocean');
		return true;
	}
	
	public static function sendIdToDeamon() {
		foreach (self::byType('openenocean') as $eqLogic) {
			$eqLogic->allowDevice();
			$eqLogic->setStatus('lasRepeat0', 0);
			$eqLogic->setStatus('lasRepeat1', 0);
			$eqLogic->setStatus('lasRepeat2', 0);
			usleep(300);
		}
	}
	
	public static function changeLogLive($_level) {
		$value = array('apikey' => jeedom::getApiKey('openenocean'), 'cmd' => $_level);
		$value = json_encode($value);
		$socket = socket_create(AF_INET, SOCK_STREAM, 0);
		socket_connect($socket, '127.0.0.1', config::byKey('socketport', 'openenocean'));
		socket_write($socket, $value, strlen($value));
		socket_close($socket);
	}
	
	public static function deamon_stop() {
		$pid_file = jeedom::getTmpFolder('openenocean') . '/deamon.pid';
		if (file_exists($pid_file)) {
			$pid = intval(trim(file_get_contents($pid_file)));
			system::kill($pid);
		}
		system::kill('openenoceand.py');
		system::fuserk(config::byKey('socketport', 'openenocean'));
		$port = config::byKey('port', 'openenocean');
		if ($port != 'auto') {
			system::fuserk(jeedom::getUsbMapping($port));
		}
		config::save('exclude_mode', 0, 'openenocean');
		config::save('include_mode', 0, 'openenocean');
		sleep(1);
	}
	
	public static function excludedDevice($_logical_id = null) {
		if ($_logical_id !== null && $_logical_id != 0) {
			$eqLogic = eqlogic::byLogicalId($_logical_id, 'openenocean');
			if (is_object($eqLogic)) {
				event::add('jeedom::alert', array(
					'level' => 'warning',
					'page' => 'openenocean',
					'message' => __('Le module ', __FILE__) . $eqLogic->getHumanName() . __(' vient d\'être exclu', __FILE__),
				));
				if (config::byKey('autoRemoveExcludeDevice', 'openenocean') == 1) {
					$eqLogic->remove();
					event::add('openenocean::includeDevice', '');
				}
				sleep(2);
				event::add('jeedom::alert', array(
					'level' => 'warning',
					'page' => 'openenocean',
					'message' => '',
				));
				sleep(1);
				event::add('jeedom::alert', array(
					'level' => 'warning',
					'page' => 'openenocean',
					'message' => '',
				));
				return;
			}
			sleep(1);
			event::add('jeedom::alert', array(
				'level' => 'warning',
				'page' => 'openenocean',
				'message' => '',
			));
		}
		return;
	}
	
	public static function generateEnOceanId() {
		if (config::byKey('baseid', 'openenocean') == '') {
			throw new Exception(__('Le base ID est vide, veuillez relance le démon', __FILE__));
		}
		$eqLogics = self::byType('openenocean');
		$baseId = hexdec(config::byKey('baseid', 'openenocean'));
		for ($i = 0; $i < 128; $i++) {
			$found = false;
			$logicalId = strtoupper(dechex($baseId + $i));
			foreach ($eqLogics as $eqLogic) {
				if ($eqLogic->getLogicalId() == $logicalId) {
					$found = true;
					break;
				}
				if ($eqLogic->getConfiguration('actionid', '0') == $logicalId) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				return $logicalId;
			}
		}
		throw new Exception(__('Impossible de trouver un ID libre (limite à 128 modules)', __FILE__));
	}
	
	public static function changeIncludeState($_state, $_mode, $_type) {
		if ($_type == '' || $_type == 0) {
			if ($_mode == 1) {
				if ($_state == 1) {
					$value = json_encode(array('apikey' => jeedom::getApiKey('openenocean'), 'cmd' => 'learnin'));
				} else {
					$value = json_encode(array('apikey' => jeedom::getApiKey('openenocean'), 'cmd' => 'learnout'));
				}
			} else {
				if ($_state == 1) {
					$value = json_encode(array('apikey' => jeedom::getApiKey('openenocean'), 'cmd' => 'excludein'));
				} else {
					$value = json_encode(array('apikey' => jeedom::getApiKey('openenocean'), 'cmd' => 'excludeout'));
				}
			}
			if (config::byKey('port', 'openenocean', 'none') != 'none') {
				$socket = socket_create(AF_INET, SOCK_STREAM, 0);
				socket_connect($socket, '127.0.0.1', config::byKey('socketport', 'openenocean'));
				socket_write($socket, $value, strlen($value));
				socket_close($socket);
			}
		}
	}
	
	public static function sendSocket($_send) {
		$socket = socket_create(AF_INET, SOCK_STREAM, 0);
		socket_connect($socket, '127.0.0.1', config::byKey('socketport', 'openenocean'));
		socket_write($socket, $_send, strlen($_send));
		socket_close($socket);
	}
	
	/*     * *********************Methode d'instance************************* */
	public function setDeviceConfiguration($_params = '') {
		$profile = array(
			'func' => $this->getConfiguration('func'),
			'rorg' => $this->getConfiguration('rorg'),
			'type' => $this->getConfiguration('type'),
		);
		$params = json_decode($_params, true);
		$saveconfig = [];
		foreach ($params as $key => $value) {
			$saveconfig[$key] = json_decode($value, true)['value'];
		}
		$this->setConfiguration('params', $saveconfig);
		$this->save();
		$pushGroupParam = [];
		foreach ($params as $key => $value) {
			$IOList = explode('|', json_decode($value, true)['channel']);
			$command = json_decode($value, true)['cmd'];
			foreach ($IOList as $IO) {
				$pushGroupParam[intval($IO) . '|' . $command][explode('|', $key)[0]] = json_decode($value, true)['value'];
			}
		}
		foreach ($pushGroupParam as $key => $value) {
			$command = [];
			foreach ($value as $keycommand => $valuecommand) {
				$command[$keycommand] = $valuecommand;
			}
			$command['IO'] = explode('|', $key)[0];
			$command['command'] = explode('|', $key)[1];
			$send = json_encode(array('apikey' => jeedom::getApiKey('openenocean'), 'cmd' => 'send', 'dest' => $this->getLogicalId(), 'profile' => $profile, 'command' => $command));
			$socket = socket_create(AF_INET, SOCK_STREAM, 0);
			socket_connect($socket, '127.0.0.1', config::byKey('socketport', 'openenocean'));
			socket_write($socket, $send, strlen($send));
			socket_close($socket);
		}
		return;
	}
	
	public function getDeviceConfiguration() {
		return $this->getConfiguration('params');
	}
	
	public function getImage() {
		return 'plugins/openenocean/core/config/devices/' . $this->getConfiguration('iconModel') . '.jpg';
	}
	
	public function changeRepeater($_type) {
		$profile = array(
			'func' => $this->getConfiguration('func'),
			'rorg' => $this->getConfiguration('rorg'),
			'type' => $this->getConfiguration('type'),
		);
		$this->setConfiguration('lastRepeat', $_type);
		$send = json_encode(array('apikey' => jeedom::getApiKey('openenocean'), 'cmd' => 'learn', 'dest' => $this->getLogicalId(), 'profile' => $profile, 'type' => 'repeater', 'level' => $_type));
		$socket = socket_create(AF_INET, SOCK_STREAM, 0);
		socket_connect($socket, '127.0.0.1', config::byKey('socketport', 'openenocean'));
		socket_write($socket, $send, strlen($send));
		socket_close($socket);
		$this->save();
		return;
	}
	
	public function remMan($_code, $_type) {
		$send = json_encode(array('apikey' => jeedom::getApiKey('openenocean'), 'cmd' => 'learn', 'dest' => $this->getLogicalId(), 'type' => 'remMan', 'subtype' => $_type, 'code' => $_code));
		self::sendSocket($send);
		return;
	}
	
	public function remCom($_type) {
		$send = json_encode(array('apikey' => jeedom::getApiKey('openenocean'), 'cmd' => 'learn', 'dest' => $this->getLogicalId(), 'type' => 'remMan', 'subtype' => $_type));
		self::sendSocket($send);
		return;
	}
	
	public function computeTherm() {
		log::add('openenocean','debug','Computing Therm for ' . $this->getHumanName());
		$lastdata=$this->getCache('lastdata');
		if (isset($lastdata['TMP'])){
			log::add('openenocean','debug','Last data ' . $lastdata['TMP']);
			$cmdSource = cmd::byId(str_replace('#','',$this->getConfiguration('sourceTemp','')));
			if (!is_object($cmdSource)){
				$cmdSource = $this->getCmd(null, 'TMP::value');
			}
			if (!is_object($cmdSource)){
				$sourceTemp =0;
			} else {
				$sourceTemp = $cmdSource->execCmd();
			}
			$sourceTemp =intval(255 -($sourceTemp*6.375));
			$lastdata['TMP'] = $sourceTemp;
			$profile = array(
			'func' => $this->getConfiguration('func'),
			'rorg' => $this->getConfiguration('rorg'),
			'type' => $this->getConfiguration('type'),
			);
			log::add('openenocean','debug','new data ' . $lastdata['TMP']);
			$value = json_encode(array('apikey' => jeedom::getApiKey('openenocean'), 'cmd' => 'send', 'dest' => $this->getLogicalId(), 'profile' => $profile, 'command' => $lastdata));
			$socket = socket_create(AF_INET, SOCK_STREAM, 0);
			socket_connect($socket, '127.0.0.1', config::byKey('socketport', 'openenocean'));
			socket_write($socket, $value, strlen($value));
			socket_close($socket);
		}
		return;
	}
	
	public function getModelListParam($_conf = '') {
		if ($_conf == '') {
			$_conf = $this->getConfiguration('device');
		}
		$modelList = array();
		$param = false;
		$files = array();
		foreach (ls(dirname(__FILE__) . '/../config/devices', '*') as $dir) {
			if (!is_dir(dirname(__FILE__) . '/../config/devices/' . $dir)) {
				continue;
			}
			$files[$dir] = ls(dirname(__FILE__) . '/../config/devices/' . $dir, $_conf . '_*.jpg', false, array('files', 'quiet'));
			if (file_exists(dirname(__FILE__) . '/../config/devices/' . $dir . $_conf . '.jpg')) {
				$selected = 0;
				if ($dir . $_conf == $this->getConfiguration('iconModel')) {
					$selected = 1;
				}
				$modelList[$dir . $_conf] = array(
					'value' => __('Défaut', __FILE__),
					'selected' => $selected,
				);
			}
			if (count($files[$dir]) == 0) {
				unset($files[$dir]);
			}
		}
		$replace = array(
			$_conf => '',
			'.jpg' => '',
			'_' => ' ',
		);
		foreach ($files as $dir => $images) {
			foreach ($images as $imgname) {
				$selected = 0;
				if ($dir . str_replace('.jpg', '', $imgname) == $this->getConfiguration('iconModel')) {
					$selected = 1;
				}
				$modelList[$dir . str_replace('.jpg', '', $imgname)] = array(
					'value' => ucfirst(trim(str_replace(array_keys($replace), $replace, $imgname))),
					'selected' => $selected,
				);
			}
		}
		$json = self::devicesParameters($_conf);
		if (isset($json['parameters'])) {
			$param = true;
		}
		$remark = false;
		if (isset($json['compatibility'])) {
			foreach ($json['compatibility'] as $compatibility) {
				if ($compatibility['imglink'] == explode('/', $this->getConfiguration('iconModel'))[1]) {
					$remark = $compatibility['remark'] . ' | ' . $compatibility['inclusion'];
					break;
				}
			}
		}
		$hasrepeat = false;
		if (isset($json['configuration'])) {
			if (isset($json['configuration']['hasrepeat']) && $json['configuration']['hasrepeat'] == 1) {
				$hasrepeat = true;
			}
		}
		$hasremcom = false;
		if (isset($json['configuration'])) {
			if (isset($json['configuration']['hasremcom']) && $json['configuration']['hasremcom'] == 1) {
				$hasremcom = true;
			}
		}
		$hasSourceTemp = false;
		if (isset($json['configuration'])) {
			if (isset($json['configuration']['hasSourceTemp']) && $json['configuration']['hasSourceTemp'] == 1) {
				$hasSourceTemp = true;
			}
		}
		return [$modelList, $param, $remark, $hasrepeat, $hasremcom, $hasSourceTemp];
	}
	
	public function preInsert() {
		if ($this->getLogicalId() == '') {
			$this->setLogicalId(self::generateEnOceanId());
		}
	}
	
	public function postSave() {
		if ($this->getConfiguration('actionid') == '') {
			$this->setConfiguration('actionid', self::generateEnOceanId());
			$this->save();
			sleep(1);
		}
		if ($this->getConfiguration('applyDevice') != $this->getConfiguration('device')) {
			$this->applyModuleConfiguration();
		} else {
			$this->allowDevice();
		}
	}
	
	public function preRemove() {
		$this->disallowDevice();
	}
	
	public function allowDevice() {
		$value = array('apikey' => jeedom::getApiKey('openenocean'), 'cmd' => 'add');
		if ($this->getConfiguration('func') != '' && $this->getConfiguration('type') != '' && $this->getConfiguration('rorg') != '') {
			$value['device'] = array(
				'id' => $this->getLogicalId(),
				'profils' => array(
					array(
						'func' => $this->getConfiguration('func'),
						'type' => $this->getConfiguration('type'),
						'rorg' => $this->getConfiguration('rorg'),
						'ignoreRelease' => $this->getConfiguration('ignoreRelease'),
						'allButtons' => $this->getConfiguration('allButtons'),
					),
				),
			);
			if ($this->getConfiguration('rorg2') != '') {
				array_push($value['device']['profils'], array(
					'func' => $this->getConfiguration('func2'),
					'type' => $this->getConfiguration('type2'),
					'rorg' => $this->getConfiguration('rorg2'),
					'ignoreRelease' => $this->getConfiguration('ignoreRelease'),
					'allButtons' => $this->getConfiguration('allButtons'),
				));
			}
			$value = json_encode($value);
			$socket = socket_create(AF_INET, SOCK_STREAM, 0);
			socket_connect($socket, '127.0.0.1', config::byKey('socketport', 'openenocean'));
			socket_write($socket, $value, strlen($value));
			socket_close($socket);
		}
	}
	
	public function disallowDevice() {
		if ($this->getLogicalId() == '') {
			return;
		}
		$value = json_encode(array('apikey' => jeedom::getApiKey('openenocean'), 'cmd' => 'remove', 'device' => array('id' => $this->getLogicalId())));
		$socket = socket_create(AF_INET, SOCK_STREAM, 0);
		socket_connect($socket, '127.0.0.1', config::byKey('socketport', 'openenocean'));
		socket_write($socket, $value, strlen($value));
		socket_close($socket);
	}
	
	public function applyModuleConfiguration() {
		$this->setConfiguration('applyDevice', $this->getConfiguration('device'));
		$this->save();
		if ($this->getConfiguration('device') == '') {
			return true;
		}
		$device = self::devicesParameters($this->getConfiguration('device'));
		if (!is_array($device)) {
			return true;
		}
		event::add('jeedom::alert', array(
			'level' => 'warning',
			'page' => 'openenocean',
			'message' => __('Périphérique reconnu, intégration en cours', __FILE__),
		));
		$this->import($device);
		if (isset($device['afterInclusionSend']) && $device['afterInclusionSend'] != '') {
			event::add('jeedom::alert', array(
				'level' => 'warning',
				'page' => 'openenocean',
				'message' => __('Envoi des commandes post-inclusion', __FILE__),
			));
			sleep(5);
			$sends = explode('&&', $device['afterInclusionSend']);
			foreach ($sends as $send) {
				foreach ($this->getCmd('action') as $cmd) {
					if (strtolower($cmd->getName()) == strtolower(trim($send))) {
						$cmd->execute();
					}
				}
				sleep(1);
			}
			
		}
		sleep(2);
		event::add('jeedom::alert', array(
			'level' => 'warning',
			'page' => 'openenocean',
			'message' => '',
		));
	}
	
}

class openenoceanCmd extends cmd {
	/*     * *************************Attributs****************************** */
	
	/*     * ***********************Methode static*************************** */
	
	/*     * *********************Methode d'instance************************* */
	
	public function preSave() {
		if ($this->getConfiguration('id') == '') {
			$id = $this->getConfiguration('rorg') . $this->getConfiguration('func') . $this->getConfiguration('type');
			if ($id == '') {
				$id = openenocean::generateEnOceanId();
			}
			$this->setConfiguration('id', $id);
		}
	}
	
	public function execute($_options = null) {
		if ($this->getType() != 'action') {
			return;
		}
		$data = array();
		$eqLogic = $this->getEqLogic();
		$profile = array(
			'func' => $eqLogic->getConfiguration('func'),
			'rorg' => $eqLogic->getConfiguration('rorg'),
			'type' => $eqLogic->getConfiguration('type'),
		);
		$values = explode(',', $this->getLogicalId());
		foreach ($values as $value) {
			$value = explode(':', $value);
			if (count($value) == 2) {
				switch ($this->getSubType()) {
					case 'slider':
					if ($eqLogic->getConfiguration('invert100', 0) == 0) {
						$data[trim($value[0])] = floatval(trim(str_replace('#slider#', $_options['slider'], $value[1])));
					} else {
						$data[trim($value[0])] = floatval(trim(str_replace('#slider#', 100 - $_options['slider'], $value[1])));
					}
					break;
					case 'color':
					$data[trim($value[0])] = trim(str_replace('#color#', $_options['color'], $value[1]));
					break;
					case 'select':
					$data[trim($value[0])] = trim(str_replace('#select#', $_options['select'], $value[1]));
					break;
					case 'message':
					$data[trim($value[0])] = trim(str_replace('#message#', $_options['message'], $value[1]));
					break;
					default:
					$data[trim($value[0])] = trim($value[1]);
				}
				if ($eqLogic->getConfiguration('device') == 'a5-20-01'){
					if (strpos($value[1],'sourceTemp') !== false) {
						$cmdSource = cmd::byId(str_replace('#','',$eqLogic->getConfiguration('sourceTemp','')));
						if (!is_object($cmdSource)){
							$cmdSource = $eqLogic->getCmd(null, 'TMP::value');
						}
						if (!is_object($cmdSource)){
							$sourceTemp =0;
						} else {
							$sourceTemp = $cmdSource->execCmd();
						}
						$sourceTemp =intval(255 -($sourceTemp*6.375));
						$data[trim($value[0])] = trim(str_replace('#sourceTemp#', $sourceTemp, $value[1]));
					}
				}
				$operation = explode('||',$data[trim($value[0])]);
				if (count($operation) == 2) {
					if (substr($operation[1],0,1) == '*') {
						$data[trim($value[0])] = round($operation[0] * substr($operation[1],1));
					}
				}
			}
		}
		if (count($data) == 0) {
			return;
		}
		if (isset($data['generic']) && $data['generic'] == 1) {
			$data['generic'] = $eqLogic->getLogicalId();
		}
		if ($eqLogic->getConfiguration('twoids')) {
			$data['generic'] = $eqLogic->getConfiguration('actionid');
		}
		if (isset($data['profil'])) {
			$profile = array(
				'func' => substr($data['profil'], 2, 2),
				'rorg' => substr($data['profil'], 0, 2),
				'type' => substr($data['profil'], 4, 2),
			);
		}
		if ($this->getConfiguration('linked', '') != '') {
			foreach ($eqLogic->getCmd('info') as $cmdlink) {
				if ($cmdlink->getConfiguration('linkname', '') == $this->getConfiguration('linked', '')) {
					$data[$cmdlink->getConfiguration('linkname', '')] = strval($cmdlink->execCmd());
					break;
				}
			}
		}
		if ($eqLogic->getConfiguration('device') == 'a5-20-01'){
			$eqLogic->setCache('lastdata',$data);
		}
		$value = json_encode(array('apikey' => jeedom::getApiKey('openenocean'), 'cmd' => 'send', 'dest' => $eqLogic->getLogicalId(), 'profile' => $profile, 'command' => $data));
		$socket = socket_create(AF_INET, SOCK_STREAM, 0);
		socket_connect($socket, '127.0.0.1', config::byKey('socketport', 'openenocean'));
		socket_write($socket, $value, strlen($value));
		socket_close($socket);
		
	}
}
