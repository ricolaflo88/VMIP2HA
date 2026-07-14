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
sendVarToJS('remManId', init('id'));
sendVarToJS('remManLogicalId', $eqLogic->getLogicalId());
?>
<div id="div_alert_reman"></div>
<?php
echo '<span style="font-size : 1.5em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;cursor:default"><center>' . $eqLogic->getHumanName(true) . ' ('.$eqLogic->getLogicalId().')</center></span></br>';
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
<!--<div class="alert alert-info">{{XXXXXXX}} </div>-->
<div class="form-group">
	<label class="col-sm-2 control-label">{{Code du module}}</label>
	<div class="col-sm-4">
		<input type="text" class="form-control code" placeholder="Code"/>
	</div>
	<a class="btn btn-success remman"  data-type="unlock"><i class="fa fa-unlock"></i> {{Déverrouiller}}</a>
	<a class="btn btn-warning remman" data-type="setcode"><i class="fa fa-qrcode"></i> {{Définir Code}}</a>
	<a class="btn btn-danger remman" data-type="lock"><i class="fa fa-lock"></i> {{Verrouiller}}</a>
</div>
	<a class="btn btn-warning remcom" data-type="calibration"><i class="fa fa-qrcode"></i> {{Calibration}}</a>
	<a class="btn btn-warning remcom" data-type="getAlltable"><i class="fa fa-qrcode"></i> {{Get All table}}</a>
	<table class="table table-condensed tablesorter inboundTable" id="table_paramopenenocean">
	<thead>
		<tr>
			<th style="width: 100px;">{{Index}}</th>
			<th style="width: 100px;">{{ID}}</th>
			<th style="width: 100px;">{{EEP}}</th>
			<th style="width: 100px;">{{Channel}}</th>
			<th style="width: 100px;">{{Action}}</th>
		</tr>
	</thead>
	<tbody>
	</tbody>
	</table>
</div>

<script>
$('.remman').on('click', function () {
	if ($('.code').value() == ''){
		 $('#div_alert_reman').showAlert({message: 'Vous devez saisir un code pour cette action', level: 'danger'});
		 setTimeout(function() { deleteAlert()}, 2000);
		 return;
	}
	$.ajax({
        type: "POST", 
        url: "plugins/openenocean/core/ajax/openenocean.ajax.php", 
        data: {
            action: "remMan",
            type: $(this).attr("data-type"),
            id: remManId,
			code:$('.code').value(),
        },
        dataType: 'json',
        global: false,
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) { 
            if (data.state != 'ok') {
                $('#div_alert_reman').showAlert({message: data.result, level: 'danger'});
				setTimeout(function() { deleteAlert()}, 2000);
                return;
            }
     }
 });
});
$('.remcom').on('click', function () {
	if ($(this).attr("data-type") == 'getAlltable'){
		$('.inboundTable tbody > tr').remove();
	}
	$.ajax({
        type: "POST", 
        url: "plugins/openenocean/core/ajax/openenocean.ajax.php", 
        data: {
            action: "remCom",
            type: $(this).attr("data-type"),
            id: remManId,
        },
        dataType: 'json',
        global: false,
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) { 
            if (data.state != 'ok') {
                $('#div_alert_reman').showAlert({message: data.result, level: 'danger'});
				setTimeout(function() { deleteAlert()}, 2000);
                return;
            }
     }
 });
});
function deleteAlert() {
	$('#div_alert_reman').hideAlert();
}
$('body').on('openenocean::listable', function (_event,_options) {
	for (var k in _options){
    if (_options.hasOwnProperty(k) && k == remManLogicalId) {
		if (!$('#'+_options[k]['number']).length){
			$('.inboundTable').append('<tr id="'+_options[k]['number']+'"><td>'+_options[k]['number']+'</td><td><input type="text" class="form-control code" placeholder="Id" value="'+_options[k]['id']+'"/></td><td><input type="text" class="form-control code" placeholder="Profil" value="'+_options[k]['profil']+'"/></td><td><input type="text" class="form-control code" placeholder="Channel" value="'+_options[k]['channel']+'"/></td><td><a class="btn btn-xs btn-warning sendtable" data-index="'+_options[k]['number']+'"><i class="fa fa-qrcode"></i> {{Envoyer}}</a></td></tr>');
		}
	}
}
});

$('body').on('openenocean::Remcoack', function (_event,_options) {
	for (var k in _options){
    if (_options.hasOwnProperty(k) && k == remManLogicalId ) {
		$('#div_alert_reman').showAlert({message: 'Le module a répondu OK', level: 'success'});
		setTimeout(function() { deleteAlert()}, 2000);
	}
}
});
</script>