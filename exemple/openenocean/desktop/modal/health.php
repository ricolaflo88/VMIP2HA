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

if (!isConnect('admin')) {
	throw new Exception('401 Unauthorized');
}
$plugin = plugin::byId('openenocean');
$eqLogics = openenocean::byType($plugin->getId());
?>

<table class="table table-condensed tablesorter" id="table_healthopenenocean">
	<thead>
		<tr>
			<th>{{Image}}</th>
			<th>{{Module}}</th>
			<th>{{ID Jeedom}}</th>
			<th>{{ID Enocean}}</th>
			<th>{{Profil}}</th>
			<th>{{Configuration}}</th>
			<th>{{Répéteur}}</th>
			<th>{{dBm}}</th>
			<th>{{Messages}}</th>
			<th>{{Dernière communication}}</th>
			<th>{{Date création}}</th>
		</tr>
	</thead>
	<tbody>
	 <?php
foreach ($eqLogics as $eqLogic) {
	$profile = strtoupper($eqLogic->getConfiguration('rorg') . '-' . $eqLogic->getConfiguration('func') . '-' . $eqLogic->getConfiguration('type'));
	$alternateImg = $eqLogic->getConfiguration('iconModel');
	if (file_exists(dirname(__FILE__) . '/../../core/config/devices/' . $alternateImg . '.jpg')) {
		$img = '<img class="lazy" src="plugins/openenocean/core/config/devices/' . $alternateImg . '.jpg" height="65" width="55" />';
	} elseif (file_exists(dirname(__FILE__) . '/../../core/config/devices/' . $eqLogic->getConfiguration('device') . '.jpg')) {
		$img = '<img class="lazy" src="plugins/openenocean/core/config/devices/' . $eqLogic->getConfiguration('device') . '.jpg" height="65" width="55" />';
	} else {
		$img = '<img class="lazy" src="' . $plugin->getPathImgIcon() . '" height="65" width="55" />';
	}
	$signalcmd = $eqLogic->getCmd('info', 'dBM');
	$signal = '';
	if (is_object($signalcmd)) {
		$signal = $signalcmd->execCmd();
	}
	echo '<tr><td>' . $img . '</td><td><a href="' . $eqLogic->getLinkToConfiguration() . '" style="text-decoration: none;">' . $eqLogic->getHumanName(true) . '</a></td>';
	echo '<td><span class="label label-info" style="font-size : 1em;cursor:default;">' . $eqLogic->getId() . '</span></td>';
	$moduleid = $eqLogic->getLogicalId();
	if ($eqLogic->getConfiguration('twoids', 0) == 1) {
		$moduleid .= ' (' . $eqLogic->getConfiguration('actionid') . ')';
	}
	echo '<td><span class="label label-info" style="font-size : 1em;cursor:default;">' . $moduleid . '</span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em;cursor:default;">' . $profile . '</span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em;cursor:default;">' . $eqLogic->getConfiguration('device') . '</span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em;cursor:default;">' . $eqLogic->getConfiguration('lastRepeat','0') . '</span></td>';
	$signalLevel = 'success';
	if ($signal <= -85) {
		$signalLevel = 'danger';
	} elseif ($signal <= -75) {
		$signalLevel = 'warning';
	}
	echo '<td><span class="label label-' . $signalLevel . '" style="font-size : 1em;cursor:default;">' . $signal . '</span></td>';
	echo '<td><center><span class="label label-info" style="font-size : 1em;cursor:default;display: inline-block;">' . strval($eqLogic->getStatus('lasRepeat0', 0) + $eqLogic->getStatus('lasRepeat1', 0) + $eqLogic->getStatus('lasRepeat2', 0)) . '</span></br>';
	echo '<span class="label label-info" style="font-size : 0.8em;cursor:default;display: inline-block;margin-top:5px;" title="Combien de messages ont été répétés 0 fois/1 fois/2 fois depuis le démarrage du plugin et dernier message"> Répétition : ' . $eqLogic->getStatus('lasRepeat0', '0') . '/';
	echo $eqLogic->getStatus('lasRepeat1', '0') . '/';
	echo $eqLogic->getStatus('lasRepeat2', '0') . '</br> Dernier : ' . $eqLogic->getStatus('lasRepeat', '0') . ' fois</span></center></td>';
	echo '<td><span class="label label-info" style="font-size : 1em;cursor:default;">' . $eqLogic->getStatus('lastCommunication') . '</span></br></td>';
	echo '<td><span class="label label-info" style="font-size : 1em;cursor:default;">' . $eqLogic->getConfiguration('createtime') . '</span></td></tr>';
}
?>
	</tbody>
</table>
