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
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }
	ajax::init();

	if (init('action') == 'sync') {
		ajax::success(ventilairsec::discover());
	}

	if (init('action') == 'eventRefresh') {
		ajax::success(ventilairsec::eventRefresh(init('id')));
	}

	if (init('action') == 'sendAction') {
		ajax::success(ventilairsec::sendAction(init('type'),init('value')));
	}

	if (init('action') == 'sendPair') {
		ajax::success(openenocean::changeIncludeState(1,1,0));
	}

	if (init('action') == 'setConfiguration') {
		ajax::success(ventilairsec::setIntConfiguration(init('parameters')));
	}

  if (init('action') == 'setMobileConfiguration') {
		ajax::success(ventilairsec::setMobileConfiguration(init('parameters')));
	}
  
	if (init('action') == 'getConfiguration') {
		ajax::success(ventilairsec::getIntConfiguration());
	}

	if (init('action') == 'getGraphData') {
		ajax::success(ventilairsec::getGraphData(init('object'),init('type'),init('daytype'),init('datetime')));
	}

	if (init('action') == 'getAgendaData') {
		ajax::success(ventilairsec::getAgendaData());
	}

  if (init('action') == 'checknotif') {
    ajax::success(ventilairsec::checknotif());
  }

    throw new Exception(__('Aucune methode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayExeption($e), $e->getCode());
}
?>
