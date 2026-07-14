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
	throw new Exception('{{401 - Accès non autorisé}}');
}
if (init('id') == '') {
	throw new Exception('{{EqLogic ID ne peut être vide}}');
}
$plugin = plugin::byId('openenocean');
$eqLogic = eqLogic::byId(init('id'));
if (!is_object($eqLogic)) {
	throw new Exception('{{EqLogic non trouvé}}');
}
$device = openenocean::devicesParameters($eqLogic->getConfiguration('device'));
sendVarToJS('configureDeviceId', init('id'));
sendVarToJS('configureDeviceLogicalId', $eqLogic->getLogicalId());
?>
<?php
echo '<span style="font-size : 1.5em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;cursor:default"><center>' . $eqLogic->getHumanName(true) . '</center></span></br>';
echo "<center>";
$alternateImg = $eqLogic->getConfiguration('iconModel');
if (file_exists(dirname(__FILE__) . '/../../core/config/devices/' . $alternateImg . '.jpg')) {
	echo '<img class="lazy" src="plugins/openenocean/core/config/devices/' . $alternateImg . '.jpg" height="155" width="135" />';
} elseif (file_exists(dirname(__FILE__) . '/../../core/config/devices/' . $eqLogic->getConfiguration('device') . '.jpg')) {
	echo '<img class="lazy" src="plugins/openenocean/core/config/devices/' . $eqLogic->getConfiguration('device') . '.jpg"/>';
} else {
	echo '<img class="lazy" src="' . $plugin->getPathImgIcon() . '"/>';
}
echo "</center></br>";
?>
<div class="alert alert-info">{{Les modules Enocean ne renvoient pas la valeur de tous les paramètres, l'état actuel représente le dernier état que vous avez envoyé depuis Jeedom. Bien vérifier à tout remplir avant d'envoyer, notamment la première fois.}} </div>
<table class="table table-condensed tablesorter" id="table_paramopenenocean">
	<thead>
		<tr>
			<th style="width: 250px;">{{Nom}}</th>
			<th style="width: 600px;">{{Descriptif}}</th>
			<th style="width: 150px;">{{Valeur}}</th>
		</tr>
	</thead>
	<tbody>
        <?php
$bgcolor = '#e4e4e4';
foreach ($device['parameters'] as $parameter) {
	$channel = "1";
	$cmd = 2;
	if (isset($parameter['channel'])) {
		$channel = $parameter['channel'];
	}
	if (isset($parameter['cmd']) && $parameter['cmd'] != '') {
		$cmd = $parameter['cmd'];
	}
	$paramid = $parameter['shortcut'];
	echo '<tr bgcolor=' . $bgcolor . '><td><span class="label label-info" style="font-size : 1em;">' . $parameter['name'] . '</span></td>';
	echo '<td><span style="font-size : 1em;">' . $parameter['description'] . '</span></td>';
	echo '<td><center>';
	switch ($parameter['type']) {
		case 'binary':
			echo '<span><input type="checkbox" class="cmdAttr paramEnocean" data-l1key="' . $paramid . '" data-channel="' . $channel . '" data-cmd="' . $cmd . '"/></span>';
			break;
		case 'list':
			echo '<select class = "form-control paramEnocean" data-l1key="' . $paramid . '" data-channel="' . $channel . '" data-cmd="' . $cmd . '">';
			foreach ($parameter['values'] as $name => $value) {
				echo '<option value="' . $value . '">' . $name . '</option>';
			}
			echo '</select>';
			break;
		case 'value':
			echo '<input class="form-control paramEnocean" data-l1key="' . $paramid . '" data-channel="' . $channel . '" data-cmd="' . $cmd . '"/>';
			break;
	}
	echo '</center></td></tr>';
	if ($bgcolor == '#e4e4e4') {
		$bgcolor = '#ffffff';
	} else {
		$bgcolor = '#e4e4e4';
	}
}
echo '</tbody></table><a class="btn btn-success pull-right" style="color : white;" id="bt_configureDeviceSend"><i class="fas fa-check"></i> {{Appliquer}}</a>';
?>
</div>

<script>
configureDeviceLoad();
$('#bt_configureDeviceSend').on('click', function () {
	var moduleParams= {}
	$('.paramEnocean').each(function( index ) {
		moduleParams[$(this).attr('data-l1key')] ='{"value" :' + $(this).value()+',"cmd" : '+$(this).attr('data-cmd')+', "channel":"'+$(this).attr('data-channel')+'"}';
	});
	configureDeviceSave(moduleParams);
});
    function configureDeviceSave(moduleParams) {
        $.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // méthode de transmission des données au fichier php
            url: "plugins/openenocean/core/ajax/openenocean.ajax.php", // url du fichier php
            data: {
                action: "setDeviceConfiguration",
				id : configureDeviceId,
                parameters: json_encode(moduleParams)
            },
            dataType: 'json',
            error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) { // si l'appel a bien fonctionné
        if (data.state != 'ok') {
            $('#div_alert').showAlert({message: data.result, level: 'danger'});
			$('#md_modal').dialog('close');
            return;
        }
		$('#div_alert').showAlert({message: 'Paramètres envoyés avec succès', level: 'success'});
		$('#md_modal').dialog('close');
        }
    });
}
function configureDeviceLoad() {
        $.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // méthode de transmission des données au fichier php
            url: "plugins/openenocean/core/ajax/openenocean.ajax.php", // url du fichier php
            data: {
                action: "getDeviceConfiguration",
                id: configureDeviceId
            },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error, $('#div_configureDeviceAlert'));
            },
            success: function (data) { // si l'appel a bien fonctionné
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
				return;
			}
			$('#table_paramopenenocean').setValues(data.result,'.paramEnocean');
        }
    });
    }
</script>