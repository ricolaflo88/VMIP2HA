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
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";

if (!jeedom::apiAccess(init('apikey'), 'openenocean')) {
	echo __('Vous n\'etes pas autorisé à effectuer cette action', __FILE__);
	die();
}

if (init('test') != '') {
	echo 'OK';
	die();
}
$result = json_decode(file_get_contents("php://input"), true);
if (!is_array($result)) {
	die();
}
if (isset($result['started'])) {
	if ($result['started'] == 1) {
		log::add('openenocean','info','Daemon alive sending known devices');
		openenocean::sendIdToDeamon();
	}
}
if (isset($result['baseid'])) {
	config::save('baseid', $result['baseid'], 'openenocean');
}

if (isset($result['arubaMessage'])) {
	if (class_exists('aruba')) {
		aruba::sendPayload($result['arubaMessage']);
	}
}

if (isset($result['LinkTable'])) {
	event::add('openenocean::listable', $result['LinkTable']);
}

if (isset($result['Remcoack'])) {
	event::add('openenocean::Remcoack', $result['Remcoack']);
}


if (isset($result['app_description'])) {
	config::save('app_description', $result['app_description'], 'openenocean');
}

if (isset($result['app_version'])) {
	config::save('app_version', $result['app_version'], 'openenocean');
}

if (isset($result['api_version'])) {
	config::save('api_version', $result['api_version'], 'openenocean');
}

if (isset($result['chip_version'])) {
	config::save('chip_version', $result['chip_version'], 'openenocean');
}

if (isset($result['learn_mode'])) {
	if ($result['learn_mode'] == 1) {
		config::save('include_mode', 1, 'openenocean');
		event::add('openenocean::includeState', array(
			'mode' => 'learn',
			'state' => 1)
		);
	} else {
		config::save('include_mode', 0, 'openenocean');
		event::add('openenocean::includeState', array(
			'mode' => 'learn',
			'state' => 0)
		);
	}
	die();
}

if (isset($result['exclude_mode'])) {
	if ($result['exclude_mode'] == 1) {
		config::save('exclude_mode', 1, 'openenocean');
		event::add('openenocean::includeState', array(
			'mode' => 'exclude',
			'state' => 1)
		);
	} else {
		config::save('exclude_mode', 0, 'openenocean');
		event::add('openenocean::includeState', array(
			'mode' => 'exclude',
			'state' => 0)
		);
		sleep(1);
		if (isset($result['deviceId'])) {
			event::add('jeedom::alert', array(
				'level' => 'warning',
				'page' => 'openenocean',
				'message' => __('Un périphérique EnOcean est en cours d\'exclusion. Logical ID : ', __FILE__) . $result['deviceId'],
			));
			sleep(2);
			openenocean::excludedDevice($result['deviceId']);
		}
	}
	die();
}

if (isset($result['devices'])) {
	foreach ($result['devices'] as $key => $datas) {
		if (!isset($datas['id'])) {
			continue;
		}
		$openenocean = openenocean::byLogicalId($datas['id'], 'openenocean');
		if (!is_object($openenocean)) {
			if ($datas['learn'] != 1) {
				continue;
			}
			$openenocean = openenocean::createFromDef($datas);
			if (!is_object($openenocean)) {
				log::add('openenocean', 'debug', __('Aucun équipement trouvé pour : ', __FILE__) . secureXSS($datas['id']));
				continue;
			}
			event::add('jeedom::alert', array(
				'level' => 'warning',
				'page' => 'openenocean',
				'message' => '',
			));
			event::add('openenocean::includeDevice', $openenocean->getId());
		}
		if (!$openenocean->getIsEnable()) {
			continue;
		}
		if (isset($datas['repeat'])){
			$repeat = $datas['repeat'];
			$openenocean->setStatus('lasRepeat',$repeat);
			if ($repeat=='0'){
				$openenocean->setStatus('lasRepeat0', $openenocean->getStatus('lasRepeat0',0) + 1);
			} else if ($repeat=='1'){
				$openenocean->setStatus('lasRepeat1', $openenocean->getStatus('lasRepeat1',0) + 1);
			} else if ($repeat=='1'){
				$openenocean->setStatus('lasRepeat2', $openenocean->getStatus('lasRepeat2',0) + 1);
			}
		}

		foreach ($openenocean->getCmd('info') as $cmd) {
			$logicalId = $cmd->getLogicalId();
			if ($logicalId == '') {
				continue;
			}
			$path = explode('::', $logicalId);
			$value = $datas;
			foreach ($path as $key) {
				if (!isset($value[$key])) {
					continue (2);
				}
				$value = $value[$key];
				if (!is_array($value) && strpos($value, 'toggle') !== false && $cmd->getSubType() == 'binary') {
					$value = $cmd->execCmd();
					$value = ($value != 0) ? 0 : 1;
				}
			}
			if (!is_array($value)) {
				if ($cmd->getSubType() == 'numeric') {
					$value = round($value, 2);
					if ($openenocean->getConfiguration('invert100',0) == 1) {
						if ($logicalId != 'dBm'){
							$value = 100-$value;
						}
					}
				}
				$cmd->event($value);
			}
		}
	}
}
