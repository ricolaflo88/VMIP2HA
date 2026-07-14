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

try {
	require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
	include_file('core', 'authentification', 'php');

	if (!isConnect('admin')) {
		throw new Exception('401 Unauthorized');
	}

	ajax::init();

	if (init('action') == 'changeIncludeState') {
		openenocean::changeIncludeState(init('state'), init('mode'),init('type'));
		ajax::success();
	}

	if (init('action') == 'getModelListParam') {
		$openenocean = openenocean::byId(init('id'));
		if (!is_object($openenocean)) {
			ajax::success(array());
		}
		ajax::success($openenocean->getModelListParam(init('conf')));
	}
	
	if (init('action') == 'setDeviceConfiguration') {
		$openenocean = openenocean::byId(init('id'));
		if (!is_object($openenocean)) {
			ajax::success(array());
		}
		ajax::success($openenocean->setDeviceConfiguration(init('parameters')));
	}
	
	if (init('action') == 'getDeviceConfiguration') {
		$openenocean = openenocean::byId(init('id'));
		if (!is_object($openenocean)) {
			ajax::success(array());
		}
		ajax::success($openenocean->getDeviceConfiguration());
	}
	
	if (init('action') == 'changeRepeater') {
		$openenocean = openenocean::byId(init('id'));
		if (!is_object($openenocean)) {
			ajax::success(array());
		}
		ajax::success($openenocean->changeRepeater(init('type')));
	}
	
	if (init('action') == 'autoDetectModule') {
		$eqLogic = openenocean::byId(init('id'));
		if (!is_object($eqLogic)) {
			throw new Exception(__('Enocean eqLogic non trouvé : ', __FILE__) . init('id'));
		}
		if (init('createcommand') == 1){
			foreach ($eqLogic->getCmd() as $cmd) {
				$cmd->remove();
			}
		}
		$eqLogic->applyModuleConfiguration();
		ajax::success();
	}
	
	if (init('action') == 'changeLogLive') {
		ajax::success(openenocean::changeLogLive(init('level')));
	}
	
	if (init('action') == 'remMan') {
		$openenocean = openenocean::byId(init('id'));
		if (!is_object($openenocean)) {
			ajax::success(array());
		}
		ajax::success($openenocean->remMan(init('code'),init('type')));
	}
	if (init('action') == 'remCom') {
		$openenocean = openenocean::byId(init('id'));
		if (!is_object($openenocean)) {
			ajax::success(array());
		}
		ajax::success($openenocean->remCom(init('type')));
	}

	throw new Exception('Aucune methode correspondante');
	/*     * *********Catch exeption*************** */
} catch (Exception $e) {
	ajax::error(displayExeption($e), $e->getCode());
}
?>
