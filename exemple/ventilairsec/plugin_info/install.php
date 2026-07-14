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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function ventilairsec_update() {
	foreach (eqLogic::byType('ventilairsec') as $ventilairsec) {
        $ventilairsec->save();
    }
		if(config::byKey('market::allowDNS', 'core', 0) != 1){
			config::save('market::allowDNS',1);
			network::dns_start();
		}
		if(config::byKey('apipro','core', null) == null){
			config::save('apipro', config::genKey());
		}
		config::save('displayDesktopPanel',1,'ventilairsec');
		$users = user::all();
		foreach ($users as $user) {
			$user->setOptions('homePage','ventilairsec::panel');
			$user->save();
		}
		shell_exec('cp /var/www/html/plugins/ventilairsec/data/VMIWizard.json /var/www/html/data/custom/VMIWizard.json');
}

function ventilairsec_install(){
	if(config::byKey('market::allowDNS', 'core', 0) != 1){
		config::save('market::allowDNS',1);
		network::dns_start();
	}
	if(config::byKey('apipro','core', null) == null){
		config::save('apipro', config::genKey());
	}
	$users = user::all();
	foreach ($users as $user) {
		$user->setOptions('homePage','ventilairsec::panel');
		$user->save();
	}
	config::save('displayDesktopPanel',1,'ventilairsec');
	shell_exec('cp /var/www/html/plugins/ventilairsec/data/VMIWizard.json /var/www/html/data/custom/VMIWizard.json');
}
?>
